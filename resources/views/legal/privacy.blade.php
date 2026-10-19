@extends('legal.layout')
@section('title', 'Kebijakan Privasi')
@section('content')
    <p class="eyebrow">Informasi Legal</p>
    <h1>Kebijakan Privasi</h1>
    <p class="updated">Berlaku sejak: 7 Oktober 2026 &middot; Terakhir diperbarui: 7 Oktober 2026</p>

    <div class="notice"><strong>Ringkasan penting</strong>Kebijakan ini menjelaskan pemrosesan data pribadi pada ERP Lighter Media Group, sebuah sistem internal dengan akses terbatas. Kebijakan ini harus dibaca bersama <a href="{{ route('legal.terms') }}">Ketentuan Layanan</a> dan kebijakan internal yang relevan.</div>

    <h2>1. Identitas pengendali data</h2>
    <p><strong>PT Lighter Media Group</strong> (“LMG”, “kami”) merupakan pengelola ERP dan, dalam konteks kegiatan internalnya, bertindak sebagai pengendali data pribadi. Alamat kami adalah Jl. Soekarno-Hatta No. 399A, Bandung, Jawa Barat, Indonesia. Pertanyaan privasi dapat dikirim ke <a href="mailto:lightermediagroup@gmail.com">lightermediagroup@gmail.com</a>.</p>
    <p>Untuk data milik klien atau mitra yang diproses berdasarkan instruksi mereka, pembagian peran sebagai pengendali atau prosesor dapat diatur lebih lanjut dalam kontrak terkait. Administrator organisasi mengelola akun dan hak akses atas nama LMG, tetapi tidak boleh memakai data di luar kewenangannya.</p>

    <h2>2. Cakupan Kebijakan</h2>
    <p>Kebijakan ini berlaku terhadap pengguna ERP, termasuk karyawan, administrator, tenaga kontrak, vendor, mitra, atau perwakilan klien yang diberi akses, serta orang yang datanya terdapat dalam catatan operasional, klien, SDM, keuangan, dokumen, atau platform terhubung. Situs atau layanan pihak ketiga memiliki kebijakan privasinya sendiri.</p>

    <h2>3. Data yang kami proses</h2>
    <ul>
        <li><strong>Data identitas dan akun:</strong> nama, email kerja, nomor telepon, foto profil, jabatan, peran, divisi, client yang ditugaskan, status akun, dan kredensial yang telah di-hash.</li>
        <li><strong>Data hubungan kerja dan operasional:</strong> tugas, KPI, daily work, penugasan, catatan aktivitas, dokumen, komunikasi kerja, data payroll, pembayaran, invoice, dan informasi proses bisnis.</li>
        <li><strong>Data klien dan mitra:</strong> identitas kontak, informasi perusahaan, proyek, layanan, kontrak atau dokumen, serta catatan CRM dan transaksi.</li>
        <li><strong>Data platform terhubung:</strong> identitas akun atau profil, halaman, channel, konten, metrik, insight, aset yang dipilih, cakupan izin, token akses dan refresh, serta masa berlaku koneksi, sesuai izin pada Meta, Instagram, Threads, TikTok, Google/YouTube, atau platform terkait.</li>
        <li><strong>Data teknis dan keamanan:</strong> alamat IP, browser atau perangkat, user agent, waktu akses, sesi, tindakan dalam aplikasi, perubahan data, kesalahan sistem, serta log audit dan keamanan.</li>
        <li><strong>Data permintaan:</strong> informasi yang diberikan saat menghubungi dukungan, melaporkan insiden, atau mengajukan permintaan privasi dan penghapusan.</li>
    </ul>
    <p>Kami tidak meminta pengguna memasukkan data pribadi yang tidak diperlukan. Pengguna dilarang mengunggah data sensitif tanpa kebutuhan, dasar, serta kewenangan yang sah.</p>

    <h2>4. Sumber data</h2>
    <p>Data dapat diperoleh langsung dari pengguna; dari administrator, HR, atasan, client, atau unit kerja LMG; dari penggunaan Layanan dan perangkat pengguna; dari dokumen serta sistem internal; dan dari layanan pihak ketiga yang dihubungkan oleh pengguna berwenang melalui API atau OAuth.</p>

    <h2>5. Tujuan dan dasar pemrosesan</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Kategori data</th><th>Sumber</th><th>Tujuan</th><th>Dasar pemrosesan</th><th>Penerima</th><th>Retensi</th></tr></thead>
        <tbody>
            <tr><td>Identitas, akun, peran, dan kontak</td><td>Pengguna, HR, administrator</td><td>Membuat akun, autentikasi, otorisasi, dukungan, komunikasi</td><td>Hubungan kerja atau kontrak; kepentingan sah menjaga operasional dan keamanan</td><td>Administrator dan personel berwenang; penyedia infrastruktur bila diperlukan</td><td>Selama akun atau hubungan terkait aktif, lalu sesuai kebutuhan administrasi dan kewajiban hukum</td></tr>
            <tr><td>Data pekerjaan, KPI, dokumen, CRM, dan operasional</td><td>Pengguna, atasan, client, sistem internal</td><td>Menjalankan, mengawasi, mendokumentasikan, dan melaporkan kegiatan bisnis</td><td>Hubungan kerja atau kontrak; kewajiban hukum; kepentingan sah</td><td>Tim, administrator, manajemen, client atau mitra sesuai kewenangan</td><td>Sesuai masa hubungan kerja/proyek dan jadwal retensi dokumen perusahaan</td></tr>
            <tr><td>Data keuangan, transaksi, invoice, pembayaran, dan payroll</td><td>Pengguna, finance, HR, client atau mitra</td><td>Administrasi keuangan, pembayaran, akuntansi, audit, dan pelaporan</td><td>Kontrak, hubungan kerja, dan kewajiban hukum</td><td>Finance, HR, manajemen, auditor, bank atau otoritas jika sah dan diperlukan</td><td>Sesuai jangka waktu pembukuan, perpajakan, ketenagakerjaan, dan kewajiban hukum</td></tr>
            <tr><td>Profil, konten, metrik, insight, dan token platform</td><td>Pengguna dan platform terhubung</td><td>Integrasi, analisis, pelaporan, dan pengelolaan layanan digital</td><td>Pelaksanaan kontrak atau penugasan; otorisasi koneksi; kepentingan sah</td><td>Tim dan client berwenang; platform terkait; penyedia infrastruktur</td><td>Selama koneksi atau layanan aktif; token dihapus/dinonaktifkan saat koneksi dicabut, dengan tunduk pada cadangan dan hukum</td></tr>
            <tr><td>IP, perangkat, sesi, dan log audit</td><td>Dihasilkan otomatis</td><td>Autentikasi, keamanan, pencegahan penyalahgunaan, audit, dan diagnosis</td><td>Kepentingan sah menjaga keamanan; kewajiban hukum bila berlaku</td><td>Administrator, tim teknologi, penyedia keamanan/infrastruktur</td><td>Selama diperlukan untuk keamanan, audit, insiden, dan kewajiban hukum</td></tr>
            <tr><td>Permintaan dukungan, privasi, dan penghapusan</td><td>Pemohon</td><td>Verifikasi, menjawab, melaksanakan permintaan, dan bukti penanganan</td><td>Kewajiban hukum dan kepentingan sah</td><td>Tim terkait dan penasihat profesional bila diperlukan</td><td>Selama penyelesaian dan sesudahnya sejauh diperlukan sebagai bukti kepatuhan</td></tr>
        </tbody>
    </table></div>
    <p>Dasar pemrosesan diterapkan sesuai konteks dan hukum, termasuk Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi. Jika pemrosesan bergantung pada persetujuan, persetujuan dapat ditarik tanpa memengaruhi keabsahan pemrosesan sebelumnya.</p>

    <h2>6. Data karyawan dan hubungan kerja</h2>
    <p>Data karyawan terutama diproses untuk administrasi dan pelaksanaan hubungan kerja, pemberian tugas, pengelolaan kinerja, penggajian, keamanan, pemenuhan kewajiban perusahaan, dan pembelaan hak hukum. Karena sebagian pemrosesan diperlukan bagi hubungan kerja atau kewajiban hukum, persetujuan bukan selalu menjadi dasar yang tepat dan penarikan persetujuan tidak otomatis menghapus catatan yang wajib dipertahankan.</p>

    <h2>7. Log, pemantauan, dan audit keamanan</h2>
    <p>Layanan mencatat data sesi dan aktivitas untuk autentikasi, penyelesaian masalah, audit, keamanan, serta investigasi dugaan penyalahgunaan. Administrator berwenang dapat meninjau aktivitas sesuai kebutuhan kerja dan kebijakan perusahaan. Pemantauan dilakukan secara proporsional untuk keamanan, kepatuhan, atau operasional yang sah.</p>

    <h2>8. Cookie dan teknologi sejenis</h2>
    <p>ERP menggunakan cookie yang diperlukan untuk menjaga sesi login, melindungi formulir, dan mempertahankan keamanan aplikasi. Cookie ini penting bagi fungsi Layanan. Kami tidak menggunakan cookie iklan pada ERP. Apabila cookie analitik atau kategori non-esensial ditambahkan, Kebijakan dan mekanisme pilihan pengguna akan diperbarui sebagaimana diwajibkan hukum.</p>

    <h2>9. Pembagian data</h2>
    <p>Kami dapat memberikan akses secara terbatas kepada:</p>
    <ul>
        <li>administrator, manajemen, HR, finance, tim teknologi, dan personel LMG sesuai tugas;</li>
        <li>client, afiliasi, vendor, atau mitra apabila diperlukan dan mereka berwenang menerimanya;</li>
        <li>penyedia hosting, basis data, penyimpanan, email, autentikasi, keamanan, komunikasi, dan dukungan teknis;</li>
        <li>Meta/Facebook/Instagram, Threads, TikTok, Google/YouTube, dan platform lain saat integrasi diaktifkan;</li>
        <li>auditor, penasihat hukum, akuntan, atau penyedia profesional dengan kewajiban kerahasiaan; dan</li>
        <li>pengadilan, regulator, aparat, atau pihak berwenang apabila diwajibkan hukum atau diperlukan untuk melindungi hak dan keamanan.</li>
    </ul>
    <p>Kami tidak menjual data pribadi. Penyedia layanan hanya boleh memproses data untuk fungsi yang disepakati dan tunduk pada pengamanan serta kewajiban yang relevan.</p>

    <h2>10. Transfer data lintas negara</h2>
    <p>Penggunaan platform global dan penyedia infrastruktur dapat menyebabkan data diproses atau diakses dari luar Indonesia. Jika transfer lintas negara terjadi, LMG akan menerapkan persyaratan dan perlindungan yang diwajibkan hukum, mempertimbangkan tingkat perlindungan negara penerima, serta menggunakan pengaturan kontraktual atau dasar transfer lain yang sesuai. Lokasi tertentu bergantung pada konfigurasi dan kebijakan penyedia terkait.</p>

    <h2>11. Retensi data</h2>
    <p>Kami menyimpan data hanya selama diperlukan untuk tujuan pengumpulannya, hubungan kerja atau kontrak, keamanan dan audit, penyelesaian sengketa, serta kewajiban hukum. Masa retensi mempertimbangkan jenis dan sensitivitas data, risiko, kebutuhan pemulihan, masa proyek, daluwarsa tuntutan, serta kewajiban pembukuan, pajak, dan ketenagakerjaan. Setelah tidak diperlukan, data dihapus, dianonimkan, atau aksesnya dibatasi secara aman. Salinan cadangan dapat bertahan untuk siklus pencadangan terbatas dan tidak digunakan untuk kegiatan operasional biasa.</p>

    <h2>12. Keamanan data</h2>
    <p>Kami menggunakan langkah administratif dan teknis yang proporsional, termasuk autentikasi, pembatasan akses berbasis peran dan penugasan, pencatatan aktivitas, pengelolaan sesi, serta pembatasan akses ke kredensial koneksi. Tidak ada sistem yang menjamin keamanan absolut. Pengguna wajib menjaga kredensial dan segera melaporkan insiden atau dugaan akses tanpa izin.</p>

    <h2>13. Hak subjek data</h2>
    <p>Sesuai syarat dan pengecualian hukum, subjek data dapat meminta informasi dan akses; melengkapi atau memperbaiki data; mengakhiri, menghapus, atau memusnahkan data; menarik persetujuan; membatasi atau menolak pemrosesan tertentu; memperoleh data dalam format yang sesuai apabila berlaku; serta mengajukan keberatan, pengaduan, atau tuntutan atas pelanggaran.</p>
    <p>Hak tertentu dapat dibatasi jika data harus dipertahankan untuk hubungan kerja, kontrak, kepentingan orang lain, keamanan, pembelaan klaim, atau kewajiban hukum. Kami akan menjelaskan alasan apabila permintaan tidak dapat dipenuhi seluruhnya.</p>

    <h2>14. Cara mengajukan permintaan</h2>
    <p>Kirim permintaan ke <a href="mailto:lightermediagroup@gmail.com?subject=Permintaan%20Privasi">lightermediagroup@gmail.com</a> dengan subjek “Permintaan Privasi”. Sertakan nama, email akun ERP, hubungan dengan LMG atau client terkait, jenis permintaan, dan data yang dimaksud. Kami dapat meminta informasi tambahan yang proporsional untuk memverifikasi identitas dan kewenangan. Jangan mengirim kata sandi atau token.</p>

    <h2>15. Konsekuensi jika data tidak diberikan</h2>
    <p>Jika data yang diperlukan untuk autentikasi, kewajiban kerja, kontrak, keamanan, pembayaran, atau pelaporan tidak diberikan atau tidak akurat, kami mungkin tidak dapat membuat akun, memberikan akses, menjalankan modul terkait, memproses pembayaran, atau memenuhi permintaan pengguna.</p>

    <h2>16. Penonaktifan akun dan penghapusan data</h2>
    <p>Karena akun dikelola organisasi, pengguna tidak dapat selalu menghapus akun secara mandiri. Hubungi administrator atau ikuti <a href="{{ route('legal.data-deletion') }}">Petunjuk Penghapusan Data</a>. Penonaktifan akun menghentikan akses tetapi tidak selalu langsung menghapus seluruh catatan. Data bisnis, keuangan, audit, keamanan, hubungan kerja, atau kontrak dapat tetap disimpan jika memiliki dasar dan masa retensi yang sah.</p>
    <p>Untuk platform terhubung, pengguna berwenang dapat mencabut izin melalui pengaturan platform. Pencabutan menghentikan akses baru, tetapi permintaan penghapusan mungkin tetap diperlukan untuk data yang telah tersimpan di ERP.</p>

    <h2>17. Pengambilan keputusan otomatis</h2>
    <p>ERP tidak dirancang untuk mengambil keputusan yang menimbulkan akibat hukum atau dampak signifikan terhadap seseorang semata-mata secara otomatis. Laporan, metrik, atau rekomendasi digunakan sebagai alat bantu dan harus ditinjau personel berwenang. Jika praktik ini berubah, kami akan menyediakan informasi dan perlindungan yang diwajibkan hukum.</p>

    <h2>18. Privasi anak</h2>
    <p>Layanan ditujukan bagi pengguna yang memperoleh akses untuk kegiatan profesional dan bukan untuk anak-anak. Kami tidak secara sengaja menyediakan akun ERP kepada anak. Jika data anak perlu diproses dalam kegiatan bisnis yang sah, LMG akan mensyaratkan dasar, pembatasan akses, dan perlindungan tambahan sesuai hukum.</p>

    <h2>19. Tautan dan layanan pihak ketiga</h2>
    <p>Layanan dapat memuat tautan atau koneksi ke pihak ketiga. Cara pihak ketiga mengumpulkan dan menggunakan data diatur oleh kebijakan mereka. Pengguna harus meninjau izin sebelum menyambungkan akun dan hanya menyambungkan aset yang berhak dikelolanya.</p>

    <h2>20. Perubahan Kebijakan</h2>
    <p>Kami dapat memperbarui Kebijakan agar sesuai perubahan praktik, fitur, vendor, atau hukum. Tanggal versi terbaru dicantumkan di bagian atas. Perubahan material akan diberitahukan melalui ERP, email, atau kanal internal yang wajar.</p>

    <h2>21. Pengaduan</h2>
    <p>Pengaduan privasi dapat disampaikan melalui kontak di bawah. Kami akan meninjau, memverifikasi, dan menindaklanjuti pengaduan secara wajar. Subjek data juga dapat menggunakan mekanisme pengaduan atau penyelesaian sengketa yang tersedia berdasarkan hukum Indonesia.</p>

    <h2>22. Kontak privasi</h2>
    <p><strong>PT Lighter Media Group</strong><br>Jl. Soekarno-Hatta No. 399A, Bandung, Jawa Barat, Indonesia<br>Email: <a href="mailto:lightermediagroup@gmail.com">lightermediagroup@gmail.com</a></p>

    <div class="notice"><strong>Catatan hukum</strong>Kebijakan ini perlu diperiksa secara berkala oleh penasihat hukum dan disesuaikan dengan praktik nyata aplikasi, kontrak, vendor, lokasi pemrosesan, jadwal retensi, serta peraturan yang berlaku.</div>
@endsection
