@extends('layout.app')
@section('content')
<style>
    :root {
        --bs-primary: #4f46e5;
        --facebook-blue: #0866FF;
        --instagram-gradient: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        --slate-100: #f1f5f9;
        --slate-500: #64748b;
    }

    .main-instruction-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding-top: 2rem;
        padding-bottom: 4rem;
        background-color: #f8fafc;
    }

    /* Platform Switcher Style */
    .platform-selector {
        background: #e2e8f0;
        padding: 0.4rem;
        border-radius: 16px;
        display: inline-flex;
        gap: 0.25rem;
        margin-bottom: 2rem;
    }

    .platform-btn {
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--slate-500);
        background: transparent;
    }

    /* Active State Themes */
    .platform-btn.active[data-platform="instagram"] {
        background: white;
        color: #dc2743;
        box-shadow: 0 4px 12px rgba(220, 39, 67, 0.15);
    }

    .platform-btn.active[data-platform="facebook"] {
        background: white;
        color: var(--facebook-blue);
        box-shadow: 0 4px 12px rgba(8, 102, 255, 0.15);
    }

    /* Main Card Layout */
    .main-card {
        background: white;
        border-radius: 32px;
        border: 1px solid #e2e8f0;
        padding: 2.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.03);
        min-height: 650px;
        display: flex;
        flex-direction: column;
        transition: border-top 0.3s ease;
    }

    .main-card.theme-instagram { border-top: 6px solid #dc2743; }
    .main-card.theme-facebook { border-top: 6px solid var(--facebook-blue); }

    .step-indicator-text {
        color: var(--bs-primary);
        font-weight: 800;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Image Container */
    .step-image-wrapper {
        background-color: #f8fafc;
        border-radius: 20px;
        border: 2px dashed #e2e8f0;
        margin: 1.5rem 0;
        overflow: hidden;
        position: relative;
        min-height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .step-image-wrapper img {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: contain;
        display: none;
        padding: 1.5rem;
    }

    .icon-box {
        transition: all 0.3s ease;
    }
    .icon-box-ig { background: #fff5f7; color: #dc2743; }
    .icon-box-fb { background: #ebf5ff; color: var(--facebook-blue); }

    .tips-box {
        background-color: #fffbeb;
        border: 1px solid #fef3c7;
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        gap: 1rem;
    }

    .btn-step-nav {
        padding: 0.8rem 2rem;
        border-radius: 14px;
        font-weight: 700;
        transition: all 0.2s;
    }

    @media (max-width: 768px) {
        .main-card { padding: 1.5rem; min-height: auto; }
        .step-image-wrapper { min-height: 250px; }
    }
</style>

<div class="container main-instruction-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            
            <div class="text-center">
                <div class="platform-selector">
                    <button class="platform-btn active" data-platform="instagram" onclick="switchPlatform('instagram')">
                        <i data-lucide="instagram"></i> Instagram
                    </button>
                    <button class="platform-btn" data-platform="facebook" onclick="switchPlatform('facebook')">
                        <i data-lucide="facebook"></i> Facebook
                    </button>
                </div>
            </div>

            <div class="text-center mb-4">
                <span class="step-indicator-text">Langkah <span id="current-step-num">1</span> dari <span id="total-steps-num">10</span></span>
            </div>

            <div class="main-card theme-instagram" id="instruction-card">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="p-3 rounded-4 icon-box icon-box-ig" id="step-icon-container">
                        <i data-lucide="globe" class="size-32"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold small text-uppercase" id="step-label">Langkah 1</span>
                        <h2 class="fw-bold mb-0" id="step-title">Memuat...</h2>
                    </div>
                </div>

                <div class="flex-grow-1 mb-5">
                    <p class="fs-5 text-secondary leading-relaxed" id="step-description">
                        Sedang memuat instruksi...
                    </p>
                    
                    <div class="step-image-wrapper">
                        <div class="image-loader" id="img-loader">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <p class="small fw-medium d-block">Memuat Visual...</p>
                        </div>
                        <img src="" id="step-visual" alt="Instruksi Visual">
                    </div>

                    <div class="tips-box">
                        <div class="text-warning"><i data-lucide="lightbulb"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Catatan Penting</h6>
                            <p class="small text-muted mb-0" id="step-tips"></p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-top d-flex justify-content-between align-items-center">
                    <button class="btn btn-light btn-step-nav text-secondary" onclick="prevStep()" id="btn-prev">
                        <i data-lucide="chevron-left" class="me-2"></i> Kembali
                    </button>
                    <button class="btn btn-primary btn-step-nav" onclick="nextStep()" id="btn-next">
                        <span>Langkah Selanjutnya</span> <i data-lucide="chevron-right" class="ms-2"></i>
                    </button>
                </div>
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
        });

        // Update Theme Card & Icon Box
        const card = document.getElementById('instruction-card');
        const iconBox = document.getElementById('step-icon-container');
        
        if(platform === 'instagram') {
            card.className = 'main-card theme-instagram';
            iconBox.className = 'p-3 rounded-4 icon-box icon-box-ig';
        } else {
            card.className = 'main-card theme-facebook';
            iconBox.className = 'p-3 rounded-4 icon-box icon-box-fb';
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
        loader.style.display = 'block';
        imgElement.src = step.image;
        imgElement.onload = () => {
            loader.style.display = 'none';
            imgElement.style.display = 'block';
        };

        // Update Icon
        document.getElementById('step-icon-container').innerHTML = `<i data-lucide="${step.icon}" style="width:32px; height:32px"></i>`;

        // Tombol Navigasi
        document.getElementById('btn-prev').style.visibility = currentStep === 0 ? 'hidden' : 'visible';
        
        const btnNext = document.getElementById('btn-next');
        if (currentStep === platformSteps.length - 1) {
            btnNext.innerHTML = 'Selesai <i data-lucide="check" class="ms-2"></i>';
            btnNext.classList.replace('btn-primary', 'btn-success');
        } else {
            btnNext.innerHTML = 'Langkah Selanjutnya <i data-lucide="chevron-right" class="ms-2"></i>';
            btnNext.classList.replace('btn-success', 'btn-primary');
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