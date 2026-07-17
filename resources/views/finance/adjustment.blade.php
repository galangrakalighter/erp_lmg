@extends('layout.app')
@section('content')

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Jurnal Penyesuaian</h6>
            
            {{-- Tombol Buat Baru: Muncul hanya jika bulan ini tidak terkunci --}}
            @php $currentPeriod = date('Y-m'); @endphp
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#tambahJournalModal" onclick="resetJournalForm()">
                <i class="fas fa-plus fa-sm mr-1"></i> Buat Jurnal Baru
            </button>
        </div>
        
        <div class="card-body">
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-bordered table-sm mb-0 align-middle" style="min-width: 1000px; background-color: #ffffff;">
                    <thead class="text-center text-white" style="background-color: #4e73df;">
                        <tr>
                            <th style="width: 12%; vertical-align: middle;">Tanggal</th>
                            <th style="width: 35%; vertical-align: middle;" class="text-left pl-3">Keterangan</th>
                            <th style="width: 12%; vertical-align: middle;">Kode Akun</th>
                            <th style="width: 14%; vertical-align: middle;">Debet</th>
                            <th style="width: 14%; vertical-align: middle;">Kredit</th>
                            <th style="width: 13%; vertical-align: middle;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $grandTotalDebet = 0;
                            $grandTotalKredit = 0;
                        @endphp

                        @forelse($journals as $journal)
                            @foreach($journal->details as $index => $detail)
                                @php
                                    $grandTotalDebet += (float) $detail->debit;
                                    $grandTotalKredit += (float) $detail->kredit;
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold text-dark border-bottom-0 align-top pt-2">
                                        @if($index === 0)
                                            <span class="d-block">{{ \Carbon\Carbon::parse($journal->tgl_transaksi)->format('Y') }}</span>
                                            <span class="text-primary">{{ \Carbon\Carbon::parse($journal->tgl_transaksi)->translatedFormat('d M') }}</span>
                                        @endif
                                    </td>

                                    <td class="pl-3 py-2 align-middle">
                                        @if((float) $detail->debit > 0)
                                            <div class="font-weight-600 text-dark">{{ $detail->account->nama_akun ?? 'Nama Akun N/A' }}</div>
                                        @else
                                            <div class="pl-5 text-muted font-italic">{{ $detail->account->nama_akun ?? 'Nama Akun N/A' }}</div>
                                        @endif

                                        @if($index === count($journal->details) - 1 && $journal->keterangan)
                                            <div class="small text-info font-italic mt-1 pl-3">
                                                <i class="fas fa-info-circle mr-1"></i> ({{ $journal->keterangan }})
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-center font-weight-bold text-secondary align-middle">
                                        {{ $detail->account->kode ?? '-' }}
                                    </td>

                                    <td class="text-right pr-3 align-middle font-weight-bold">
                                        @if((float) $detail->debit > 0)
                                            <span class="text-dark">{{ number_format($detail->debit, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-right pr-3 align-middle font-weight-bold">
                                        @if((float) $detail->kredit > 0)
                                            <span class="text-dark">{{ number_format($detail->kredit, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center align-top pt-2 border-bottom-0">
                                        @if($index === 0)
                                            {{-- Gunakan atribut 'is_locked' yang didefinisikan di Model --}}
                                            @if($journal->is_locked)
                                                <span class="text-secondary" title="Data terkunci karena periode sudah ditutup">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            @else
                                                <button type="button" class="btn btn-warning btn-xs px-2 shadow-sm" title="Edit Jurnal"
                                                        onclick="editJournal({{ json_encode($journal) }}, '{{ route('adjustment_journal.update', $journal->id) }}')">
                                                    <i class="fas fa-edit fa-xs"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-xs px-2 shadow-sm" title="Hapus Jurnal"
                                                        onclick="deleteJournal('{{ route('adjustment_journal.destroy', $journal->id) }}', '{{ $journal->journal_number ?? '' }}')">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            
                            <tr style="background-color: #f8f9fc;">
                                <td colspan="6" class="p-1 border-top-0 border-bottom-1" style="height: 8px;"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x d-block mb-2"></i>
                                    Belum ada data ayat jurnal penyesuaian untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(count($journals) > 0)
                        <tfoot class="font-weight-bold text-dark bg-light" style="border-top: 3px double #b7c1d1;">
                            <tr>
                                <td colspan="3" class="text-center py-2 text-uppercase letter-spacing-1">TOTAL</td>
                                <td class="text-right pr-3 text-success font-weight-extrabold">{{ number_format($grandTotalDebet, 0, ',', '.') }}</td>
                                <td class="text-right pr-3 text-success font-weight-extrabold">{{ number_format($grandTotalKredit, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

{{-- MODAL INPUT --}}
<div class="modal fade" id="tambahJournalModal" tabindex="-1" role="dialog" aria-labelledby="tambahJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="tambahJournalModalLabel"><i class="fas fa-sliders-h mr-2"></i> Input Jurnal Penyesuaian</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('adjustment_journal.store') }}" method="POST" id="formModalJournal">
                @csrf
                <div class="modal-body text-left bg-light">
                    
                    <div class="row bg-white p-3 rounded shadow-sm mb-3 mx-1">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-dark">No. Jurnal</label>
                            <input type="text" class="form-control form-control-sm" name="journal_number" value="AJ-{{ date('Ymd') }}-XXXX" readonly>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-dark">Tanggal Jurnal</label>
                            <input type="date" class="form-control form-control-sm border-primary" name="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold text-dark">Keterangan / Deskripsi</label>
                            <input type="text" class="form-control form-control-sm border-primary" name="description" placeholder="Contoh: Penyesuaian sewa dibayar dimuka..." required>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white py-2">
                            <span class="font-weight-bold text-primary small">Rincian Akun (Debit & Kredit)</span>
                            <button type="button" class="btn btn-success btn-sm px-2 py-1 shadow-sm" onclick="addModalRow('tambah')">
                                <i class="fas fa-plus fa-xs mr-1"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 text-center table-sm" id="modalJournalTable">
                                <thead class="thead-light small">
                                    <tr>
                                        <th style="width: 45%;" class="text-left">Akun (COA)</th>
                                        <th style="width: 22%;">Debit (Rp)</th>
                                        <th style="width: 22%;">Kredit (Rp)</th>
                                        <th style="width: 11%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="modalJournalRows">
                                    <tr class="modal-journal-row">
                                        <td class="text-left">
                                            <select name="items[0][chart_of_account_id]" class="form-control form-control-sm select-coa" required>
                                                <option value="">-- Pilih Akun COA --</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->kode }} - {{ $account->nama_akun }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][debit]" class="form-control form-control-sm text-right input-debit" value="0" oninput="calculateModalTotal('tambah')">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][kredit]" class="form-control form-control-sm text-right input-kredit" value="0" oninput="calculateModalTotal('tambah')">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-danger btn-circle" onclick="removeModalRow(this, 'tambah')">
                                                <i class="fas fa-trash fa-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light font-weight-bold text-dark small">
                                    <tr>
                                        <td class="text-right py-2">Total :</td>
                                        <td class="text-right py-2" id="modalTotalDebitDisplay">Rp 0,00</td>
                                        <td class="text-right py-2" id="modalTotalKreditDisplay">Rp 0,00</td>
                                        <td id="modalBalanceStatus" class="text-center text-danger font-weight-bold py-2">
                                            <i class="fas fa-times-circle mr-1"></i> Unbalanced
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnModalSubmit" class="btn btn-primary btn-sm px-4" disabled>
                        <i class="fas fa-save mr-1"></i> Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editJournalModal" tabindex="-1" role="dialog" aria-labelledby="editJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold" id="editJournalModalLabel"><i class="fas fa-edit mr-2"></i> Edit Jurnal Penyesuaian</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="formEditJournal">
                @csrf
                @method('PUT')
                <div class="modal-body text-left bg-light">
                    
                    <div class="row bg-white p-3 rounded shadow-sm mb-3 mx-1">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-dark">No. Jurnal</label>
                            <input type="text" class="form-control form-control-sm" id="edit_journal_number" name="journal_number" readonly>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-dark">Tanggal Jurnal</label>
                            <input type="date" class="form-control form-control-sm border-warning" id="edit_date" name="date" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold text-dark">Keterangan / Deskripsi</label>
                            <input type="text" class="form-control form-control-sm border-warning" id="edit_description" name="description" required>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white py-2">
                            <span class="font-weight-bold text-warning small">Rincian Akun (Debit & Kredit)</span>
                            <button type="button" class="btn btn-success btn-sm px-2 py-1 shadow-sm" onclick="addModalRow('edit')">
                                <i class="fas fa-plus fa-xs mr-1"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 text-center table-sm">
                                <thead class="thead-light small">
                                    <tr>
                                        <th style="width: 45%;" class="text-left">Akun (COA)</th>
                                        <th style="width: 22%;">Debit (Rp)</th>
                                        <th style="width: 22%;">Kredit (Rp)</th>
                                        <th style="width: 11%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="editModalJournalRows">
                                    {{-- Baris akan di-inject via JavaScript JavaScript --}}
                                </tbody>
                                <tfoot class="bg-light font-weight-bold text-dark small">
                                    <tr>
                                        <td class="text-right py-2">Total :</td>
                                        <td class="text-right py-2" id="editModalTotalDebitDisplay">Rp 0,00</td>
                                        <td class="text-right py-2" id="editModalTotalKreditDisplay">Rp 0,00</td>
                                        <td id="editModalBalanceStatus" class="text-center text-danger font-weight-bold py-2">
                                            <i class="fas fa-times-circle mr-1"></i> Unbalanced
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnEditModalSubmit" class="btn btn-warning btn-sm px-4 text-dark font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Perbarui Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL HAPUS (CONFIRMATION) --}}
<div class="modal fade" id="deleteJournalModal" tabindex="-1" role="dialog" aria-labelledby="deleteJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="deleteJournalModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="formDeleteJournal">
                @csrf
                @method('DELETE')
                <div class="modal-body text-left">
                    Apakah Anda yakin ingin menghapus Jurnal Penyesuaian <strong id="delete_journal_code" class="text-danger"></strong>? 
                    <p class="text-muted small mt-2">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua rincian pos debit/kredit yang terkait.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">Ya, Hapus Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    let modalRowIndex = 1;
    let editModalRowIndex = 0;

    // Kumpulan data COA untuk kebutuhan injeksi baris baru pada JS dinamis
    const coaOptions = `
        <option value="">-- Pilih Akun COA --</option>
        @foreach($accounts as $account)
            <option value="{{ $account->id }}">{{ $account->kode }} - {{ $account->nama_akun }}</option>
        @endforeach
    `;

    function addModalRow(type = 'tambah') {
        const tbodyId = type === 'tambah' ? 'modalJournalRows' : 'editModalJournalRows';
        const index = type === 'tambah' ? modalRowIndex : editModalRowIndex;
        const calcParam = type === 'tambah' ? "'tambah'" : "'edit'";
        
        const tbody = document.getElementById(tbodyId);
        const rowHTML = `
            <tr class="${type}-modal-journal-row">
                <td class="text-left">
                    <select name="items[${index}][chart_of_account_id]" class="form-control form-control-sm select-coa" required>
                        ${coaOptions}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${index}][debit]" class="form-control form-control-sm text-right input-debit" value="0" oninput="calculateModalTotal(${calcParam})">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${index}][kredit]" class="form-control form-control-sm text-right input-kredit" value="0" oninput="calculateModalTotal(${calcParam})">
                </td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger btn-circle" onclick="removeModalRow(this, ${calcParam})">
                        <i class="fas fa-trash fa-xs"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', rowHTML);
        
        if(type === 'tambah') modalRowIndex++;
        else editModalRowIndex++;
        
        calculateModalTotal(type);
    }

    function removeModalRow(button, type = 'tambah') {
        const rowClass = type === 'tambah' ? '.tambah-modal-journal-row' : '.edit-modal-journal-row';
        const rows = document.querySelectorAll(rowClass);
        if (rows.length > 1) {
            button.closest('tr').remove();
            calculateModalTotal(type);
        } else {
            alert('Minimal harus mengisi 1 baris transaksi!');
        }
    }

    function calculateModalTotal(type = 'tambah') {
        const prefix = type === 'tambah' ? 'modal' : 'editModal';
        const rowClass = type === 'tambah' ? '.tambah-modal-journal-row' : '.edit-modal-journal-row';
        
        let totalDebit = 0;
        let totalKredit = 0;

        document.querySelectorAll(`${rowClass} .input-debit`).forEach(input => {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll(`${rowClass} .input-kredit`).forEach(input => {
            totalKredit += parseFloat(input.value) || 0;
        });

        document.getElementById(`${prefix}TotalDebitDisplay`).textContent = 'Rp ' + totalDebit.toLocaleString('id-ID', { minimumFractionDigits: 2 });
        document.getElementById(`${prefix}TotalKreditDisplay`).textContent = 'Rp ' + totalKredit.toLocaleString('id-ID', { minimumFractionDigits: 2 });

        const balanceStatus = document.getElementById(`${prefix}BalanceStatus`);
        const btnSubmit = type === 'tambah' ? document.getElementById('btnModalSubmit') : document.getElementById('btnEditModalSubmit');

        if (totalDebit === totalKredit && totalDebit > 0) {
            balanceStatus.className = "text-center text-success font-weight-bold py-2";
            balanceStatus.innerHTML = `<i class="fas fa-check-circle mr-1"></i> Balanced`;
            btnSubmit.disabled = false;
        } else {
            balanceStatus.className = "text-center text-danger font-weight-bold py-2";
            balanceStatus.innerHTML = `<i class="fas fa-times-circle mr-1"></i> Unbalanced`;
            btnSubmit.disabled = true;
        }
    }

    function resetJournalForm() {
        document.getElementById('formModalJournal').reset();
        const tbody = document.getElementById('modalJournalRows');
        
        tbody.innerHTML = `
            <tr class="tambah-modal-journal-row">
                <td class="text-left">
                    <select name="items[0][chart_of_account_id]" class="form-control form-control-sm select-coa" required>
                        ${coaOptions}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[0][debit]" class="form-control form-control-sm text-right input-debit" value="0" oninput="calculateModalTotal('tambah')">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[0][kredit]" class="form-control form-control-sm text-right input-kredit" value="0" oninput="calculateModalTotal('tambah')">
                </td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger btn-circle" onclick="removeModalRow(this, 'tambah')">
                        <i class="fas fa-trash fa-xs"></i>
                    </button>
                </td>
            </tr>
        `;
        modalRowIndex = 1;
        calculateModalTotal('tambah');
    }

    // Fungsi Pemicu Modal Edit Jurnal (Mengisi Data Lama Secara Otomatis)
    function editJournal(journal, actionUrl) {
        document.getElementById('formEditJournal').action = actionUrl;
        document.getElementById('edit_journal_number').value = journal.journal_number || `AJ-${journal.id}`;
        document.getElementById('edit_date').value = journal.tgl_transaksi;
        document.getElementById('edit_description').value = journal.keterangan || '';
        
        const tbody = document.getElementById('editModalJournalRows');
        tbody.innerHTML = ''; 
        editModalRowIndex = 0;

        // Loop detail item dari database ke baris tabel modal edit
        journal.details.forEach((detail, index) => {
            const rowHTML = `
                <tr class="edit-modal-journal-row">
                    <td class="text-left">
                        <select name="items[${index}][chart_of_account_id]" class="form-control form-control-sm select-coa" required>
                            ${coaOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${index}][debit]" class="form-control form-control-sm text-right input-debit" value="${parseFloat(detail.debit) || 0}" oninput="calculateModalTotal('edit')">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${index}][kredit]" class="form-control form-control-sm text-right input-kredit" value="${parseFloat(detail.kredit) || 0}" oninput="calculateModalTotal('edit')">
                    </td>
                    <td>
                        <button type="button" class="btn btn-xs btn-danger btn-circle" onclick="removeModalRow(this, 'edit')">
                            <i class="fas fa-trash fa-xs"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', rowHTML);
            
            // Set agar nilai select-option COA sesuai database
            const selectElement = tbody.children[index].querySelector('.select-coa');
            selectElement.value = detail.chart_of_account_id;
            
            editModalRowIndex++;
        });

        calculateModalTotal('edit');
        $('#editJournalModal').modal('show');
    }

    // Fungsi Pemicu Modal Hapus
    function deleteJournal(actionUrl, journalCode) {
        document.getElementById('formDeleteJournal').action = actionUrl;
        document.getElementById('delete_journal_code').textContent = journalCode || 'Terpilih';
        $('#deleteJournalModal').modal('show');
    }
</script>

@endsection