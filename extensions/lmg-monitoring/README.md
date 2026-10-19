# LMG Privacy Monitoring Extension

Extension ini hanya membagikan tab kerja yang dipilih pengguna. Tab lain, window lain, dan seluruh desktop tidak dicapture.

## Instalasi untuk pengguna

Untuk pengguna biasa, unggah paket `public/downloads/lmg-privacy-monitoring-chrome-web-store.zip` ke Chrome Web Store sebagai **Unlisted** atau **Private**. Setelah Chrome menyetujui publikasi, pengguna cukup membuka link Store dan menekan **Add to Chrome**. Tidak perlu mengaktifkan Developer mode atau memilih folder lokal.

Chrome tidak mengizinkan extension dipasang otomatis dari file unduhan biasa. Instalasi otomatis hanya tersedia melalui Chrome Web Store atau kebijakan perangkat organisasi (Chrome Enterprise/Google Workspace).

Untuk instalasi manual, gunakan `public/downloads/lmg-privacy-monitoring-install.zip`. Setelah diextract, ZIP tersebut menghasilkan satu folder `lmg-monitoring` yang dapat dipilih langsung pada **Load unpacked**.

## Pasang untuk pengembangan

1. Buka `chrome://extensions` pada Chrome.
2. Aktifkan **Developer mode**.
3. Pilih **Load unpacked**.
4. Pilih folder ini: `extensions/lmg-monitoring`.
5. Pin extension **LMG Privacy Monitoring**.

## Cara memakai

1. Buka setiap tab kerja yang ingin dibagikan, klik icon extension, lalu pilih **Tambahkan tab ini**.
2. Setelah semua tab dipilih, buka halaman Monitoring ERP dengan akun target.
3. Klik icon extension lalu pilih **Bagikan tab terpilih ke ERP**.
4. ERP akan menampilkan preview gabungan. Klik **Izinkan kamera** untuk memulai sesi live.

Tab yang tidak ditambahkan tidak bisa diakses extension. Daftar tab disimpan hanya untuk sesi browser saat ini dan dihapus saat browser ditutup.

## Batasan browser

- Chrome mengharuskan pengguna memberi akses dengan klik extension pada tiap tab kerja.
- Extension hanya dapat menangkap tab dalam profil Chrome tempat extension dipasang.
- Memilih tab tidak memberi extension akses ke desktop atau window lain.
