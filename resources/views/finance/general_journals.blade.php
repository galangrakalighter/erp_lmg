@extends('layout.app')
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between border-bottom">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book mr-2"></i>Jurnal Umum
                    </h5>
                    <div>
                        <!-- Tombol Export PDF Baru -->
                        <button type="button" class="btn btn-danger btn-sm shadow-sm mr-2" onclick="exportJournalPdf()">
                            <i class="fas fa-file-pdf fa-sm mr-1"></i> Export PDF
                        </button>

                        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#modalTambahJurnal">
                            <i class="fas fa-plus fa-sm mr-2"></i>Buat Jurnal Baru
                        </button>
                    </div>
                </div>
                
                <!-- Bagian Filter Data -->
                <div class="card-body bg-light border-bottom py-3">
                    <form id="formFilterJurnal" class="row align-items-end">
                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-dark">Periode Bulan:</label>
                            <input type="month" id="filter_period" class="form-control form-control-sm border-primary" value="{{ date('Y-m') }}" onchange="updateDatesFromPeriod()">
                        </div>

                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-dark">Dari Tanggal:</label>
                            <input type="date" id="filter_start_date" class="form-control form-control-sm border-primary" value="{{ date('Y-m-01') }}" onchange="loadJournalData()">
                        </div>

                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-dark">Sampai Tanggal:</label>
                            <input type="date" id="filter_end_date" class="form-control form-control-sm border-primary" value="{{ date('Y-m-t') }}" onchange="loadJournalData()">
                        </div>

                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-dark">Status:</label>
                            <select id="filter_status" class="form-control form-control-sm border-primary" onchange="loadJournalData()">
                                <option value="">-- Semua --</option>
                                <option value="draft">Draft</option>
                                <option value="posted">Posted</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-dark">Cari No. Jurnal / Invoice / Keterangan:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="filter_search" class="form-control border-primary" placeholder="Ketik keyword...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="loadJournalData()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-1 text-md-right">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="resetJournalFilter()">
                                <i class="fas fa-undo mr-1"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bagian Tabel Main Master-Detail -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover m-0 text-dark" id="journalTable">
                            <thead class="bg-dark text-white small font-weight-bold text-center">
                                <tr>
                                    <th style="width: 12%">Tanggal / No. Jurnal</th>
                                    <th style="width: 15%">No. Invoice / Keterangan</th>
                                    <th style="width: 15%">Kode Akun</th>
                                    <th style="width: 23%">Nama Akun COA</th>
                                    <th style="width: 15%">Debit (Rp)</th>
                                    <th style="width: 15%">Kredit (Rp)</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="journalTableBody" class="small">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Sedang memuat data jurnal...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="modalTambahJurnal" tabindex="-1" role="dialog" aria-labelledby="modalTambahJurnalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTambahJurnalLabel">
                    <i class="fas fa-edit mr-2"></i>Input Transaksi Jurnal Umum
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambahJurnal" action="{{ route('general_journal.store') }}" method="POST">
                @csrf
                <div class="modal-body text-dark">
                    <!-- Form Atas (Header Jurnal) -->
                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-secondary">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_transaksi" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-secondary">No. Invoice Referensi (Opsional)</label>
                            <input type="text" name="no_invoice" class="form-control" placeholder="Contoh: INV/2026/0012">
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-secondary">Keterangan Umum <span class="text-danger">*</span></label>
                            <textarea name="keterangan" class="form-control" rows="1" placeholder="Contoh: Pembayaran sewa gedung kantor" required></textarea>
                        </div>

                        <div class="col-md-3 form-group mt-2">
                            <label class="small font-weight-bold text-secondary">Status Awal Jurnal <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="draft" selected>Draft</option>
                                <option value="posted">Posted</option>
                            </select>
                        </div>
                        
                        <div class="col-md-5 form-group mt-2">
                            <label class="small font-weight-bold text-secondary">Aktivitas Arus Kas (Metode Langsung) <span class="text-danger">*</span></label>
                            <select name="cash_flow_activity" class="form-control" required>
                                <option value="non_cash" selected>Bukan Transaksi Kas (Non-Kas)</option>
                                <option value="operasional">Operasional</option>
                                <option value="investasi">Investasi</option>
                                <option value="pendanaan">Pendanaan</option>
                            </select>
                            <small class="form-text text-muted">Pilih jenis aktivitas jika transaksi ini mengalirkan uang tunai/bank.</small>
                        </div>
                    </div>

                    <!-- Form Bawah (Detail Akun Ledger) -->
                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-list mr-2"></i>Rincian Akun Transaksi</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tableJurnalEntries">
                            <thead class="bg-light text-center small font-weight-bold">
                                <tr>
                                    <th style="width: 40%">Pilih Akun Chart Of Account (COA) <span class="text-danger">*</span></th>
                                    <th style="width: 25%">Keterangan Item Baris (Opsional)</th>
                                    <th style="width: 15%">Debit (Rp)</th>
                                    <th style="width: 15%">Kredit (Rp)</th>
                                    <th style="width: 5%">#</th>
                                </tr>
                            </thead>
                            <tbody id="entriesRowContainer">
                                <!-- Baris Pertama (Default Minimal Terpasang 2 baris awal) -->
                                <tr class="entry-row">
                                    <td>
                                        <select name="items[0][chart_of_account_id]" class="form-control form-control-sm select-coa-target" required>
                                            <option value="">- Cari / Pilih Akun COA -</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ strtoupper($acc->type) }}">
                                                    {{ $acc->kode }} - {{ $acc->nama_akun }} ({{ $acc->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="items[0][keterangan_item]" class="form-control form-control-sm" placeholder="Keterangan baris..."></td>
                                    <td><input type="number" name="items[0][debit]" class="form-control form-control-sm input-nominal input-debit" value="0" min="0" step="0.01" required></td>
                                    <td><input type="number" name="items[0][kredit]" class="form-control form-control-sm input-nominal input-kredit" value="0" min="0" step="0.01" required></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger disabled-btn" disabled><i class="fas fa-trash"></i></button></td>
                                </tr>
                                <!-- Baris Kedua -->
                                <tr class="entry-row">
                                    <td>
                                        <select name="items[1][chart_of_account_id]" class="form-control form-control-sm select-coa-target" required>
                                            <option value="">- Cari / Pilih Akun COA -</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ strtoupper($acc->type) }}">
                                                    {{ $acc->kode }} - {{ $acc->nama_akun }} ({{ $acc->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="items[1][keterangan_item]" class="form-control form-control-sm" placeholder="Keterangan baris..."></td>
                                    <td><input type="number" name="items[1][debit]" class="form-control form-control-sm input-nominal input-debit" value="0" min="0" step="0.01" required></td>
                                    <td><input type="number" name="items[1][kredit]" class="form-control form-control-sm input-nominal input-kredit" value="0" min="0" step="0.01" required></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger disabled-btn" disabled><i class="fas fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-success shadow-sm" onclick="addNewEntryRow()">
                                            <i class="fas fa-plus-circle mr-1"></i>Tambah Baris Akun
                                        </button>
                                    </td>
                                    <td class="text-right small align-middle">TOTAL BALANCE:</td>
                                    <td><input type="text" id="total_debit_label" class="form-control form-control-sm text-right font-weight-bold text-success border-0 bg-transparent" value="Rp 0" readonly></td>
                                    <td><input type="text" id="total_kredit_label" class="form-control form-control-sm text-right font-weight-bold text-danger border-0 bg-transparent" value="Rp 0" readonly></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Alert Peringatan Jika Belum Seimbang -->
                    <div id="balance_alert" class="alert alert-warning py-2 small d-none mt-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Jumlah Debit dan Kredit tidak seimbang! Selisih: <span id="balance_diff_text" class="font-weight-bold">Rp 0</span>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSimpanJurnal" class="btn btn-primary shadow-sm btn-sm" disabled>
                        <i class="fas fa-save mr-1"></i>Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditJurnal" tabindex="-1" role="dialog" aria-labelledby="modalEditJurnalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold" id="modalEditJurnalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Transaksi Jurnal Umum
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Action URL diisi dinamis via Javascript -->
            <form id="formEditJurnal" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body text-dark">
                    <!-- Form Atas (Header Jurnal) -->
                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-secondary">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_transaksi" id="edit_tgl_transaksi" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-secondary">No. Invoice Referensi (Opsional)</label>
                            <input type="text" name="no_invoice" id="edit_no_invoice" class="form-control" placeholder="Contoh: INV/2026/0012">
                        </div>
                        <div class="col-md-5 form-group">
                            <label class="small font-weight-bold text-secondary">Keterangan Umum <span class="text-danger">*</span></label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="1" required></textarea>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-secondary">Status Jurnal <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="draft">Draft (Simpan Sementara)</option>
                                <option value="posted">Posted (Masuk Buku Besar)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Form Bawah (Detail Akun Ledger) -->
                    <h6 class="font-weight-bold text-info mb-3"><i class="fas fa-list mr-2"></i>Rincian Akun Transaksi</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tableJurnalEditEntries">
                            <thead class="bg-light text-center small font-weight-bold">
                                <tr>
                                    <th style="width: 40%">Pilih Akun Chart Of Account (COA) <span class="text-danger">*</span></th>
                                    <th style="width: 25%">Keterangan Item Baris (Opsional)</th>
                                    <th style="width: 15%">Debit (Rp)</th>
                                    <th style="width: 15%">Kredit (Rp)</th>
                                    <th style="width: 5%">#</th>
                                </tr>
                            </tbody>
                            <tbody id="editEntriesRowContainer">
                                <!-- Baris data akan di-inject otomatis melalui fetch detail JavaScript -->
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-info shadow-sm" onclick="addNewEditEntryRow()">
                                            <i class="fas fa-plus-circle mr-1"></i>Tambah Baris Akun
                                        </button>
                                    </td>
                                    <td class="text-right small align-middle">TOTAL BALANCE:</td>
                                    <td><input type="text" id="edit_total_debit_label" class="form-control form-control-sm text-right font-weight-bold text-success border-0 bg-transparent" value="Rp 0" readonly></td>
                                    <td><input type="text" id="edit_total_kredit_label" class="form-control form-control-sm text-right font-weight-bold text-danger border-0 bg-transparent" value="Rp 0" readonly></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Alert Peringatan Seimbang -->
                    <div id="edit_balance_alert" class="alert alert-warning py-2 small d-none mt-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Jumlah Debit dan Kredit tidak seimbang! Selisih: <span id="edit_balance_diff_text" class="font-weight-bold">Rp 0</span>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnUpdateJurnal" class="btn btn-info shadow-sm btn-sm" disabled>
                        <i class="fas fa-save mr-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapusJurnal" tabindex="-1" role="dialog" aria-labelledby="modalHapusJurnalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="modalHapusJurnalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus Transaksi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formHapusJurnal" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body text-dark">
                    <p>Apakah Anda yakin ingin menghapus/membatalkan transaksi jurnal ini?</p>
                    <div class="card bg-light border p-3">
                        <span class="small text-secondary d-block">Nomor Jurnal:</span>
                        <h5 id="hapus_no_jurnal" class="font-weight-bold text-danger mb-2">-</h5>
                        <span class="small text-secondary d-block">Keterangan:</span>
                        <p id="hapus_keterangan" class="mb-0 small text-dark">-</p>
                    </div>
                    <small class="text-danger d-block mt-3 font-italic">
                        *Peringatan: Tindakan ini akan menghapus data header beserta seluruh rincian akun ledger yang terikat didalamnya.
                    </small>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger shadow-sm btn-sm">
                        <i class="fas fa-trash mr-1"></i>Ya, Hapus Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let entryRowIndex = 2; 

    // 1. Fungsi Menambah Baris Baru (Tetap Mempertahankan data-type hasil clone)
    function addNewEntryRow() {
        const container = document.getElementById('entriesRowContainer');
        const masterSelect = document.querySelector('.select-coa-target');
        const optionsHtml = masterSelect ? masterSelect.innerHTML : '<option value="">- Cari / Pilih Akun COA -</option>';

        const newRowHtml = `
            <tr class="entry-row">
                <td>
                    <select name="items[${entryRowIndex}][chart_of_account_id]" class="form-control form-control-sm select-coa-target" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td><input type="text" name="items[${entryRowIndex}][keterangan_item]" class="form-control form-control-sm" placeholder="Keterangan baris..."></td>
                <td><input type="number" name="items[${entryRowIndex}][debit]" class="form-control form-control-sm input-nominal input-debit" value="0" min="0" step="0.01" required></td>
                <td><input type="number" name="items[${entryRowIndex}][kredit]" class="form-control form-control-sm input-nominal input-kredit" value="0" min="0" step="0.01" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger row-delete-btn" onclick="removeEntryRow(this)"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        
        container.insertAdjacentHTML('beforeend', newRowHtml);
        entryRowIndex++;
        calculateJournalTotals();
    }

    let editEntryRowIndex = 2;

    function addNewEditEntryRow() {
        const container = document.getElementById('editEntriesRowContainer');
        
        // Mengambil pilihan opsi COA dari elemen select yang ada di dalam modal edit, 
        // atau fallback ke select utama jika belum ada baris di modal edit.
        const masterSelect = container.querySelector('.select-coa-target') || document.querySelector('.select-coa-target');
        const optionsHtml = masterSelect ? masterSelect.innerHTML : '<option value="">- Cari / Pilih Akun COA -</option>';

        const newRowHtml = `
            <tr class="entry-row">
                <td>
                    <select name="items[${editEntryRowIndex}][chart_of_account_id]" class="form-control form-control-sm select-coa-target" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td><input type="text" name="items[${editEntryRowIndex}][keterangan_item]" class="form-control form-control-sm" placeholder="Keterangan baris..."></td>
                <td><input type="number" name="items[${editEntryRowIndex}][debit]" class="form-control form-control-sm input-nominal input-debit" value="0" min="0" step="0.01" required></td>
                <td><input type="number" name="items[${editEntryRowIndex}][kredit]" class="form-control form-control-sm input-nominal input-kredit" value="0" min="0" step="0.01" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger row-delete-btn" onclick="removeEntryRow(this)"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        
        container.insertAdjacentHTML('beforeend', newRowHtml);
        editEntryRowIndex++;
        
        // Panggil fungsi kalkulasi total khusus edit jika sudah tersedia di skrip Anda
        if (typeof calculateEditJournalTotals === 'function') {
            calculateEditJournalTotals();
        }
    }

    function exportJournalPdf() {
        let startDate = document.getElementById('filter_start_date').value;
        let endDate = document.getElementById('filter_end_date').value;
        let status = document.getElementById('filter_status').value;
        let search = document.getElementById('filter_search').value;

        let params = new URLSearchParams({
            start_date: startDate,
            end_date: endDate,
            status: status,
            search: search
        });

        // Sesuaikan route endpoint export Anda
        window.open(`{{ route('journals.export-pdf') }}?${params.toString()}`, '_blank');
    }

    function removeEntryRow(button) {
        button.closest('tr').remove();
        calculateJournalTotals();
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('select-coa-target')) {
            const currentRow = e.target.closest('tr');
            const selectedOption = e.target.options[e.target.selectedIndex];
            
            // Ambil data-type dari option (D atau K)
            const coaType = selectedOption.getAttribute('data-type'); 
            
            const debitInput  = currentRow.querySelector('.input-debit');
            const kreditInput = currentRow.querySelector('.input-kredit');

            // Reset perlakuannya terlebih dahulu
            debitInput.removeAttribute('readonly');
            kreditInput.removeAttribute('readonly');
            debitInput.classList.remove('bg-light');
            kreditInput.classList.remove('bg-light');
            console.log(coaType);
            
            // if (coaType === 'D') {
            //     // Jika tipe D (Debit): Kredit dikunci & dijadikan 0
            //     kreditInput.value = 0;
            //     kreditInput.setAttribute('readonly', true);
            //     kreditInput.classList.add('bg-light'); // Efek abu-abu menandakan terkunci
            //     debitInput.focus();
            // } else if (coaType === 'K') {
            //     // Jika tipe K (Kredit): Debit dikunci & dijadikan 0
            //     debitInput.value = 0;
            //     debitInput.setAttribute('readonly', true);
            //     debitInput.classList.add('bg-light'); // Efek abu-abu menandakan terkunci
            //     kreditInput.focus();
            // } else {
            //     // Jika user memilih kembali ke "- Cari / Pilih Akun COA -"
            //     debitInput.value = 0;
            //     kreditInput.value = 0;
            // }

            calculateJournalTotals();
        }

        if (e.target.classList.contains('select-edit-coa-target')) {
            const currentRow = e.target.closest('tr');
            const selectedOption = e.target.options[e.target.selectedIndex];
            const coaType = selectedOption.getAttribute('data-type'); 
            
            const debitInput  = currentRow.querySelector('.input-edit-debit');
            const kreditInput = currentRow.querySelector('.input-edit-kredit');

            debitInput.removeAttribute('readonly');
            kreditInput.removeAttribute('readonly');
            debitInput.classList.remove('bg-light');
            kreditInput.classList.remove('bg-light');

            calculateEditTotals();
        }
    });

    // Fungsi inti menghitung neraca keseimbangan debit kredit
    function calculateJournalTotals() {
        let totalDebit = 0;
        let totalKredit = 0;

        document.querySelectorAll('.input-debit').forEach(input => {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.input-kredit').forEach(input => {
            totalKredit += parseFloat(input.value) || 0;
        });

        document.getElementById('total_debit_label').value = 'Rp ' + totalDebit.toLocaleString('id-ID');
        document.getElementById('total_kredit_label').value = 'Rp ' + totalKredit.toLocaleString('id-ID');

        const alertBox = document.getElementById('balance_alert');
        const btnSimpan = document.getElementById('btnSimpanJurnal');
        const diffText = document.getElementById('balance_diff_text');
        const diff = Math.abs(totalDebit - totalKredit);

        if (totalDebit.toFixed(2) === totalKredit.toFixed(2) && totalDebit > 0) {
            alertBox.classList.add('d-none');
            btnSimpan.removeAttribute('disabled');
        } else {
            alertBox.classList.remove('d-none');
            btnSimpan.setAttribute('disabled', 'disabled');
            
            if (totalDebit === 0 && totalKredit === 0) {
                diffText.innerText = "Nominal transaksi tidak boleh kosong.";
            } else {
                diffText.innerText = 'Rp ' + diff.toLocaleString('id-ID');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadJournalData();
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-nominal')) {
            calculateJournalTotals();
        }

        if (e.target.classList.contains('input-edit-nominal')) {
            calculateEditTotals();
        }
    });

    function loadJournalData() {
        const startDate = document.getElementById('filter_start_date').value;
        const endDate = document.getElementById('filter_end_date').value;
        const search = document.getElementById('filter_search').value;
        const status = document.getElementById('filter_status').value;
        
        const tbody = document.getElementById('journalTableBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data transaksi...</td></tr>`;
        }
        
        let params = new URLSearchParams({ start_date: startDate, end_date: endDate });
        if (search) params.append('search', search);
        if (status) params.append('status', status);

        let url = '{{ route("general_journal.data") }}?' + params.toString();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(response => {
            console.log(response);
            const data = response.journals;
            let lockedPeriods = response.isLocked;
            console.log(lockedPeriods);
            
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ditemukan transaksi jurnal pada periode ini.</td></tr>`;
                return;
            }

            // Helper: Cek apakah periode dari tanggal transaksi ada di list terkunci
            function isPeriodLocked(dateString) {
                if (!dateString) return false;
                
                const txDate = new Date(dateString);
                if (isNaN(txDate.getTime())) return false;

                const now = new Date();
                
                if (txDate.getFullYear() === now.getFullYear() && txDate.getMonth() === now.getMonth()) {
                    return false;
                }

                const period = `${txDate.getFullYear()}-${('0' + (txDate.getMonth() + 1)).slice(-2)}`;
                const periodsArray = Array.isArray(lockedPeriods) ? lockedPeriods.map(p => String(p).trim()) : [];
                
                return periodsArray.includes(period);
            }

            let currentJournalId = null;

            data.forEach(row => {
                const debitVal = parseFloat(row.debit) || 0;
                const kreditVal = parseFloat(row.kredit) || 0;

                const debitFormatted = debitVal > 0 ? 'Rp. ' + debitVal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '';
                const kreditFormatted = kreditVal > 0 ? 'Rp. ' + kreditVal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '';
                
                let dateCell = '';
                let descCell = '';
                let statusCell = '';
                let actionCell = '';
                const rowClass = currentJournalId !== row.journal_id ? 'border-top-double' : '';

                // Jika ini adalah baris pertama untuk journal_id tersebut, isi datanya
                if (currentJournalId !== row.journal_id) {
                    const isLocked = row.status === 'posted' || isPeriodLocked(row.tgl_transaksi);

                    let statusBadge = '';
                    if (row.status === 'posted') {
                        statusBadge = `<br><span class="badge badge-success small mt-1"><i class="fas fa-check-circle mr-1"></i>Posted</span>`;
                    } else if (isLocked) {
                        statusBadge = `<br><span class="badge badge-secondary small mt-1"><i class="fas fa-lock mr-1"></i>Locked</span>`;
                    } else {
                        statusBadge = `<br><span class="badge badge-warning small mt-1"><i class="fas fa-file-alt mr-1"></i>Draft</span>`;
                    }

                    dateCell = `<b>${row.tgl_transaksi}</b><br><small class="text-primary">${row.no_jurnal}</small>`;
                    descCell = `<b>${row.no_invoice || '-'}</b><br><span class="text-muted">${row.keterangan_header}</span>`;
                    
                    // Status dan Action hanya muncul sekali di baris pertama
                    statusCell = `${statusBadge}`;
                    
                    actionCell = `
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-light text-info border" 
                                onclick="openEditJournalModal(${row.journal_id})" title="Edit Jurnal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-light text-danger border" 
                                onclick="voidJournal(${row.journal_id})" title="Void Jurnal">
                                <i class="fas fa-ban"></i>
                            </button>
                        </div>
                    `;
                }

                const trHtml = `
                    <tr class="${rowClass}">
                        <td>${dateCell}</td>
                        <td>${descCell}</td>
                        <td class="text-center">${row.kode_akun}</td>
                        <td>
                            <span class="${parseFloat(row.kredit) > 0 ? 'pl-4 text-secondary' : 'font-weight-bold'}">
                                ${row.nama_akun}
                            </span>
                            ${row.keterangan_item ? `<br><small class="text-muted font-italic">(${row.keterangan_item})</small>` : ''}
                        </td>
                        <td class="text-right text-success font-mono">${debitFormatted}</td>
                        <td class="text-right text-danger font-mono">${kreditFormatted}</td>
                        <td class="text-center font-weight-bold align-middle">${statusCell}</td>
                        <td class="text-center align-middle">${actionCell}</td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', trHtml);
                
                // Perbarui currentJournalId setelah baris diproses
                currentJournalId = row.journal_id;
            });
        })
        .catch(err => {
            console.error("Gagal memuat jurnal:", err);
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data transaksi dari server.</td></tr>`;
        });
    }

    let editRowIndex = 0;

    function openEditJournalModal(id) {
        // Set Action Form Dinamis
        const form = document.getElementById('formEditJurnal');
        form.action = `/general_journal/${id}`; // Sesuaikan dengan endpoint rute update kamu

        const container = document.getElementById('editEntriesRowContainer');
        container.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">Mengambil rincian data...</td></tr>';
        
        // Buka Modal
        $('#modalEditJurnal').modal('show');

        // Ambil data via Ajax/Fetch
        fetch(`/general_journal/${id}`)
            .then(res => res.json())
            .then(data => {
                console.log(data);
                // Set Field Header
                document.getElementById('edit_tgl_transaksi').value = data.header.tgl_transaksi;
                document.getElementById('edit_no_invoice').value = data.header.no_invoice || '';
                document.getElementById('edit_keterangan').value = data.header.keterangan;

                container.innerHTML = '';
                editRowIndex = 0;

                // Ambil master select untuk kloning options COA
                const masterSelect = document.querySelector('.select-coa-target');
                const optionsHtml = masterSelect ? masterSelect.innerHTML : '';

                // Render baris items hasil dari database
                data.items.forEach(item => {
                    const isD = item.coa_type.toUpperCase() === 'D';
                    
                    const trHtml = `
                        <tr class="edit-entry-row">
                            <td>
                                <select name="items[${editRowIndex}][chart_of_account_id]" class="form-control form-control-sm select-edit-coa-target" required>
                                    ${optionsHtml}
                                </select>
                            </td>
                            <td><input type="text" name="items[${editRowIndex}][keterangan_item]" class="form-control form-control-sm" value="${item.keterangan_item || ''}" placeholder="Keterangan baris..."></td>
                            <td><input type="number" name="items[${editRowIndex}][debit]" class="form-control form-control-sm input-edit-nominal input-edit-debit ${!isD ? 'bg-light' : ''}" value="${parseFloat(item.debit)}" min="0" step="0.01" required></td>
                            <td><input type="number" name="items[${editRowIndex}][kredit]" class="form-control form-control-sm input-edit-nominal input-edit-kredit ${isD ? 'bg-light' : ''}" value="${parseFloat(item.kredit)}" min="0" step="0.01" required></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); calculateEditTotals();"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    container.insertAdjacentHTML('beforeend', trHtml);
                    
                    // Set value select option terpilih
                    const currentSelect = container.querySelector(`select[name="items[${editRowIndex}][chart_of_account_id]"]`);
                    currentSelect.value = item.chart_of_account_id;

                    editRowIndex++;
                });

                calculateEditTotals();
            });
    }

    function openEditJournalModal(id) {
        // 1. Set URL Action Form secara dinamis ke endpoint update
        const form = document.getElementById('formEditJurnal');
        form.action = `/general_journal/${id}`; // Sesuaikan dengan named route atau URL project-mu

        const container = document.getElementById('editEntriesRowContainer');
        container.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Mengambil rincian data...</td></tr>';
        
        // Tampilkan Modal Edit
        $('#modalEditJurnal').modal('show');

        // 2. Ambil data transaksi dari backend via AJAX Fetch
        fetch(`/general_journal/${id}`)
            .then(res => {
                if (!res.ok) throw new Error('Gagal memuat data dari server');
                return res.json();
            })
            .then(data => {
                // Set Nilai Field Bagian Atas (Header)
                document.getElementById('edit_tgl_transaksi').value = data.header.tgl_transaksi;
                document.getElementById('edit_no_invoice').value = data.header.no_invoice || '';
                document.getElementById('edit_keterangan').value = data.header.keterangan;
                
                // Set Dropdown Status Baru (Draft/Posted)
                document.getElementById('edit_status').value = data.header.status;

                // Kosongkan container loading sebelum merender baris rincian akun
                container.innerHTML = '';
                editRowIndex = 0; // Reset index baris agar berurutan dari 0 kembali

                // Ambil master select untuk kloning options COA
                const masterSelect = document.querySelector('.select-coa-target');
                const optionsHtml = masterSelect ? masterSelect.innerHTML : '';

                console.log(data);

                // 3. Render Baris Akun Rincian hasil dari database (Gunakan data.details sesuai tabel barumu)
                data.items.forEach(detail => {
                    // Tentukan input mana yang harus di-lock readonly berdasarkan tipe COA aslinya (opsional)
                    const isD = detail.coa_type ? detail.coa_type.toUpperCase() === 'D' : true;
                    
                    const trHtml = `
                        <tr class="edit-entry-row">
                            <td>
                                <select name="items[${editRowIndex}][chart_of_account_id]" class="form-control form-control-sm select-edit-coa-target" required>
                                    ${optionsHtml}
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[${editRowIndex}][keterangan_item]" class="form-control form-control-sm" value="${detail.keterangan_item || ''}" placeholder="Keterangan baris...">
                            </td>
                            <td>
                                <input type="number" name="items[${editRowIndex}][debit]" class="form-control form-control-sm input-edit-nominal input-edit-debit" value="${parseFloat(detail.debit)}" min="0" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="items[${editRowIndex}][kredit]" class="form-control form-control-sm input-edit-nominal input-edit-kredit" value="${parseFloat(detail.kredit)}" min="0" step="0.01" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); calculateEditTotals();">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    container.insertAdjacentHTML('beforeend', trHtml);
                    
                    // Set nilai select option COA yang sedang aktif pada baris ini
                    const currentSelect = container.querySelector(`select[name="items[${editRowIndex}][chart_of_account_id]"]`);
                    if (currentSelect) {
                        currentSelect.value = detail.chart_of_account_id;
                    }

                    editRowIndex++;
                });

                // Hitung kalkulasi total debit & kredit pertama kali saat data ter-load
                calculateEditTotals();
            })
            .catch(err => {
                container.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle mr-1"></i> ${err.message}</td></tr>`;
            });
    }

    function calculateEditTotals() {
        let totalDebit = 0, totalKredit = 0;

        document.querySelectorAll('.input-edit-debit').forEach(i => totalDebit += parseFloat(i.value) || 0);
        document.querySelectorAll('.input-edit-kredit').forEach(i => totalKredit += parseFloat(i.value) || 0);

        document.getElementById('edit_total_debit_label').value = 'Rp ' + totalDebit.toLocaleString('id-ID');
        document.getElementById('edit_total_kredit_label').value = 'Rp ' + totalKredit.toLocaleString('id-ID');

        const alertBox = document.getElementById('edit_balance_alert');
        const btnUpdate = document.getElementById('btnUpdateJurnal');
        const diff = Math.abs(totalDebit - totalKredit);

        if (totalDebit.toFixed(2) === totalKredit.toFixed(2) && totalDebit > 0) {
            alertBox.classList.add('d-none');
            btnUpdate.removeAttribute('disabled');
        } else {
            alertBox.classList.remove('d-none');
            btnUpdate.setAttribute('disabled', 'disabled');
            document.getElementById('edit_balance_diff_text').innerText = 'Rp ' + diff.toLocaleString('id-ID');
        }
    }

    function voidJournal(id) {
        // Set endpoint Action Form secara dinamis
        const form = document.getElementById('formHapusJurnal');
        form.action = `/general_journal/${id}`; // Sesuaikan dengan konvensi route DELETE kamu

        // Reset dan tampilkan indikator loading text di modal sebelum fetch data selesai
        document.getElementById('hapus_no_jurnal').innerText = "Memuat...";
        document.getElementById('hapus_keterangan').innerText = "Memuat data transaksi...";

        // Tampilkan modal hapus
        $('#modalHapusJurnal').modal('show');

        // Ambil detail singkat dari server untuk ditampilkan sebagai konfirmasi ke user
        fetch(`/general_journal/${id}`)
            .then(res => {
                if (!res.ok) throw new Error();
                return res.json();
            })
            .then(data => {
                document.getElementById('hapus_no_jurnal').innerText = data.header.no_jurnal;
                document.getElementById('hapus_keterangan').innerText = data.header.keterangan;
            })
            .catch(() => {
                document.getElementById('hapus_no_jurnal').innerText = "Error";
                document.getElementById('hapus_keterangan').innerText = "Gagal memuat detail ringkasan transaksi.";
            });
    }

    // Handler Submit Form Hapus via AJAX Fetch (Mencegah reload halaman penuh)
    document.getElementById('formHapusJurnal').addEventListener('submit', function(e) {
        e.preventDefault();

        const actionUrl = this.action;
        
        fetch(actionUrl, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            $('#modalHapusJurnal').modal('hide');
            
            if (data.status === 'success') {
                // Tampilkan notifikasi sukses (bisa diganti Toastr / Swal)
                alert(data.message); 
                
                // Segarkan / reload kembali tabel jurnal utama kamu tanpa reload page
                loadJournalData(); 
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan sistem saat memproses penghapusan.');
        });
    });

    function isPeriodLocked(dateString) {
        const txDate = new Date(dateString);
        const nowDate = new Date();

        const currentYear = nowDate.getFullYear();
        const currentMonth = nowDate.getMonth(); // Rentang 0 - 11

        const txYear = txDate.getFullYear();
        const txMonth = txDate.getMonth();

        if (txYear < currentYear) {
            return true;
        } else if (txYear === currentYear && txMonth < currentMonth) {
            return true;
        }
        return false;
    }

    function updateDatesFromPeriod() {
        const periodVal = document.getElementById('filter_period').value; // Hasil: "YYYY-MM"
        
        if (periodVal) {
            const [year, month] = periodVal.split('-');
            
            // Tanggal awal selalu tanggal 01
            const startDate = `${year}-${month}-01`;
            
            // Mencari tanggal terakhir di bulan tersebut
            const lastDay = new Date(year, month, 0).getDate(); 
            const endDate = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
            
            // Set value ke input date asli
            document.getElementById('filter_start_date').value = startDate;
            document.getElementById('filter_end_date').value = endDate;
        }
        
        // Tarik data ulang dari server
        loadJournalData();
    }

    function resetJournalFilter() {
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = String(now.getMonth() + 1).padStart(2, '0');
        const lastDay = new Date(currentYear, currentMonth, 0).getDate();

        // Kembalikan ke bulan berjalan sekarang
        document.getElementById('filter_period').value = `${currentYear}-${currentMonth}`;
        document.getElementById('filter_start_date').value = `${currentYear}-${currentMonth}-01`;
        document.getElementById('filter_end_date').value = `${currentYear}-${currentMonth}-${lastDay}`;
        document.getElementById('filter_search').value = '';
        document.getElementById('filter_status').value = '';

        // Reload data awal
        loadJournalData();
    }

    // Tambahkan Debounce pas user mengetik di filter search biar ringan
    let journalSearchTimeout = null;
    document.getElementById('filter_search').addEventListener('input', function() {
        clearTimeout(journalSearchTimeout);
        journalSearchTimeout = setTimeout(() => {
            loadJournalData();
        }, 500);
    });
</script>

@endsection