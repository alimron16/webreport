<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get a setting by key, auto-decrypting if is_encrypted is true.
     */
    public static function get(string $key, $default = null)
    {
        try {
            $setting = static::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            if ($setting->is_encrypted && !empty($setting->value)) {
                try {
                    return Crypt::decryptString($setting->value);
                } catch (\Throwable $e) {
                    Log::warning("Failed to decrypt setting [{$key}]: " . $e->getMessage());
                    return $setting->value; // Fallback if plain text
                }
            }

            return $setting->value ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting by key, optionally encrypting the value.
     */
    public static function set(string $key, $value, bool $encrypted = false): self
    {
        $storedValue = $value;
        if ($encrypted && !empty($value)) {
            $storedValue = Crypt::encryptString((string)$value);
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'is_encrypted' => $encrypted,
            ]
        );
    }

    /**
     * Check if a setting key exists in the database.
     */
    public static function has(string $key): bool
    {
        try {
            return static::where('key', $key)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
