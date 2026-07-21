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
            <form id="invoiceForm" method="POST">
                @csrf
                <div id="methodField"></div>
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
                            <input type="text" name="no_invoice" id="no_invoice" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Total Tagihan</label>
                            <input type="number" name="total_amount" id="total_amount" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Jatuh Tempo</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" required>
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

@endsection