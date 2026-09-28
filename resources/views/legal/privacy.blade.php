@extends('legal.layout')
@section('title', 'Kebijakan Privasi')
@section('content')
    <p class="eyebrow">Informasi Legal</p>
    <h1>Kebijakan Privasi</h1>
    <p class="updated">Terakhir diperbarui: 28 September 2026</p>

    <p>Kebijakan ini menjelaskan cara Lighter Media Group mengakses, menggunakan, menyimpan, dan melindungi data ketika pengguna menghubungkan akun platform pihak ketiga ke ERP.</p>

    <h2>Data yang diakses</h2>
    <p>Kami hanya meminta data yang diizinkan pengguna melalui proses OAuth, seperti identitas akun, halaman atau channel yang dikelola, konten, serta data performa dan insight yang tersedia sesuai izin platform.</p>

    <h2>Penggunaan data</h2>
    <p>Data digunakan untuk menampilkan laporan, analisis, dan fungsi operasional internal yang diminta pengguna. Kami tidak menjual data pribadi atau data akun terhubung kepada pihak lain.</p>

    <h2>Penyimpanan dan keamanan</h2>
    <p>Kredensial koneksi disimpan pada sistem server dan tidak ditampilkan kepada pengguna lain. Akses dibatasi berdasarkan akun, client, dan divisi yang berwenang.</p>

    <h2>Pihak ketiga</h2>
    <p>Integrasi dapat melibatkan Meta, Threads, TikTok, Google, dan YouTube. Penggunaan data juga tunduk pada kebijakan dan izin yang diberikan pada masing-masing platform.</p>

    <h2>Penghapusan dan pencabutan akses</h2>
    <p>Pengguna dapat mencabut koneksi dari platform terkait atau mengikuti <a href="{{ route('legal.data-deletion') }}">petunjuk penghapusan data</a>.</p>

    <h2>Kontak</h2>
    <p>Pertanyaan mengenai privasi dapat dikirim ke <a href="mailto:lightermediagroup@gmail.com">lightermediagroup@gmail.com</a>.</p>
@endsection
