@include('partials.header')

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script src="{{ asset('vendor-local/axios/axios.min.js') }}"></script>

<div class="container-fluid min-vh-100 d-flex align-items-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                
                <div class="text-center mb-4">
                    <h2 class="fw-black text-dark mb-1">Konfirmasi Pembayaran</h2>
                    <p class="text-muted small">Lighter Media Group - CRM System</p>
                </div>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5 bg-white">
                        
                        <div class="rounded-3 p-3 mb-4" style="background-color: #f0f7ff; border: 1px solid #e0eefe;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-uppercase fw-bold text-primary" style="font-size: 0.65rem; letter-spacing: 1px;">Order ID</span>
                                <span class="fw-bold text-dark small">#INV-{{ time() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-uppercase fw-bold text-primary" style="font-size: 0.65rem; letter-spacing: 1px;">Total Tagihan</span>
                                <span class="fw-black text-dark h5 mb-0">Rp 1.000</span>
                            </div>
                        </div>

                        <form id="payment-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nama Pelanggan</label>
                                <input type="text" value="{{ Auth::user()->name }}" disabled 
                                    class="form-control form-control-lg bg-light border-0 small shadow-none" style="font-size: 0.9rem;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">Email</label>
                                <input type="email" value="{{ Auth::user()->email }}" disabled
                                    class="form-control form-control-lg bg-light border-0 small shadow-none" style="font-size: 0.9rem;">
                            </div>

                            <button type="button" id="pay-button"
                                class="btn btn-primary btn-lg w-full fw-bold shadow-sm py-3 transition-all active-scale"
                                style="background: #4e73df; border: none; border-radius: 12px; font-size: 0.9rem;">
                                <i class="fas fa-shield-alt me-2"></i> BAYAR SEKARANG
                            </button>
                        </form>

                        <div class="position-relative my-5">
                            <hr class="text-muted opacity-25">
                            <div class="position-absolute top-50 start-50 translate-middle bg-white px-3">
                                <span class="text-muted small fst-italic" style="font-size: 0.7rem;">Secure Payment by Midtrans</span>
                            </div>
                        </div>

                        <div class="row g-2 opacity-50 text-center grayscale-icons">
                            <div class="col-4"><img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo.svg" style="height: 12px;"></div>
                            <div class="col-4"><img src="https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana.svg" style="height: 12px;"></div>
                            <div class="col-4"><img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" style="height: 12px;"></div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="text-decoration-none text-muted small">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .fw-black { font-weight: 900; }
    .grayscale-icons img { filter: grayscale(100%); transition: 0.3s; }
    .grayscale-icons img:hover { filter: grayscale(0%); }
    .active-scale:active { transform: scale(0.95); }
    .rounded-4 { border-radius: 1.5rem !important; }
    .transition-all { transition: all 0.2s ease-in-out; }
</style>

<script>
    // KRUSIAL: Beritahu Axios untuk selalu mengirim Token CSRF Laravel
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('input[name="_token"]').value;

    document.getElementById('pay-button').onclick = function(e){
        e.preventDefault();
        const btn = this;
        
        // Simpan teks asli buat balikin tombol nanti
        const originalText = '<i class="fas fa-shield-alt me-2"></i> BAYAR SEKARANG';
        
        // Animasi Loading
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menghubungkan...';
        btn.disabled = true;

        // Ambil Token dari Controller
        axios.post("{{ route('payment.getToken') }}")
            .then(response => {
                const snapToken = response.data.snap_token;

                // Tampilkan Pop-up Midtrans
                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        alert("Pembayaran Berhasil!");
                        window.location.href = "{{ route('home') }}";
                    },
                    onPending: function(result) {
                        alert("Menunggu pembayaran Anda. Silakan cek aplikasi bank/e-wallet Anda.");
                        window.location.reload(); 
                    },
                    onError: function(result) {
                        alert("Pembayaran Gagal!");
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    },
                    onClose: function() {
                        // User menutup popup tanpa bayar
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                });
            })
            .catch(error => {
                // Tampilkan pesan error yang lebih detail dari server jika ada
                const errorMsg = error.response?.data?.message || error.message;
                alert('Gagal mengambil token pembayaran: ' + errorMsg);
                
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    };
</script>

@include('partials.footer')
