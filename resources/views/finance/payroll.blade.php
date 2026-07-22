@extends('layout.app')

@section('content')


    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addPayrollModal">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Buat Data Gaji Baru
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Tabel Daftar Keseluruhan Payroll -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Rekapitulasi Seluruh Gaji Karyawan</h6>
            
            <!-- Filter Tanggal / Periode -->
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small font-weight-bold text-dark">Periode Bulan:</label>
                <input type="month" id="filter_period" class="form-control form-control-sm border-primary" value="{{ date('Y-m') }}" onchange="loadPayrollData()">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Karyawan</th>
                            <th>Total Tagihan</th>
                            <th>Sudah Dibayar</th>
                            <th>Sisa Kurang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="payrollTableBody">
                        <!-- Data akan dimuat secara otomatis lewat API AJAX -->
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Memuat data dari server...</td>
                        </tr>
                    </tbody>
                </table>
                <nav aria-label="Page navigation">
                    <ul id="payrollPagination" class="pagination justify-content-end mt-3"></ul>
                </nav>
            </div>
        </div>
    </div>


    <!-- Modal Tambah Payroll -->
    <div class="modal fade" id="addPayrollModal" tabindex="-1" role="dialog" aria-labelledby="addPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="addPayrollForm" action="{{ route('payroll.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addPayrollModalLabel">Tambah Data Payroll</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Pilih Karyawan (user_id) -->
                        <div class="form-group">
                            <select name="coa" id="coa" class="form-control" required>
                                <option value="">-- Pilih Akun --</option>
                                <!-- Asumsi Anda mengirim variable $users dari controller -->
                                @foreach($account as $ap)
                                    <option value="{{ $ap->id }}">{{ $ap->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="user_id" class="small font-weight-bold">Nama Karyawan <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">-- Pilih Karyawan --</option>
                                <!-- Asumsi Anda mengirim variable $users dari controller -->
                                @foreach($user as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Gaji (payroll_date) -->
                        <div class="form-group">
                            <label for="payroll_date" class="small font-weight-bold">Tanggal Gaji <span class="text-danger">*</span></label>
                            <input type="date" name="payroll_date" id="payroll_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Total Tagihan / Gaji (total_amount) -->
                        <div class="form-group">
                            <label for="total_amount" class="small font-weight-bold">Total Tagihan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="total_amount" id="total_amount" class="form-control" placeholder="Contoh: 5000000" min="0" step="0.01" required>
                        </div>

                        <!-- Sudah Dibayar (paid_amount) -->
                        <div class="form-group">
                            <label for="paid_amount" class="small font-weight-bold">Sudah Dibayar (Rp)</label>
                            <input type="number" name="paid_amount" id="paid_amount" class="form-control" placeholder="Contoh: 0" min="0" step="0.01" value="0">
                        </div>

                        <!-- Status Pembayaran (status) -->
                        <div class="form-group">
                            <label for="status" class="small font-weight-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="unpaid">Belum Dibayar (Unpaid)</option>
                                <option value="partial">Sebagian (Partial)</option>
                                <option value="paid">Lunas (Paid)</option>
                            </select>
                        </div>

                        <!-- Keterangan (keterangan) -->
                        <div class="form-group">
                            <label for="keterangan" class="small font-weight-bold">Keterangan / Catatan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Opsional..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Payroll</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Payment / Bayar -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="paymentForm" action="{{ route('payroll.payment') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="paymentModalLabel">Form Pembayaran Gaji</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Hidden ID Payroll -->
                        <input type="hidden" name="payroll_id" id="payment_payroll_id">

                        <div class="form-group">
                            <select name="coa" id="coa" class="form-control" required>
                                <option value="">-- Pilih Akun --</option>
                                <!-- Asumsi Anda mengirim variable $users dari controller -->
                                @foreach($accountKewajiban as $ap)
                                    <option value="{{ $ap->id }}">{{ $ap->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilih Karyawan (Select Option) -->
                        <div class="form-group">
                            <label for="payment_user_id" class="small font-weight-bold">Nama Karyawan <span class="text-danger">*</span></label>
                            <select name="user_id" id="payment_user_id" class="form-control" required disabled>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($user as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                            <!-- Input hidden opsional jika ingin tetap mengirim nilai user_id saat disabled -->
                            <input type="hidden" name="user_id" id="payment_user_id_hidden">
                        </div>

                        <div class="form-group">
                            <label for="payment_date" class="small font-weight-bold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="payment_amount" class="small font-weight-bold">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="payment_amount" class="form-control" placeholder="Masukkan jumlah bayar" min="1" step="0.01" required>
                            <small class="text-muted" id="info_sisa_tagihan"></small>
                        </div>

                        <div class="form-group">
                            <label for="keterangan" class="small font-weight-bold">Keterangan / Catatan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Opsional..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">Simpan Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPayrollModal" tabindex="-1" role="dialog" aria-labelledby="editPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editPayrollForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="editPayrollModalLabel">Edit Data Payroll</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Field yang HANYA MUNCUL jika status UNPAID -->
                        <div class="unpaid-only">
                            <!-- Pilih Akun (COA) -->
                            <div class="form-group">
                                <label for="edit_coa" class="small font-weight-bold">Akun <span class="text-danger">*</span></label>
                                <select name="coa" id="edit_coa" class="form-control">
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach($account as $ap)
                                        <option value="{{ $ap->id }}">{{ $ap->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilih Karyawan (user_id) -->
                            <div class="form-group">
                                <label for="edit_user_id" class="small font-weight-bold">Nama Karyawan <span class="text-danger">*</span></label>
                                <select name="user_id" id="edit_user_id" class="form-control">
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($user as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tanggal Gaji (payroll_date) -->
                            <div class="form-group">
                                <label for="edit_payroll_date" class="small font-weight-bold">Tanggal Gaji <span class="text-danger">*</span></label>
                                <input type="date" name="payroll_date" id="edit_payroll_date" class="form-control">
                            </div>
                        </div>

                        <!-- Total Tagihan / Gaji (total_amount) -> SELALU MUNCUL -->
                        <div class="form-group">
                            <label for="edit_total_amount" class="small font-weight-bold">Total Tagihan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="total_amount" id="edit_total_amount" class="form-control" placeholder="Contoh: 5000000" min="0" step="0.01" required>
                        </div>

                        <!-- Field lain yang IKUT DISEMBUNYIKAN jika bukan unpaid (opsional, jika ingin disembunyikan juga) -->
                        <div class="unpaid-only">
                            <div class="form-group">
                                <label for="edit_paid_amount" class="small font-weight-bold">Sudah Dibayar (Rp)</label>
                                <input type="number" name="paid_amount" id="edit_paid_amount" class="form-control" placeholder="Contoh: 0" min="0" step="0.01">
                            </div>

                            <div class="form-group">
                                <label for="edit_status" class="small font-weight-bold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="unpaid">Belum Dibayar (Unpaid)</option>
                                    <option value="partial">Sebagian (Partial)</option>
                                    <option value="paid">Lunas (Paid)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="edit_keterangan" class="small font-weight-bold">Keterangan / Catatan</label>
                                <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="3" placeholder="Opsional..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm text-white">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function loadPayrollData(period = '', page = 1) {
        if (!period) {
            period = $('#filter_period').val();
        }

        let url = "{{ url('/api/get-data-payroll') }}";
        let params = [];

        if (period) {
            let parts = period.split('-');
            if (parts.length === 2) {
                params.push(`tahun=${parts[0]}`);
                params.push(`bulan=${parts[1]}`);
            }
        }

        // Tambahkan parameter page untuk pagination
        params.push(`page=${page}`);

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let tbody = $('#payrollTableBody');
                tbody.empty();

                // Asumsi response Laravel pagination: response.data berisi array item, 
                // atau jika response.data adalah objek paginator, sesuaikan (misal response.data.data)
                let items = response.data.data ? response.data.data : response.data;
                let pagination = response.data.current_page ? response.data : response;

                if (items.length === 0) {
                    tbody.append(`
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data payroll untuk periode ini.</td>
                        </tr>
                    `);
                    $('#payrollPagination').empty(); // Bersihkan pagination jika kosong
                    return;
                }

                // Hitung nomor urut berdasarkan halaman aktif (jika menggunakan pagination Laravel)
                let currentPage = pagination.current_page || 1;
                let perPage = pagination.per_page || items.length;

                items.forEach((p, index) => {
                    let rowNumber = (currentPage - 1) * perPage + (index + 1);
                    let sisa = parseFloat(p.total_amount) - parseFloat(p.paid_amount);
                    
                    let formatTotal = 'Rp ' + Number(p.total_amount).toLocaleString('id-ID');
                    let formatPaid = 'Rp ' + Number(p.paid_amount).toLocaleString('id-ID');
                    let formatSisa = 'Rp ' + Number(sisa).toLocaleString('id-ID');

                    let dateObj = new Date(p.payroll_date);
                    let formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

                    let badgeHtml = '';
                    if (p.status === 'paid') {
                        badgeHtml = '<span class="badge badge-success">Lunas</span>';
                    } else if (p.status === 'partial') {
                        badgeHtml = '<span class="badge badge-warning">Sebagian</span>';
                    } else {
                        badgeHtml = '<span class="badge badge-secondary">Belum Dibayar</span>';
                    }

                    let userName = p.user ? p.user.name : '-';
                    let userEmail = p.user ? p.user.email : '';
                    let userId = p.user_id;
                    let csrfToken = '{{ csrf_token() }}';

                    let deleteUrlTemplate = "{{ route('payroll.destroy', ':id') }}";

                    // Di dalam loop render baris tabel (template literal):
                    let destroyUrl = deleteUrlTemplate.replace(':id', p.id);

                    let row = `
                        <tr>
                            <td class="text-center">${rowNumber}</td>
                            <td>${formattedDate}</td>
                            <td>
                                <strong>${userName}</strong>
                                <br><small class="text-muted">${userEmail}</small>
                            </td>
                            <td>${formatTotal}</td>
                            <td class="text-success font-weight-bold">${formatPaid}</td>
                            <td class="${sisa > 0 ? 'text-danger font-weight-bold' : 'text-success'}">
                                ${formatSisa}
                            </td>
                            <td>${badgeHtml}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-success btn-circle btn-sm btn-pay" 
                                        data-toggle="modal" 
                                        data-target="#paymentModal" 
                                        data-id="${p.id}" 
                                        data-userid="${userId}"
                                        data-sisa="${sisa}"
                                        title="Tambah Pembayaran">
                                    <i class="fas fa-money-bill-wave"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-circle btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#editPayrollModal" 
                                        data-id="${p.id}" 
                                        data-userid="${p.user_id}" 
                                        data-coa="${p.coa ?? ''}" 
                                        data-date="${p.payroll_date}" 
                                        data-total="${p.total_amount}" 
                                        data-paid="${p.paid_amount}" 
                                        data-status="${p.status}" 
                                        data-keterangan="${p.keterangan ?? ''}"
                                        title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="${destroyUrl}".replace(':id', p.id) method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Render Navigasi Pagination
                renderPagination(pagination);
            },
            error: function(xhr) {
                console.error("Gagal mengambil data dari API:", xhr.responseText);
                $('#payrollTableBody').html(`
                    <tr>
                        <td colspan="8" class="text-center text-danger py-4">Gagal memuat data dari server.</td>
                    </tr>
                `);
            }
        });
    }

    // Panggil otomatis saat halaman pertama kali dibuka
    $(document).ready(function() {
        loadPayrollData();
        $('#paymentModal').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget); // Tombol yang diklik
            let payrollId = button.data('id');
            let userId = button.data('userid');
            let sisaTagihan = button.data('sisa');

            let modal = $(this);
            modal.find('#payment_payroll_id').val(payrollId);
            
            // Otomatis select karyawan berdasarkan data dari tabel
            modal.find('#payment_user_id').val(userId);
            modal.find('#payment_user_id_hidden').val(userId); // Jika select di-disable

            modal.find('#payment_amount').val(sisaTagihan);
            modal.find('#info_sisa_tagihan').text('Sisa tagihan saat ini: Rp ' + Number(sisaTagihan).toLocaleString('id-ID'));
            modal.find('#payment_amount').attr('max', sisaTagihan);
        });

        // Event handler ketika tombol pagination diklik
        $(document).on('click', '#payrollPagination .page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                loadPayrollData('', page);
            }
        });

        $('#editPayrollModal').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget);
            let id = button.data('id');
            let userId = button.data('userid');
            let coa = button.data('coa');
            let date = button.data('date');
            let total = button.data('total');
            let paid = button.data('paid');
            let status = button.data('status');
            let keterangan = button.data('keterangan');

            let modal = $(this);

            // Atur action form update
            modal.find('#editPayrollForm').attr('action', "{{ url('payroll') }}/" + id);

            // Masukkan data ke input
            modal.find('#edit_coa').val(coa);
            modal.find('#edit_user_id').val(userId);
            modal.find('#edit_payroll_date').val(date);
            modal.find('#edit_total_amount').val(total);
            modal.find('#edit_paid_amount').val(paid);
            modal.find('#edit_status').val(status);
            modal.find('#edit_keterangan').val(keterangan);

            // Kondisi Tampilan Berdasarkan Status
            if (status === 'unpaid') {
                // Jika unpaid, tampilkan semua field dan aktifkan kembali atribut required-nya
                modal.find('.unpaid-only').show();
                modal.find('#edit_coa, #edit_user_id, #edit_payroll_date, #edit_status').prop('required', true);
            } else {
                // Jika bukan unpaid (partial/paid), sembunyikan field lain dan matikan required agar tidak error
                modal.find('.unpaid-only').hide();
                modal.find('#edit_coa, #edit_user_id, #edit_payroll_date, #edit_status').prop('required', false);
            }
        });
    });

    function renderPagination(meta) {
        let paginationContainer = $('#payrollPagination'); // Pastikan Anda memiliki elemen ini di HTML (misal: <nav><ul id="payrollPagination" class="pagination justify-content-center"></ul></nav>)
        if (!paginationContainer.length) return;

        paginationContainer.empty();

        if (!meta.last_page || meta.last_page <= 1) return;

        let currentPage = meta.current_page;
        let lastPage = meta.last_page;

        // Tombol Previous
        let prevDisabled = currentPage === 1 ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo; Previous</a>
            </li>
        `);

        // Render nomor halaman (bisa disesuaikan tampilannya jika halaman terlalu banyak)
        for (let i = 1; i <= lastPage; i++) {
            let active = i === currentPage ? 'active' : '';
            // Batasi halaman jika terlalu banyak (opsional, misal tampilkan di sekitar current page)
            if (i === 1 || i === lastPage || (i >= currentPage - 2 && i <= currentPage + 2)) {
                paginationContainer.append(`
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                paginationContainer.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
        }

        // Tombol Next
        let nextDisabled = currentPage === lastPage ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next &raquo;</a>
            </li>
        `);
    }

    
</script>
@endsection