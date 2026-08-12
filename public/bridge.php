<?php
// Tampilkan error untuk debugging jika terjadi kendala
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi Keamanan Sederhana
$secret_token = "Lmg160925!";

// Validasi API Key dari URL parameter (?key=...)
$api_key = isset($_GET['key']) ? $_GET['key'] : '';

if ($api_key !== $secret_token) {
    http_response_code(403);
    echo json_encode([
        "status" => "error", 
        "message" => "Unauthorized", 
        "debug_received" => $api_key, 
        "expected" => $secret_token
    ]);
    exit;
}

// Koneksi Database PostgreSQL
$host = "laravel_pgsql";
$port = "5432";
$user = "postgres";
$pass = "Lmg140818!";
$db   = "erp_baru_lagi";

$conn_string = "host=$host port=$port dbname=$db user=$user password=$pass";
$conn = pg_connect($conn_string);

if (!$conn) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Connection failed"]);
    exit;
}

// Set client encoding ke UTF-8
pg_set_client_encoding($conn, "UTF8");

// Ambil data JSON yang dikirimkan oleh n8n
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (!isset($input['query'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Query is missing"]);
    exit;
}

$sql = $input['query'];

// Eksekusi Query
$result = pg_query($conn, $sql);

if (!$result) {
    // Kirim pesan error asli dari PostgreSQL agar terlihat di n8n
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "pgsql_error" => pg_last_error($conn),
        "failed_query" => $sql
    ]);
} else {
    // Cek apakah query menghasilkan kolom (misal: SELECT atau RETURNING)
    if (pg_num_fields($result) > 0) {
        $rows = pg_fetch_all($result);
        if ($rows === false) {
            $rows = []; // Jika query SELECT tapi hasilnya kosong
        }
        echo json_encode(["status" => "success", "data" => $rows]);
    } else {
        // Untuk query non-SELECT (INSERT, UPDATE, DELETE biasa)
        echo json_encode([
            "status" => "success", 
            "message" => "Query executed successfully",
            "affected_rows" => pg_affected_rows($result)
        ]);
    }
}

pg_close($conn);
?>