<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nama_pemilik',
        'no_hp',
        'email',
        'alamat',
        'pin',
        'nomor_ktp',
        'npwp',
        'alamat_ip',
        'password_ip',
        'url_report',
        'berkas_path',
        'berkas_nama',
        'foto_ktp_path',
        'foto_npwp_path',
        'status',
        'kode_reseller',
        'kode_prefix',
        'catatan_admin',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Generate unique reseller code: {PREFIX}{5-digit random}
     * e.g. CMP12345
     */
    public static function generateKode(string $prefix = 'CMP'): string
    {
        do {
            $kode = strtoupper($prefix) . str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('kode_reseller', $kode)->exists());

        return $kode;
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function statusLabel(): string
    {
        return match($this->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => 'Menunggu',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'warning',
        };
    }
}
