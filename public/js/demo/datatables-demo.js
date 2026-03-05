// Call the dataTables jQuery plugin
$(document).ready(function() {
  $('#dataTable').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 15, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#dataTable_1').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#swot_analize').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#segmentasi').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });
  
  $('#positioning').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#target_audience').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#brand_image').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#tagline').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#komunikasi').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#komposisi').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#audio').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });

  $('#alatBranding').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });
  $('#myTaskTable').DataTable({
    responsive: true,
    autoWidth: false,
    "pageLength": 10, // Menampilkan 10 data per halaman
        "columnDefs": [
            { "orderable": false, "targets": [1] } // Nonaktifkan sorting untuk kolom URL
        ],
        "order": [[0, "asc"]], // Urutkan berdasarkan tanggal terbaru secara default
        "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
  });
});
