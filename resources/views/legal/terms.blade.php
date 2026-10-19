@extends('legal.layout')
@section('title', 'Ketentuan Layanan')
@section('content')
    <p class="eyebrow">Informasi Legal</p>
    <h1>Ketentuan Layanan</h1>
    <p class="updated">Berlaku sejak: 7 Oktober 2026 &middot; Terakhir diperbarui: 7 Oktober 2026</p>

    <div class="notice"><strong>Ringkasan penting</strong>ERP Lighter Media Group merupakan sistem internal dengan akses terbatas. Akun hanya diberikan kepada karyawan, administrator, atau pihak lain yang memperoleh otorisasi dari PT Lighter Media Group. Ketentuan ini harus dibaca bersama <a href="{{ route('legal.privacy') }}">Kebijakan Privasi</a>.</div>

    <h2>1. Identitas penyedia layanan</h2>
    <p>Layanan ini disediakan dan dikelola oleh <strong>PT Lighter Media Group</strong> (selanjutnya disebut “LMG”, “kami”, atau “Pengelola”), berkedudukan di Jl. Soekarno-Hatta No. 399A, Bandung, Jawa Barat, Indonesia. Korespondensi mengenai layanan dapat dikirimkan ke <a href="mailto:lightermediagroup@gmail.com">lightermediagroup@gmail.com</a>.</p>

    <h2>2. Penerimaan Ketentuan</h2>
    <p>Dengan mengakses atau menggunakan ERP Lighter Media Group (“Layanan”), pengguna menyatakan telah membaca, memahami, dan menyetujui Ketentuan Layanan ini serta kebijakan internal organisasi yang berlaku. Apabila pengguna tidak menyetujuinya atau tidak lagi memiliki otorisasi, pengguna wajib menghentikan penggunaan Layanan dan menghubungi administrator.</p>

    <h2>3. Ruang lingkup dan tujuan Layanan</h2>
    <p>Layanan adalah sistem enterprise resource planning internal yang mendukung kegiatan operasional LMG, termasuk pengelolaan pekerjaan dan KPI, pengguna dan pembagian peran, klien dan CRM, dokumen, aktivitas pemasaran digital, data platform terhubung, sumber daya manusia, pembelian, pembayaran, payroll, audit, dan laporan keuangan. Ketersediaan modul bergantung pada peran, divisi, penugasan, dan keputusan administrator.</p>
    <p>Layanan tidak ditawarkan sebagai produk konsumen umum dan tidak menyediakan pendaftaran akun secara mandiri.</p>

    <h2>4. Kelayakan dan otorisasi pengguna</h2>
    <p>Pengguna harus merupakan orang yang secara sah diberi akses oleh LMG, misalnya karyawan, administrator, tenaga kontrak, vendor, mitra, atau perwakilan klien yang memerlukan akses untuk tujuan kerja. Pengguna hanya boleh bertindak dalam lingkup tugas dan kewenangannya. Hak akses tidak dapat dialihkan kepada orang lain.</p>

    <h2>5. Akun, kredensial, dan keamanan akses</h2>
    <p>Akun dibuat atau dikelola oleh administrator. Pengguna wajib memberikan informasi akun yang benar, menjaga kata sandi dan perangkat tetap aman, tidak berbagi akun, keluar dari sesi pada perangkat bersama, serta segera melaporkan dugaan akses tanpa izin. Seluruh tindakan melalui akun dianggap dilakukan oleh pemegang akun sepanjang tidak terdapat bukti penyalahgunaan yang telah dilaporkan.</p>
    <p>Jika kredensial perusahaan, autentikasi terpusat, atau single sign-on digunakan pada masa mendatang, pengguna juga tunduk pada kebijakan identitas dan keamanan penyedia atau organisasi terkait. Pengguna dilarang menghindari autentikasi, kontrol akses, atau langkah pengamanan lainnya.</p>

    <h2>6. Penggunaan yang diperbolehkan</h2>
    <p>Pengguna dapat memakai Layanan untuk kegiatan bisnis yang sah, relevan dengan pekerjaan, dan telah disetujui, termasuk memasukkan serta memperbarui data operasional, berkolaborasi, membuat laporan, mengelola dokumen, dan menghubungkan akun platform pihak ketiga apabila memiliki hak serta izin untuk melakukannya.</p>

    <h2>7. Aktivitas yang dilarang</h2>
    <ul>
        <li>Mengakses akun, client, divisi, dokumen, token, atau data di luar kewenangannya.</li>
        <li>Membagikan kata sandi, token akses, data rahasia, atau hasil ekspor kepada pihak yang tidak berwenang.</li>
        <li>Mengunggah data yang melanggar hukum, hak privasi, kekayaan intelektual, kewajiban kontraktual, atau kebijakan platform pihak ketiga.</li>
        <li>Mengganggu, memindai, menguji, merekayasa balik, menghindari pembatasan, atau berupaya memperoleh akses tidak sah.</li>
        <li>Menggunakan bot, scraping, atau otomatisasi yang tidak disetujui sehingga membebani atau merusak Layanan.</li>
        <li>Menghapus atau mengubah catatan bisnis, log, maupun data pihak lain tanpa kewenangan.</li>
        <li>Menggunakan Layanan untuk penipuan, pelecehan, malware, atau kegiatan yang melanggar hukum.</li>
    </ul>

    <h2>8. Keakuratan dan legalitas data</h2>
    <p>Pengguna bertanggung jawab memastikan data yang dimasukkan, diunggah, diimpor, atau dihubungkan akurat, mutakhir, relevan, dan diperoleh secara sah. Pengguna wajib melakukan koreksi ketika mengetahui adanya kekeliruan. LMG dapat mengoreksi, membatasi, atau meminta klarifikasi atas data yang tidak lengkap, tidak konsisten, atau berisiko.</p>

    <h2>9. Administrator, peran, dan pengawasan organisasi</h2>
    <p>Administrator LMG dapat membuat, mengubah, mengunci, dan menonaktifkan akun; menetapkan peran, divisi, client, dan hak akses; meninjau log aktivitas; mengelola data; serta melakukan tindakan lain yang diperlukan untuk keamanan, kepatuhan, dukungan, dan operasional. Pengguna memahami bahwa penggunaan Layanan dalam konteks pekerjaan dapat dipantau secara proporsional oleh personel yang berwenang sesuai kebijakan perusahaan dan hukum yang berlaku.</p>

    <h2>10. Data perusahaan dan data pengguna</h2>
    <p>Data bisnis, data klien, dokumen, laporan, catatan transaksi, materi kerja, dan informasi lain yang dimasukkan atau dibuat dalam pelaksanaan pekerjaan tetap berada dalam penguasaan pihak yang secara hukum berhak atas data tersebut. Hak akses pengguna tidak mengalihkan kepemilikan data. LMG dapat memproses data untuk menyediakan, mengamankan, memelihara, mengaudit, dan meningkatkan Layanan sesuai <a href="{{ route('legal.privacy') }}">Kebijakan Privasi</a>, kontrak, dan hukum.</p>

    <h2>11. Hak kekayaan intelektual</h2>
    <p>Perangkat lunak, desain, antarmuka, dokumentasi, merek, logo, kode, dan materi Layanan milik LMG atau pemberi lisensinya dan dilindungi hukum. Selama memiliki otorisasi, pengguna memperoleh hak terbatas, dapat dicabut, tidak eksklusif, dan tidak dapat dialihkan untuk memakai Layanan hanya bagi tujuan kerja yang disetujui.</p>

    <h2>12. Integrasi dan layanan pihak ketiga</h2>
    <p>Layanan dapat berinteraksi dengan Meta/Facebook/Instagram, Threads, TikTok, Google/YouTube, layanan email, infrastruktur komunikasi, atau penyedia teknologi pendukung. Integrasi hanya boleh diaktifkan oleh pihak yang berwenang dan tunduk pula pada syarat, kebijakan, izin, serta perubahan teknis penyedia terkait.</p>
    <p>LMG tidak mengendalikan layanan pihak ketiga dan tidak menjamin bahwa API, izin, data, atau fitur mereka akan selalu tersedia. Pencabutan izin dapat menghentikan pengambilan data baru. Petunjuk pencabutan dan penghapusan tersedia pada halaman <a href="{{ route('legal.data-deletion') }}">Penghapusan Data</a>.</p>

    <h2>13. Ketersediaan, pemeliharaan, dan perubahan</h2>
    <p>LMG berupaya menjaga Layanan tetap berfungsi secara wajar, tetapi tidak menjanjikan tingkat ketersediaan tertentu. Layanan dapat terganggu karena pemeliharaan, pembaruan, insiden keamanan, konektivitas, keadaan kahar, atau perubahan pihak ketiga. LMG dapat menambah, mengubah, atau menghentikan fitur dengan mempertimbangkan kebutuhan operasional, keamanan, dan dampaknya.</p>

    <h2>14. Penangguhan dan pencabutan akses</h2>
    <p>LMG dapat membatasi, mengunci, atau mencabut akses apabila hubungan kerja atau penugasan berakhir; akses tidak lagi diperlukan; terdapat risiko keamanan; pengguna melanggar Ketentuan ini atau kebijakan perusahaan; diminta oleh organisasi, pemilik data, platform, atau pihak berwenang; atau diperlukan untuk melindungi Layanan dan pihak lain.</p>

    <h2>15. Biaya dan pembayaran</h2>
    <p>Pengguna akhir tidak dikenai biaya langsung untuk membuat atau memakai akun internal. Biaya layanan, vendor, lisensi, atau hubungan komersial dengan klien dan mitra, apabila ada, diatur dalam perjanjian terpisah dan bukan melalui Ketentuan ini.</p>

    <h2>16. Kerahasiaan</h2>
    <p>Pengguna wajib merahasiakan informasi nonpublik yang diperoleh melalui Layanan, termasuk data klien, strategi, kredensial, token, informasi keuangan, data pribadi, dan dokumen internal. Informasi hanya boleh digunakan dan diungkapkan untuk tujuan kerja kepada pihak berwenang. Kewajiban ini tetap berlaku setelah akses berakhir sejauh diwajibkan oleh hukum, kontrak, atau sifat informasinya.</p>

    <h2>17. Keamanan dan tanggung jawab pengguna</h2>
    <p>LMG menerapkan langkah pengamanan secara proporsional, tetapi keamanan juga bergantung pada tindakan pengguna. Pengguna wajib memperbarui perangkat, menghindari perangkat atau jaringan yang tidak aman, memeriksa tujuan sebelum mengekspor data, serta segera melaporkan phishing, kehilangan perangkat, salah kirim, atau insiden lain.</p>

    <h2>18. Penafian jaminan</h2>
    <p>Sejauh diperbolehkan hukum, Layanan disediakan menurut kondisi yang tersedia untuk kebutuhan internal. LMG tidak menjamin Layanan bebas sepenuhnya dari gangguan, kesalahan, kehilangan data, atau perubahan pihak ketiga. Laporan dan analisis merupakan alat bantu; keputusan bisnis tetap memerlukan peninjauan manusia. Ketentuan ini tidak menghapus jaminan atau hak yang tidak dapat dikesampingkan berdasarkan hukum.</p>

    <h2>19. Batasan tanggung jawab</h2>
    <p>Sejauh diperbolehkan hukum, LMG tidak bertanggung jawab atas kerugian tidak langsung atau konsekuensial akibat penyalahgunaan akun, tindakan di luar kewenangan, data keliru, keputusan berdasarkan data yang belum diverifikasi, atau gangguan pihak ketiga. Pembatasan ini tidak berlaku terhadap tanggung jawab yang tidak dapat dibatasi, termasuk kerugian karena kesengajaan atau kelalaian berat LMG.</p>

    <h2>20. Ganti rugi</h2>
    <p>Sejauh sah dan relevan, pengguna bertanggung jawab atas kerugian langsung yang timbul dari tindakan sengaja, penggunaan tanpa hak, atau pelanggaran hukum dan kerahasiaan oleh pengguna. Ketentuan ini tidak mengurangi perlindungan ketenagakerjaan atau hak wajib lain.</p>

    <h2>21. Keadaan kahar</h2>
    <p>Para pihak tidak bertanggung jawab atas keterlambatan atau kegagalan karena keadaan di luar kendali wajar, seperti bencana, kebakaran, gangguan listrik atau telekomunikasi luas, serangan siber eksternal, tindakan pemerintah, konflik, epidemi, atau kegagalan infrastruktur, sepanjang dilakukan upaya wajar untuk mengurangi dampaknya.</p>

    <h2>22. Hukum dan penyelesaian sengketa</h2>
    <p>Ketentuan ini diatur oleh hukum Republik Indonesia. Perselisihan akan terlebih dahulu diselesaikan melalui musyawarah dengan itikad baik. Apabila tidak tercapai, perselisihan diselesaikan melalui pengadilan yang berwenang di Kota Bandung, tanpa mengurangi hak atau forum wajib berdasarkan hukum.</p>

    <h2>23. Perubahan Ketentuan</h2>
    <p>LMG dapat memperbarui Ketentuan untuk mencerminkan perubahan Layanan, praktik, atau kewajiban hukum. Tanggal pembaruan dicantumkan di bagian atas. Perubahan material akan diberitahukan melalui Layanan, email, atau kanal internal yang wajar.</p>

    <h2>24. Ketentuan umum</h2>
    <p>Apabila suatu ketentuan dinyatakan tidak berlaku, bagian lainnya tetap berlaku. Kegagalan menegakkan suatu ketentuan bukan pelepasan hak. Ketentuan ini melengkapi perjanjian kerja, kerahasiaan, kontrak vendor atau klien, dan kebijakan internal; dokumen yang lebih khusus berlaku atas aktivitas yang secara khusus diaturnya sepanjang dibenarkan hukum.</p>

    <h2>25. Hubungi kami</h2>
    <p>Pertanyaan, laporan keamanan, atau permintaan terkait Ketentuan dapat dikirimkan kepada PT Lighter Media Group melalui <a href="mailto:lightermediagroup@gmail.com">lightermediagroup@gmail.com</a> atau ke Jl. Soekarno-Hatta No. 399A, Bandung, Jawa Barat, Indonesia.</p>

    <div class="notice"><strong>Catatan hukum</strong>Dokumen ini perlu diperiksa secara berkala oleh penasihat hukum dan disesuaikan dengan praktik nyata Layanan serta yurisdiksi yang berlaku.</div>
@endsection
