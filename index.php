<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS Otomatis</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Invoice Pembayaran</h2>
        <p class="text-sm text-gray-500 mb-6">Metode Pembayaran: <span class="font-semibold text-blue-600">QRIS (Automated)</span></p>

        <?php
        // Logika sederhana untuk simulasi generate Invoice
        if (isset($_POST['bayar'])) {
            $amount = $_POST['amount'];
            $api_key = "FR3_ziyqk6312052026pmccfzpuhbdnyw";
            $merchant_id = "M23456"; // Ganti dengan Merchant ID FR3NEWERA Anda jika ada
            $unique_code = rand(100, 999); // Kode unik jika diperlukan oleh gateway
            $total_bayar = $amount + $unique_code;
            
            // Endpoint API FR3NEWERA (Sesuaikan dengan dokumentasi resmi URL API mereka)
            $url = "https://api.fr3newera.com/v1/generate-qris"; 

            $payload = [
                'api_key' => $api_key,
                'amount'  => $total_bayar,
                'method'  => 'QRIS',
                'reff_id' => 'INV' . time()
            ];

            // Proses Request ke Server FR3NEWERA
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            // Jika sukses mendapatkan data gambar QRIS atau string QRIS
            if (isset($result['status']) && $result['status'] == 'success') {
                $qr_data = $result['qr_code']; // Bisa berupa link gambar atau string QR Code
                ?>
                
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 my-4 animate-fade-in">
                    <p class="text-sm text-gray-600">Silahkan Scan QRIS di bawah ini:</p>
                    <div class="flex justify-center my-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode($qr_data); ?>" alt="QRIS Code" class="shadow-md rounded-lg">
                    </div>
                    <p class="text-lg font-bold text-gray-700">Total: Rp <?= number_format($total_bayar, 0, ',', '.'); ?></p>
                    <span class="inline-block mt-2 px-3 py-1 bg-yellow-400 text-xs font-bold text-yellow-900 rounded-full animate-pulse">Menunggu Pembayaran...</span>
                </div>
                <a href="index.php" class="block text-sm text-blue-500 hover:underline mt-4">← Kembali / Batalkan</a>

            <?php } else { ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm mb-4">
                    Gagal membuat pembayaran. Cek konfigurasi API atau endpoint Server.
                </div>
                <a href="index.php" class="block text-sm text-blue-500 hover:underline mt-4">Coba Lagi</a>
            <?php }
        } else { ?>

            <form action="" method="POST" class="space-y-4 text-left">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih / Masukkan Nominal</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number" name="amount" required min="1000" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900" placeholder="0">
                    </div>
                </div>

                <button type="submit" name="bayar" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                    Bayar Sekarang via QRIS
                </button>
            </form>
        <?php } ?>

        <div class="mt-8 pt-4 border-t border-gray-100 flex justify-center items-center gap-2">
            <span class="text-xs text-gray-400">Powered by FR3NEWERA Gateway</span>
        </div>
    </div>

</body>
</html>
