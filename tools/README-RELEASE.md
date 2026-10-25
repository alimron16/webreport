# Panduan Build Release & Obfuscation Webreport

Dokumen ini menjelaskan cara membuat paket rilis Webreport yang terproteksi lisensi dan kode sumbernya telah diacak (obfuscate) dengan **YAK Pro**.

---

## 1. Mekanisme Proteksi & Lisensi
1. **Sistem Lisensi Otomatis**:
   - Terintegrasi dengan server lisensi: `http://103.150.197.121:4000/api/license/verify`
   - Product: `"WEBREPORT"`
   - Middleware `EnsureLicenseValid` otomatis mengunci seluruh halaman dan mengarahkan ke `/license` jika lisensi belum diisi, kadaluarsa, atau tidak valid.
   - Dilengkapi sistem cache lokal 6 jam, grace period offline 72 jam jika server lisensi sesaat down, dan rate limiter aktivasi.
2. **Obfuscation Kode Backend**:
   - Folder `app/`, `routes/`, dan `bootstrap/` diacak menggunakan **YAK Pro**.
   - Seluruh string di-encode octal/hex, variabel lokal diacak, alur logika diacak dengan jump statements (`goto`), komentar & indentasi dihapus.
   - Nama class, model, route parameters, dan variabel compact view (`$data`, dll) tetap dipertahankan agar tidak merusak ekosistem Laravel.

---

## 2. Cara Membuat Paket Rilis (Build ZIP)

Cukup buka PowerShell di root project lalu jalankan:

```powershell
powershell -ExecutionPolicy Bypass -File tools\build-release.ps1
```

Script akan otomatis:
1. Menyalin project ke `dist\webreport\`.
2. Membersihkan file dev/sensitif (`.git`, `.env`, database sqlite, cache, log, sesi).
3. Mengumpulkan variabel yang dipakai di Blade/Route agar tidak dirusak.
4. Menjalankan obfuscation pada `app/`, `routes/`, dan `bootstrap/`.
5. Mengecek sintaks semua file PHP hasil (`php -l`) untuk memastikan 100% valid.
6. Mengompres menjadi file arsip ZIP siap kirim di `dist\webreport-release-YYYYMMDD.zip`.

---

## 3. Cara Pembeli Menjalankan & Mengaktivasi
1. Pembeli mengekstrak ZIP `webreport-release-YYYYMMDD.zip`.
2. Pembeli cukup klik dua kali **`RunWebreport.bat`**.
   - Database SQLite otomatis di-generate.
   - Server otomatis aktif dan membuka browser ke `http://127.0.0.1:8000`.
3. Karena belum ada lisensi, web langsung menampilkan **Halaman Aktivasi Lisensi**:
   - Pembeli melihat **Hardware ID (HWID)** PC mereka (bisa langsung di-copy).
   - Pembeli memasukkan **License Key** yang Anda berikan, lalu klik **Aktivasi Lisensi**.
4. Setelah diverifikasi oleh server Anda, web langsung terbuka penuh dan siap digunakan.
