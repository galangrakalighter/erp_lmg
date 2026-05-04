@include('partials.header')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    .automation-body { background-color: #f4f7fa; font-family: 'Inter', sans-serif; }
    .card-premium { border: none; border-radius: 24px; background: #ffffff; box-shadow: 0 15px 35px rgba(0,0,0,0.05); overflow: hidden; }
    .icon-box { width: 60px; height: 60px; background: #eef2ff; color: #4f46e5; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    .form-label-custom { font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 6px; }
    .input-modern { border-radius: 12px; padding: 10px 14px; border: 2px solid #f1f5f9; background: #f8fafc; transition: all 0.2s ease; font-size: 0.9rem; }
    .input-modern:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); outline: none; }
    .btn-execute { border-radius: 12px; padding: 14px; font-weight: 600; background: #1e293b; color: white; border: none; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .btn-execute:hover { background: #0f172a; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .alert-modern { border-radius: 12px; border: none; font-size: 0.9rem; font-weight: 500; }
</style>

<div class="container py-5 automation-body">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-premium p-4">
                <div class="card-body text-center">
                    <div class="icon-box"><i class="bi bi-layers-half fs-3"></i></div>
                    <h4 class="fw-bold text-dark">Data Integrator</h4>
                    <p class="text-muted small mb-4">Konfigurasi pengambilan data Google Sheet</p>

                    <div class="text-start">
                        <!-- Kategori -->
                        <div class="mb-3">
                            <label class="form-label-custom">Kategori Sistem</label>
                            <select id="automationType" class="form-select input-modern">
                                <option value="minei">Minei Portal</option>
                                <option value="eikyoo">Eikyoo Ecosystem</option>
                            </select>
                        </div>

                        <!-- URL -->
                        <div class="mb-3">
                            <label class="form-label-custom">Link Google Sheet</label>
                            <input type="url" id="sheetUrl" class="form-control input-modern" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                        </div>

                        <!-- Baris Konfigurasi (Header & First Data) -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label-custom">Header Row</label>
                                <input type="number" id="headerRow" class="form-control input-modern" value="1" min="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label-custom">First Data Row</label>
                                <input type="number" id="firstDataRow" class="form-control input-modern" value="2" min="1">
                            </div>
                        </div>

                        <!-- Limit -->
                        <div class="mb-4">
                            <label class="form-label-custom">Limit Data (Opsional)</label>
                            <input type="number" id="limitData" class="form-control input-modern" placeholder="Contoh: 100">
                        </div>
                    </div>

                    <button id="triggerBtn" class="btn btn-execute w-100">
                        <span id="btnText">Eksekusi n8n</span>
                        <i id="btnIcon" class="bi bi-rocket-takeoff-fill"></i>
                        <div id="btnSpinner" class="spinner-border spinner-border-sm d-none"></div>
                    </button>

                    <div id="statusMessage" class="mt-4 d-none alert alert-modern py-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('triggerBtn').addEventListener('click', async function() {
        const btn = this;
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');
        const statusMessage = document.getElementById('statusMessage');
        
        const payload = {
            automation_type: document.getElementById('automationType').value,
            sheet_url: document.getElementById('sheetUrl').value,
            header_row: document.getElementById('headerRow').value,
            first_data_row: document.getElementById('firstDataRow').value,
            limit: document.getElementById('limitData').value
        };

        if (!payload.sheet_url.includes('docs.google.com/spreadsheets')) {
            alert('Link Google Sheet tidak valid!');
            return;
        }

        btn.disabled = true;
        btnText.innerText = "Processing...";
        btnIcon.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        statusMessage.classList.add('d-none');

        try {
            const response = await fetch("{{ route('n8n.trigger') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            statusMessage.classList.remove('d-none');
            statusMessage.innerText = result.message;
            statusMessage.className = `mt-4 alert alert-modern py-3 ${response.ok ? 'alert-success' : 'alert-danger'}`;

            if (response.ok) document.getElementById('sheetUrl').value = '';

        } catch (error) {
            statusMessage.classList.remove('d-none');
            statusMessage.className = "mt-4 alert alert-danger alert-modern";
            statusMessage.innerText = "Error menghubungi server.";
        } finally {
            btn.disabled = false;
            btnText.innerText = "Eksekusi n8n";
            btnIcon.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
        }
    });
</script>

@include('partials.footer')