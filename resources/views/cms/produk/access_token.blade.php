@extends('layout.app')

@section('content')

<div class="min-h-screen bg-zinc-50 px-4 py-8 sm:px-6 lg:px-8 lg:py-12">

    <div class="mx-auto w-full max-w-4xl">

        {{-- Platform Selector --}}
        <div class="mb-8 flex justify-center">
            <div class="inline-flex gap-1 rounded-2xl border border-zinc-200 bg-zinc-100 p-1.5">

                <button
                    type="button"
                    class="platform-btn active inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-zinc-900 shadow-sm transition-all duration-200"
                    data-platform="instagram"
                    onclick="switchPlatform('instagram')"
                >
                    <i data-lucide="instagram" class="h-4 w-4"></i>
                    <span>Instagram</span>
                </button>

                <button
                    type="button"
                    class="platform-btn inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-zinc-500 transition-all duration-200 hover:bg-white/70 hover:text-zinc-900"
                    data-platform="facebook"
                    onclick="switchPlatform('facebook')"
                >
                    <i data-lucide="facebook" class="h-4 w-4"></i>
                    <span>Facebook</span>
                </button>

            </div>
        </div>


        {{-- Step Indicator --}}
        <div class="mb-5 text-center">
            <span class="text-xs font-bold uppercase tracking-[0.18em] text-zinc-500">
                Langkah
                <span id="current-step-num" class="text-zinc-900">1</span>
                <span class="mx-1 text-zinc-300">dari</span>
                <span id="total-steps-num">10</span>
            </span>
        </div>


        {{-- Main Card --}}
        <div
            id="instruction-card"
            class="main-card theme-instagram flex min-h-[650px] flex-col overflow-hidden rounded-3xl border border-zinc-200 border-t-[5px] border-t-zinc-900 bg-white shadow-[0_20px_50px_rgba(22,22,22,0.05)]"
        >

            {{-- Card Header --}}
            <div class="flex items-center gap-4 px-6 pt-6 sm:px-8 sm:pt-8">

                <div
                    id="step-icon-container"
                    class="icon-box icon-box-ig flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-900 sm:h-16 sm:w-16"
                >
                    <i data-lucide="globe" class="h-7 w-7 sm:h-8 sm:w-8"></i>
                </div>

                <div class="min-w-0">
                    <span
                        class="block text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400"
                        id="step-label"
                    >
                        Langkah 1
                    </span>

                    <h2
                        id="step-title"
                        class="mt-1 text-xl font-bold tracking-tight text-zinc-950 sm:text-2xl"
                    >
                        Memuat...
                    </h2>
                </div>

            </div>


            {{-- Content --}}
            <div class="flex flex-1 flex-col px-6 pb-6 pt-6 sm:px-8 sm:pb-8">

                <p
                    id="step-description"
                    class="text-base font-medium leading-7 text-zinc-600 sm:text-lg sm:leading-8"
                >
                    Sedang memuat instruksi...
                </p>


                {{-- Image --}}
                <div
                    class="step-image-wrapper relative my-6 flex min-h-[250px] flex-1 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 sm:min-h-[380px]"
                >

                    <div
                        id="img-loader"
                        class="flex flex-col items-center justify-center text-center"
                    >
                        <div class="mb-3 h-7 w-7 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-900"></div>

                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            Memuat Visual...
                        </p>
                    </div>

                    <img
                        src=""
                        id="step-visual"
                        alt="Instruksi Visual"
                        class="hidden max-h-[400px] w-full object-contain p-4 sm:p-6"
                    >

                </div>


                {{-- Tips --}}
                <div class="flex gap-4 rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:p-5">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-amber-600 shadow-sm">
                        <i data-lucide="lightbulb" class="h-5 w-5"></i>
                    </div>

                    <div class="min-w-0">
                        <h6 class="mb-1 text-sm font-bold text-zinc-900">
                            Catatan Penting
                        </h6>

                        <p
                            id="step-tips"
                            class="text-sm leading-6 text-zinc-600"
                        ></p>
                    </div>

                </div>

            </div>


            {{-- Navigation --}}
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">

                <button
                    type="button"
                    id="btn-prev"
                    onclick="prevStep()"
                    class="btn-step-nav inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 hover:text-zinc-950"
                >
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                    <span>Kembali</span>
                </button>


                <button
                    type="button"
                    id="btn-next"
                    onclick="nextStep()"
                    class="btn-step-nav inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-5 py-3 text-sm font-bold text-white transition-all duration-200 hover:bg-zinc-800"
                >
                    <span>Langkah Selanjutnya</span>

                    <i
                        data-lucide="chevron-right"
                        class="h-4 w-4"
                    ></i>
                </button>

            </div>

        </div>

    </div>

