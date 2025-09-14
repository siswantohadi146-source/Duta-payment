<?php
// === CONFIG ===
$SECRET = "8bec9c30be83d77b51bfa81aed42fc20"; // ambil dari menu API & Callback bukaOlshop

// koneksi database
$DB_HOST = "localhost";
$DB_USER = "db_user";     // ganti
$DB_PASS = "db_pass";     // ganti
$DB_NAME = "db_name";     // ganti

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die("Koneksi DB gagal: " . $conn->connect_error);
}

// data dari bukaOlshop
$status       = $_POST['status'] ?? '';
$id_user      = $_POST['id_user'] ?? '';
$token_topup  = $_POST['token_topup'] ?? '';
$total_topup  = $_POST['total_topup'] ?? 0;
$secret       = $_POST['secret_callback'] ?? '';

// validasi secret
if ($secret !== $SECRET) {
    http_response_code(403);
    echo json_encode(["status" => "error", "msg" => "Secret salah"]);
    exit;
}

// proses
if ($status == "ok") {
    // tambah saldo user
    $stmt = $conn->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
    $stmt->bind_param("di", $total_topup, $id_user);
    $stmt->execute();

    // simpan log
    $stmt = $conn->prepare("INSERT INTO topup_log (id_user, token_topup, jumlah, status) VALUES (?, ?, ?, 'berhasil')");
    $stmt->bind_param("isd", $id_user, $token_topup, $total_topup);
    $stmt->execute();

    echo json_encode(["status" => "ok", "msg" => "Saldo ditambahkan"]);
} elseif ($status == "topup_dibatalkan") {
    // log batal
    $stmt = $conn->prepare("INSERT INTO topup_log (id_user, token_topup, jumlah, status) VALUES (?, ?, ?, 'dibatalkan')");
    $stmt->bind_param("isd", $id_user, $token_topup, $total_topup);
    $stmt->execute();

    echo json_encode(["status" => "ok", "msg" => "Topup dibatalkan"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Status tidak dikenali"]);
}
