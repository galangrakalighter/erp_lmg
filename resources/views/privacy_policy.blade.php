<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Lighter Media Group</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#F3F4F6] antialiased">

    <div class="min-h-screen py-12 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-3xl overflow-hidden">
            
            <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

            <div class="p-8 md:p-16">
                <header class="mb-12">
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">
                        Privacy Policy
                    </h1>
                    <div class="flex items-center text-sm text-gray-500 bg-blue-50 w-fit px-4 py-2 rounded-full">
                        <span class="relative flex h-2 w-2 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        Terakhir diperbarui: 20 Januari 2026
                    </div>
                </header>

                <p class="text-lg text-gray-600 leading-relaxed mb-12">
                    Privasi Anda sangat penting bagi kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, 
                    menggunakan, dan melindungi data Anda saat Anda menggunakan layanan kami.
                </p>

                <div class="space-y-12">
                    
                    <section id="informasi-kami-kumpulkan" class="group">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 group-hover:text-blue-600 transition-colors">
                            Informasi yang Kami Kumpulkan
                        </h2>
                        <div class="text-gray-600 leading-relaxed">
                            <p>Kami mengumpulkan informasi untuk memberikan layanan yang lebih baik kepada semua pengguna kami. Jenis informasi yang kami kumpulkan meliputi:</p>
                            <ul class="list-disc pl-5 mt-3 space-y-2">
                                <li><strong>Informasi Identitas Pribadi:</strong> Nama, alamat email, dan nomor telepon saat Anda mendaftar.</li>
                                <li><strong>Data Log:</strong> Alamat IP, jenis peramban, dan halaman yang Anda kunjungi.</li>
                                <li><strong>Cookies:</strong> Data kecil yang disimpan di perangkat Anda untuk meningkatkan pengalaman navigasi.</li>
                            </ul>
                        </div>
                    </section>

                    <section id="penggunaan-informasi" class="group">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 group-hover:text-blue-600 transition-colors">
                            Bagaimana Kami Menggunakan Informasi
                        </h2>
                        <div class="text-gray-600 leading-relaxed">
                            Informasi yang kami kumpulkan digunakan untuk mengelola akun Anda, memproses transaksi, mengirimkan pembaruan layanan, dan mencegah aktivitas penipuan. Kami tidak akan menjual informasi pribadi Anda kepada pihak ketiga.
                        </div>
                    </section>

                    <section id="keamanan-data" class="group">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 group-hover:text-blue-600 transition-colors">
                            Keamanan Data
                        </h2>
                        <div class="text-gray-600 leading-relaxed">
                            Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang dirancang untuk melindungi keamanan informasi pribadi apa pun yang kami proses. Namun, perlu diingat bahwa transmisi internet tidak pernah 100% aman.
                        </div>
                    </section>

                    <section id="hak-anda" class="group">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 group-hover:text-blue-600 transition-colors">
                            Hak-Hak Anda
                        </h2>
                        <div class="text-gray-600 leading-relaxed">
                            Anda memiliki hak untuk mengakses, memperbarui, atau menghapus informasi pribadi yang kami miliki tentang Anda. Anda dapat melakukan ini melalui pengaturan akun atau dengan menghubungi tim dukungan kami secara langsung.
                        </div>
                    </section>

                </div>

                <div class="mt-16 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Pertanyaan & Keluhan</h3>
                    <p class="text-gray-600">
                        Jika Anda memiliki kekhawatiran mengenai privasi data Anda, silakan hubungi petugas perlindungan data kami di:
                    </p>
                    <p class="mt-4 font-semibold text-indigo-600">
                        privacy@lightermediagroup.com
                    </p>
                </div>

                <div class="mt-12 flex justify-between items-center border-t pt-8">
                    <button 
                        onclick="window.history.back()"
                        class="text-gray-500 hover:text-gray-800 font-medium transition-colors"
                    >
                        &larr; Kembali
                    </button>
                    <p class="text-xs text-gray-400">
                        &copy; {{ date('Y') }} Lighter Media Group.
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