</div>


<script>
    const instructionData = {
        instagram: [
            { id: 1, title: "Login Meta", icon: "globe", desc: "Login Ke Meta Developer menggunakan akun yang sudah tersambung dengan Instagram Business.", tips: "Gunakan Akun Lighter Production", image: "{{ asset('img/langkah1.png') }}" },
            { id: 2, title: "Pindah Halaman", icon: "layout-grid", desc: "Tekan tombol 'Aplikasi Saya' di pojok kanan atas.", tips: "Pastikan Anda sudah masuk ke dashboard developer", image: "{{ asset('img/langkah2.png') }}" },
            { id: 3, title: "Pilih Aplikasi", icon: "smartphone", desc: "Pilih aplikasi yang akan digunakan sebagai penyimpan API Instagram.", tips: "Gunakan aplikasi yang statusnya sudah 'Live' atau 'In Development'", image: "{{ asset('img/langkah3.png') }}" },
            { id: 4, title: "Siapkan Produk", icon: "layers", desc: "Cari produk Instagram di sidebar dan pilih 'Siapkan'.", tips: "Gunakan Instagram Graph API untuk akses data konten", image: "{{ asset('img/Langkah4.png') }}" },
            { id: 5, title: "Tambah Akun Penguji", icon: "user-plus", desc: "Tambahkan akun Instagram ke menu Peran > Penguji Instagram.", tips: "Akun tersebut harus menerima undangan di setting Instagram-nya", image: "{{ asset('img/Langkah6.png') }}" },
            { id: 6, title: "Terima Undangan", icon: "mail-check", desc: "Buka Instagram > Settings > Website Permissions > Apps & Websites > Tester Invites.", tips: "Klik 'Accept' pada nama aplikasi Anda", image: "{{ asset('img/Langkah7.png') }}" },
            { id: 7, title: "Generate Token", icon: "key", desc: "Kembali ke Meta Developer, masuk ke Instagram > Penyiapan API > Klik 'Generate Token'.", tips: "Simpan token ini di tempat yang aman", image: "{{ asset('img/Langkah9.png') }}" },
            { id: 8, title: "Salin Token", icon: "copy", desc: "Salin token yang muncul dan masukkan ke kolom input yang tersedia di sistem.", tips: "Cek masa berlaku token di Access Token Debugger", image: "{{ asset('img/Langkah10.png') }}" }
        ],

        facebook: [
            { id: 1, title: "Login Meta", icon: "globe", desc: "Buka Portal Developer Facebook dan login dengan akun pengelola Fanspage.", tips: "Gunakan Akun Lighter Production", image: "{{ asset('img/langkah1.png') }}" },
            { id: 2, title: "Pindah Halaman", icon: "layout-grid", desc: "Tekan tombol 'Aplikasi Saya' di pojok kanan atas.", tips: "Pastikan Anda sudah masuk ke dashboard developer", image: "{{ asset('img/langkah2.png') }}" },
            { id: 3, title: "Pilih Aplikasi", icon: "smartphone", desc: "Pilih aplikasi yang akan digunakan'.", tips: "Gunakan aplikasi yang statusnya sudah 'Live' atau 'In Development'", image: "{{ asset('img/langkah3.png') }}" },
            { id: 4, title: "Pindah Halaman", icon: "layout-grid", desc: "Tekan tombol Graph API Explorer.", tips: "", image: "{{ asset('img/fb_langkah4.png') }}" },
            { id: 5, title: "Generate Access Token", icon: "layout-grid", desc: "Tekan token pengguna lalu pilih dapatkan token akses pengguna.", tips: "Pastikan Akun yang terlogin itu adalah pusat akun", image: "{{ asset('img/fb_langkah5.png') }}" },
            { id: 6, title: "Pilih Akun yang tersambung", icon: "layout-grid", desc: "Pilih Akun yang terhubung dengan pusat akun.", tips: "Pastikan memilih akun yang tepat", image: "{{ asset('img/fb_langkah6.png') }}" },
            { id: 7, title: "Pilih Izin yang digunakan", icon: "layout-grid", desc: "tekan tombol tambahkan izin lalu generate access token.", tips: "izin yang dipilih harus pages_manage_engagement, pages_manage_posts, pages_read_management, pages_read_user_content, pages_show_list, dan read_insights", image: "{{ asset('img/fb_langkah7.png') }}" },
            { id: 8, title: "Output", icon: "layout-grid", desc: "Ganti me?fields dengan me/accounts.", tips: "Pastikan Akun yang ingin dihubungkan benar", image: "{{ asset('img/fb_langkah8.png') }}" },
            { id: 9, title: "Salin Acces token", icon: "layout-grid", desc: "Salin Aksen token yang terdapat pada output lalu pindah ke halaman debugger Token Access.", tips: "Pastikan access token yang di salin sesuai dengan data akun yang ingin di tampilkan", image: "{{ asset('img/fb_langkah9.png') }}" },
            { id: 10, title: "Dapat Token", icon: "layout-grid", desc: "Tempelkan Token yang sebelumnya di salin lalu debug dan perpanjang token akses. Salin Token Access lalu masukan ke kolom input platforn facebook yang tersedia di sistem.", tips: "", image: "{{ asset('img/fb_langkah10.png') }}" }
        ]
    };

    let activePlatform = 'instagram';
    let currentStep = 0;

    function switchPlatform(platform) {
        activePlatform = platform;
        currentStep = 0;

        // Update UI Button Active
        document.querySelectorAll('.platform-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.platform === platform);

            if (btn.dataset.platform === platform) {
                btn.classList.remove(
                    'bg-transparent',
                    'text-zinc-500'
                );

                btn.classList.add(
                    'bg-white',
                    'text-zinc-900',
                    'shadow-sm'
                );
            } else {
                btn.classList.remove(
                    'bg-white',
                    'text-zinc-900',
                    'shadow-sm'
                );

                btn.classList.add(
                    'text-zinc-500'
                );
            }
        });

        // Update Theme Card & Icon Box
        const card = document.getElementById('instruction-card');
        const iconBox = document.getElementById('step-icon-container');

        if(platform === 'instagram') {

            card.classList.remove('border-t-blue-600');
            card.classList.add('border-t-zinc-900');

            iconBox.classList.remove(
                'bg-blue-50',
                'text-blue-600'
            );

            iconBox.classList.add(
                'bg-zinc-100',
                'text-zinc-900'
            );

        } else {

            card.classList.remove('border-t-zinc-900');
            card.classList.add('border-t-blue-600');

            iconBox.classList.remove(
                'bg-zinc-100',
                'text-zinc-900'
            );

            iconBox.classList.add(
                'bg-blue-50',
                'text-blue-600'
            );
        }

        updateUI();
    }

    function updateUI() {

        const platformSteps = instructionData[activePlatform];
        const step = platformSteps[currentStep];

        // Update Teks & Counter
        document.getElementById('current-step-num').innerText = currentStep + 1;
        document.getElementById('total-steps-num').innerText = platformSteps.length;
        document.getElementById('step-label').innerText = `Langkah ${step.id}`;
        document.getElementById('step-title').innerText = step.title;
        document.getElementById('step-description').innerText = step.desc;
        document.getElementById('step-tips').innerText = step.tips;

        // Logika Gambar
        const imgElement = document.getElementById('step-visual');
        const loader = document.getElementById('img-loader');

        imgElement.style.display = 'none';
        loader.style.display = 'flex';

        imgElement.src = step.image;

        imgElement.onload = () => {
            loader.style.display = 'none';
            imgElement.style.display = 'block';
        };

        // Update Icon
        document.getElementById('step-icon-container').innerHTML = `
            <i data-lucide="${step.icon}" class="h-7 w-7 sm:h-8 sm:w-8"></i>
        `;

        // Tombol Navigasi
        document.getElementById('btn-prev').style.visibility =
            currentStep === 0 ? 'hidden' : 'visible';

        const btnNext = document.getElementById('btn-next');

        if (currentStep === platformSteps.length - 1) {

            btnNext.innerHTML = `
                <span>Selesai</span>
                <i data-lucide="check" class="h-4 w-4"></i>
            `;

            btnNext.classList.remove(
                'bg-zinc-950',
                'hover:bg-zinc-800'
            );

            btnNext.classList.add(
                'bg-emerald-600',
                'hover:bg-emerald-700'
            );

        } else {

            btnNext.innerHTML = `
                <span>Langkah Selanjutnya</span>
                <i data-lucide="chevron-right" class="h-4 w-4"></i>
            `;

            btnNext.classList.remove(
                'bg-emerald-600',
                'hover:bg-emerald-700'
            );

            btnNext.classList.add(
                'bg-zinc-950',
                'hover:bg-zinc-800'
            );
        }

        lucide.createIcons();
    }

    function nextStep() {

        if (currentStep < instructionData[activePlatform].length - 1) {

            currentStep++;
            updateUI();

        } else {

            // Redirect Selesai
            window.location.href = `{{ route('cms.produk', 'LGTR') }}`;
        }
    }

    function prevStep() {

        if (currentStep > 0) {

            currentStep--;
            updateUI();

        }
    }

    document.addEventListener('DOMContentLoaded', updateUI);
</script>

@endsection