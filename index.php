<?php
// ==========================================
// KONFIGURASI API FR3 NEW ERA
// ==========================================
$apiKey = "FR3_ziyqk6312052026pmccfzpuhbdnyw";

// CATATAN: Ganti URL ini dengan endpoint API pembuatan QRIS yang sebenarnya dari dokumentasi FR3
$apiUrl = "https://fr3newera.com/api/v1/create-qris"; 

$qrisImageUrl = "";
$errorMessage = "";
$orderId = "";
$amount = 0;

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int) $_POST['amount'];
    $orderId = "INV-" . time(); // Membuat Order ID unik otomatis

    // 1. Siapkan Data (Payload)
    // Format ini biasanya berbeda tiap penyedia. Sesuaikan dengan dokumentasi FR3!
    $payloadData = [
        "order_id" => $orderId,
        "amount" => $amount,
        "payment_method" => "QRIS",
        "customer_name" => "Pelanggan Website"
    ];
    $payloadJson = json_encode($payloadData);

    // 2. Lakukan Request ke Server FR3 menggunakan cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
    
    // Set Header API Key (Bisa berupa Bearer, x-api-key, dll. Cek dokumentasi FR3)
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey 
        // "x-api-key: " . $apiKey // Gunakan ini jika FR3 memakai format header x-api-key
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 3. Proses Balasan (Response) dari FR3
    if ($response) {
        $responseData = json_decode($response, true);
        
        // Asumsi API mengembalikan URL gambar QRIS di variabel 'qris_url'
        if ($httpCode == 200 && isset($responseData['qris_url'])) {
            $qrisImageUrl = $responseData['qris_url'];
        } else {
            $errorMessage = "Gagal membuat QRIS. Respons Server: " . $response;
        }
    } else {
        $errorMessage = "Tidak ada koneksi ke server FR3 New Era.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Otomatis QRIS</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; }
        h2 { color: #333; margin-bottom: 20px; }
        input[type="number"] { width: 90%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        button { background-color: #28a745; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; width: 100%; }
        button:hover { background-color: #218838; }
        .qris-box { margin-top: 20px; padding: 15px; border: 2px dashed #007bff; border-radius: 10px; }
        .qris-box img { max-width: 100%; height: auto; }
        .error { color: red; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Checkout Pembayaran</h2>
    
    <?php if (!$qrisImageUrl): ?>
        <form method="POST" action="">
            <label for="amount" style="display:block; text-align:left; margin-bottom:8px;">Masukkan Nominal (Rp):</label>
            <input type="number" id="amount" name="amount" required min="1000" placeholder="Contoh: 10000">
            <button type="submit">Bayar Pakai QRIS</button>
        </form>
    <?php else: ?>
        <div class="qris-box">
            <h3>Scan QRIS Berikut</h3>
            <p>Order ID: <strong><?php echo $orderId; ?></strong></p>
            <p>Total: <strong>Rp <?php echo number_format($amount, 0, ',', '.'); ?></strong></p>
            
            <img src="<?php echo $qrisImageUrl; ?>" alt="QRIS Code">
            
            <p style="font-size: 12px; color: #666; mt-3;">Buka aplikasi M-Banking atau E-Wallet Anda, lalu scan kode QR di atas.</p>
        </div>
        <br>
        <a href="index.php" style="text-decoration: none; color: #007bff;">Kembali</a>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
</div>

</body>
</html>
