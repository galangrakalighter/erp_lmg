@extends('layout.app')
@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Invoice & Piutang</h1>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#invoiceModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> Tambah Invoice
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Piutang</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="invoiceTable" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Nama Client</th>
                            <th>No Invoice</th>
                            <th>Jatuh Tempo</th>
                            <th>Total Tagihan</th>
                            <th>Sisa Piutang</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <!-- Gunakan method POST biasa tanpa AJAX -->
            <form id="invoiceForm" action="/finance/invoice/store" method="POST">
                @csrf
                <div id="methodField"></div>
                
                <!-- INPUT HIDDEN UNTUK MENAMPUNG JSON LAYANAN -->
                <input type="hidden" name="items_json" id="items_json">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Invoice</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control" required>
                                <option value="">Pilih Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Akun</label>
                            <select name="coa" id="coa_id" class="form-control" required>
                                <option value="">Pilih Akun</option>
                                @foreach($accountPiutang as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>No Invoice</label>
                            <input type="text" name="no_invoice" id="no_invoice" class="form-control" placeholder="Akan digenerate otomatis oleh sistem" readonly>
                        </div>

                        <!-- Bagian Layanan & Harga Dinamis -->
                        <div class="form-group">
                            <label class="d-flex justify-content-between align-items-center">
                                <span>Rincian Layanan & Harga</span>
                                <button type="button" class="btn btn-primary btn-sm" id="addRowBtn">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </label>
                            <div id="itemRowsContainer">
                                <div class="row item-row mb-2">
                                    <div class="col-md-7">
                                        <input type="text" class="form-control item-desc" placeholder="Nama Layanan" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control item-price" placeholder="Harga (Rp)" required>
                                        <input type="hidden" class="item-price-raw" value="0">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Total Tagihan</label>
                            <input type="text" id="total_amount_display" class="form-control" readonly placeholder="Rp 0">
                            <input type="hidden" name="total_amount" id="total_amount" required>
                        </div>

                        <div class="form-group">
                            <label>Jatuh Tempo</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea id="keterangan" name="keterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="paymentForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pembayaran: <span id="modalNoInvoice"></span></h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Akun</label>
                            <select name="coa" id="coa_id" class="form-control" required>
                                <option value="">Pilih Akun</option>
                                @foreach($accountPendapatan as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Bayar</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Bayar</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Metode Pembayaran</label>
                            <select name="method" class="form-control">
                                <option value="Transfer">Transfer</option>
                                <option value="Tunai">Tunai</option>
                            </select>
                        </div>
                         <div class="form-group">
                            <label>Keterangan</label>
                            <textarea id="keterangan" name="keterangan" class="form-control" rows="4" cols="50"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Bukti Pembayaran</label>
                            <input type="file" name="file_path" class="form-control-file" accept="image/*,application/pdf">
                            <small class="text-muted">Format: JPG, PNG, atau PDF (Max 2MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Konfirmasi Bayar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/demo/datatables-demo.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        function resetForm() {
            $('#invoiceForm').attr('action', '{{ route("invoice.store", $id) }}');
            $('#methodContainer').html('');
            $('#no_invoice').val('');
            $('#total_amount').val('');
        }
        $('#invoiceTable').DataTable({
            processing: true,
            serverSide: false, // Opsional: gunakan jika data ribuan
            ajax: '{{ route('api.invoices.data', $id) }}',
            columns: [
                { data: 'customer_name', name: 'customer_name' },
                { data: 'no_invoice', name: 'no_invoice' },
                { data: 'due_date', name: 'due_date' },
                { data: 'total_amount', name: 'total_amount', className: 'text-right' },
                { data: 'remaining', name: 'remaining', className: 'text-right font-weight-bold' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        let color = data === 'paid' ? 'success' : (data === 'partial' ? 'warning' : 'danger');
                        return `<span class="badge badge-${color}">${data.toUpperCase()}</span>`;
                    }
                },
                { 
                    data: 'id', 
                    name: 'action',
                    orderable: false, 
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let actions = '';

                        // 1. Tombol Bayar (Hanya muncul jika belum lunas)
                        if (row.status !== 'paid') {
                            actions += `<button type="button" class="btn btn-sm btn-primary mr-1" 
                                onclick="openPaymentModal(${row.id}, '${row.no_invoice}')">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>`;
                        }

                        // 2. Tombol PDF (Selalu muncul)
                        actions += `<a href="/invoice/pdf/${row.id}" target="_blank" class="btn btn-sm btn-warning mr-1">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>`;

                        // 3. Tombol Edit (Selalu muncul)
                        actions += `<button type="button" class="btn btn-sm btn-info mr-1" 
                            onclick="editInvoice(${JSON.stringify(row).replace(/"/g, '&quot;')})">
                            <i class="fas fa-edit"></i>
                        </button>`;

                        // 4. Tombol Hapus (Selalu muncul)
                        actions += `<form action="/invoice/${row.id}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>`;

                        return actions;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json'
            }
        });

        function editInvoice(data) {
            // 1. Tampilkan modal
            $('#invoiceModal').modal('show');
            
            // 2. Ubah URL form agar mengarah ke route UPDATE
            $('#invoiceForm').attr('action', '/invoice/' + data.id);
            
            // 3. Tambahkan hidden method PUT untuk spoofing
            $('#methodField').html('@method("PUT")');
            
            // 4. Isi field modal dengan data dari baris tabel
            $('#customer_id').val(data.customer_id); // Pastikan ada ID ini di modal
            $('#no_invoice').val(data.no_invoice);
            $('#total_amount').val(data.total_amount.replace(/\./g, '')); // Bersihkan format angka
            $('#due_date').val(data.due_date_raw); // Pastikan data JSON Anda mengirim tanggal format Y-m-d
        }

        function openPaymentModal(invoiceId, noInvoice) {
            // 1. Set title modal
            $('#modalNoInvoice').text(noInvoice);
            
            // 2. Set action form ke route pembayaran
            // Sesuaikan route dengan route yang ada di web.php Anda
            $('#paymentForm').attr('action', '/invoice/payment/' + invoiceId);
            
            // 3. Tampilkan modal
            $('#paymentModal').modal('show');
        }

        document.addEventListener('DOMContentLoaded', function () {
            let rowIndex = 1;
            const container = document.getElementById('itemRowsContainer');
            const totalAmountInput = document.getElementById('total_amount');
            const totalDisplay = document.getElementById('total_amount_display');
            const invoiceForm = document.getElementById('invoiceForm');

            const formatRupiah = (angka) => {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka || 0);
            };

            const parseRupiah = (string) => {
                let clean = String(string).replace(/[^0-9]/g, '');
                return parseFloat(clean) || 0;
            };

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.item-price-raw').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    total += val;
                });
                
                totalDisplay.value = formatRupiah(total); 
                totalAmountInput.value = total;           
            }

            document.getElementById('addRowBtn').addEventListener('click', function () {
                const newRow = document.createElement('div');
                newRow.classList.add('row', 'item-row', 'mb-2');
                newRow.innerHTML = `
                    <div class="col-md-7">
                        <input type="text" class="form-control item-desc" placeholder="Nama Layanan" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control item-price" placeholder="Harga (Rp)" required>
                        <input type="hidden" class="item-price-raw" value="0">
                    </div>
                    <div class="col-md-1 d-flex align-items-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                container.appendChild(newRow);
                rowIndex++;
                updateRemoveButtons();
            });

            container.addEventListener('click', function (e) {
                if (e.target.closest('.remove-row')) {
                    e.target.closest('.item-row').remove();
                    calculateTotal();
                    updateRemoveButtons();
                }
            });

            container.addEventListener('input', function (e) {
                if (e.target.classList.contains('item-price')) {
                    let row = e.target.closest('.item-row');
                    let rawInput = row.querySelector('.item-price-raw');
                    
                    let numericValue = parseRupiah(e.target.value);
                    rawInput.value = numericValue; 
                    
                    e.target.value = numericValue > 0 ? formatRupiah(numericValue).replace('Rp', '').trim() : '';
                    calculateTotal();
                }
            });

            function updateRemoveButtons() {
                const rows = container.querySelectorAll('.item-row');
                rows.forEach((row) => {
                    const btn = row.querySelector('.remove-row');
                    btn.disabled = rows.length === 1;
                });
            }

            // SAAT FORM DI-SUBMIT: Ubah rincian layanan menjadi string JSON di dalam input hidden
            invoiceForm.addEventListener('submit', function (e) {
                let itemsArray = [];

                document.querySelectorAll('.item-row').forEach(row => {
                    let desc = row.querySelector('.item-desc').value;
                    let price = row.querySelector('.item-price-raw').value;
                    
                    itemsArray.push({
                        description: desc,
                        price: parseFloat(price) || 0
                    });
                });

                // Masukkan hasil stringify JSON ke input hidden 'items_json'
                document.getElementById('items_json').value = JSON.stringify(itemsArray);
                
                // Form akan lanjut terkirim secara normal (POST) membawa inputan biasa + field 'items_json'
            });
        });
    
    </script>

@endsection