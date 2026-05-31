<?php
// Mengambil data JSON yang dikirim oleh server FR3NEWERA
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die("Koneksi Ilegal");
}

// Ambil parameter dari callback (sesuaikan dengan dokumentasi parameter FR3NEWERA)
$status   = $data['status']; // contoh: 'success' atau 'settlement'
$reff_id  = $data['reff_id']; // ID Invoice dari website Anda
$amount   = $data['amount'];
$signature= $data['signature']; // Opsional: Untuk keamanan validasi data

// Validasi status sukses dari server
if ($status == 'success' || $status == 'settlement') {
    
    /* ======================================================
    TEMPAT KODE LOGIKA BISNIS ANDA BERADA:
    1. Update status transaksi di database Anda ke "Lunas"
    2. Kirim produk digital, tambah saldo, atau kirim email otomatis
    ======================================================
    */
    
    // Berikan respon balik ke server FR3NEWERA agar mereka tahu callback sukses diterima
    echo json_encode(['status' => true, 'message' => 'Callback diproses successfully']);
} else {
    echo json_encode(['status' => false, 'message' => 'Status pembayaran belum sukses']);
}
?>
