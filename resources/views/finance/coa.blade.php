@include('partials.header')

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Akun (Chart of Accounts)</h6>
            
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm mr-1"></i> Tambah Akun Baru
            </button>
        </div>

        <div class="card-body border-bottom bg-light">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold text-dark">Cari Akun:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="filterSearch" class="form-control border-primary" placeholder="Ketik kode atau nama akun..." onkeyup="loadCoaData()">
                        <div class="input-group-append">
                            <span class="input-group-text bg-primary border-primary text-white">
                                <i class="fas fa-search fa-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold text-dark">Filter Tipe Akun:</label>
                    <select id="filterType" class="form-control form-control-sm border-primary" onchange="loadCoaData()">
                        <option value="">- Semua Tipe -</option>
                        <option value="K">Kredit</option>
                        <option value="D">Debit</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold text-dark">Filter Status:</label>
                    <select id="filterStatus" class="form-control form-control-sm border-primary" onchange="loadCoaData()">
                        <option value="">- Semua Status -</option>
                        <option value="aktif">Aktif</option>
                        <option value="non_aktif">Non-Aktif</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold text-dark">Filter Kategori:</label>
                    <select id="filterCategory" class="form-control form-control-sm border-primary" onchange="loadCoaData()">
                        <option value="">- Semua Kategori -</option>
                        <option value="aset">Aset</option>
                        <option value="kewajiban">Kewajiban</option>
                        <option value="ekuitas">Ekuitas</option>
                        <option value="pendapatan">Pendapatan</option>
                        <option value="beban">Beban</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="button" onclick="resetFilter()" class="btn btn-sm btn-secondary px-4 shadow-sm w-100">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center mb-0" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 12%;">Kode Akun</th>
                            <th class="text-left" style="width: 28%;">Nama Akun</th>
                            <th style="width: 12%;">Tipe Akun</th>
                            <th style="width: 15%;">Saldo Awal</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 13%;">Kategori</th>
                            <th style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="coaTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="tambahModalLabel">Tambah Akun Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('finance_coa.store') }}" method="POST">
                @csrf
                <div class="modal-body text-left">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Kode Akun</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: 11001" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Nama Akun</label>
                        <input type="text" name="nama_akun" class="form-control" placeholder="Contoh: Kas Utama" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Tipe Akun</label>
                        <select name="type" class="form-control" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="K">Kredit</option>
                            <option value="D">Debit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Kategori</label>
                        <select name="category" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="aset">Aset</option>
                            <option value="kewajiban">Kewajiban</option>
                            <option value="ekuitas">Ekuitas</option>
                            <option value="pendapatan">Pendapatan</option>
                            <option value="beban">Beban</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Status Akun</label>
                        <select name="is_active" class="form-control" required>
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>

                    <hr class="border-light">
                    <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-money-bill-wave mr-1"></i> Saldo Awal (Opsional)</h6>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Periode Saldo Pembuka</label>
                        <input type="month" name="opening_periode" class="form-control" value="{{ date('Y-m') }}">
                        <small class="form-text text-muted">Bulan pertama kali akun ini memiliki saldo murni.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Nominal Saldo Pembuka (Rp)</label>
                        <input type="number" step="0.01" name="opening_saldo" class="form-control text-right font-weight-bold text-dark" value="0" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold" id="editModalLabel">Ubah Data Akun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditCoa" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body text-left">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Kode Akun</label>
                        <input type="text" id="edit_kode" name="kode" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Nama Akun</label>
                        <input type="text" id="edit_nama_akun" name="nama_akun" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Tipe Akun</label>
                        <select id="edit_type" name="type" class="form-control" required>
                            <option value="K">Kredit</option>
                            <option value="D">Debit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Kategori</label>
                        <select name="category" id="edit_category" class="form-control" required>
                            <option value="aset">Aset</option>
                            <option value="kewajiban">Kewajiban</option>
                            <option value="ekuitas">Ekuitas</option>
                            <option value="pendapatan">Pendapatan</option>
                            <option value="beban">Beban</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Status Akun</label>
                        <select name="is_active" id="edit_is_active" class="form-control" required>
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>

                    <hr class="border-light">
                    <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-money-bill-wave mr-1"></i> Saldo Awal Pembuka</h6>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Periode Saldo Pembuka</label>
                        <input type="month" id="edit_opening_periode" name="opening_periode" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark small">Nominal Saldo Pembuka (Rp)</label>
                        <input type="number" step="0.01" id="edit_opening_saldo" name="opening_saldo" class="form-control text-right font-weight-bold text-dark" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning shadow-sm text-dark font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog text-dark" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="deleteModalLabel">Konfirmasi Hapus Akun</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeleteCoa" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body text-left py-4">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus akun <strong id="delete_nama_akun" class="text-danger"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger shadow-sm">Ya, Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        loadCoaData();
    });

    // Helper untuk format rupiah diselaraskan (Rp. tanpa desimal)
    function formatRupiah(val) {
        const num = parseFloat(val);
        if (isNaN(num)) return 'Rp. 0';
        return 'Rp. ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // 1. AJAX READ DATA
    function loadCoaData() {
        const typeFilter = document.getElementById('filterType').value;
        const statusFilter = document.getElementById('filterStatus').value;
        const categoryFilter = document.getElementById('filterCategory').value;
        const searchFilter = document.getElementById('filterSearch').value; 
        
        const tableBody = document.getElementById('coaTableBody');
        
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>`;

        let params = new URLSearchParams();
        if (typeFilter) params.append('type', typeFilter);
        if (statusFilter) params.append('status', statusFilter);
        if (categoryFilter) params.append('category', categoryFilter);
        if (searchFilter) params.append('search', searchFilter);

        let url = '{{ route("finance_coa.data") }}?' + params.toString();

        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(res => {
            if (res.status === 'error') {
                throw new Error(res.message);
            }

            let accounts = [];
            if (Array.isArray(res)) {
                accounts = res;
            } else if (res && Array.isArray(res.data)) {
                accounts = res.data;
            } else if (res && typeof res === 'object') {
                accounts = Object.values(res);
            }

            tableBody.innerHTML = '';

            if (accounts.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data Chart of Account.</td></tr>`;
                return;
            }

            const badges = {
                'D': { color: 'success', label: 'Debit' },
                'K': { color: 'danger', label: 'Kredit' }
            };

            accounts.forEach(account => {
                if (!account || !account.kode) return; 

                const badgeInfo = badges[account.type] || { color: 'secondary', label: account.type };
                const safeNamaAkun = account.nama_akun.replace(/'/g, "\\'");
                
                const statusBadge = account.is_active 
                    ? '<span class="badge badge-light-success border border-success text-success px-2 py-1">Aktif</span>'
                    : '<span class="badge badge-light-danger border border-danger text-danger px-2 py-1">Non-Aktif</span>';
                
                const rawOpeningSaldo = account.opening_saldo !== undefined ? account.opening_saldo : 0;

                const row = `
                    <tr>
                        <td class="font-weight-bold text-gray-900">${account.kode}</td>
                        <td class="text-left font-weight-bold text-gray-800">
                            <i class="fas fa-dot-circle text-muted fa-xs mr-2"></i>${account.nama_akun}
                        </td>
                        <td>
                            <span class="badge badge-${badgeInfo.color} shadow-sm px-2 py-1" style="min-width:60px;">
                                ${badgeInfo.label}
                            </span>
                        </td>
                        <td class="text-right font-weight-bold text-dark">${formatRupiah(rawOpeningSaldo)}</td>
                        <td>${statusBadge}</td>
                        <td class="text-capitalize text-muted small">${account.category || '-'}</td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-sm btn-circle btn-warning mr-1" onclick="openEditModal(${account.id}, '${account.kode}', '${safeNamaAkun}', '${account.type}', ${account.is_active}, '${account.category || ''}', '${account.opening_periode || ''}', '${rawOpeningSaldo}')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-circle btn-danger" onclick="openDeleteModal(${account.id}, '${safeNamaAkun}')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data: ${error.message}</td></tr>`;
        });
    }

    function resetFilter() {
        document.getElementById('filterType').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterSearch').value = ''; 
        loadCoaData();
    }

    function openEditModal(id, kode, nama_akun, type, isActive, category, openingPeriode, openingSaldo) {
        const formEdit = document.getElementById('formEditCoa');
        if(formEdit) {
            formEdit.action = `/finance_coa/${id}`;
            
            document.getElementById('edit_kode').value = kode;
            document.getElementById('edit_nama_akun').value = nama_akun;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_is_active').value = isActive ? "1" : "0";
            document.getElementById('edit_opening_periode').value = openingPeriode;
            document.getElementById('edit_opening_saldo').value = openingSaldo;
            
            $('#editModal').modal('show'); 
        }
    }

    function openDeleteModal(id, nama_akun) {
        const formDelete = document.getElementById('formDeleteCoa');
        if(formDelete) {
            formDelete.action = `/finance_coa/${id}`;
            document.getElementById('delete_nama_akun').textContent = nama_akun;
            $('#deleteModal').modal('show');
        }
    }
</script>

@include('partials.footer')