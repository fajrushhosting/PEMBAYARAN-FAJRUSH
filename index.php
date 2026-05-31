<?php
// =========================================================================
// CONFIGURATION BLOCK
// =========================================================================
$api_key = "FR3_ziyqk6312052026pmccfzpuhbdnyw";

// PENTING: Jika endpoint di bawah ini tidak merespon, ganti dengan URL domain panel 
// tempat Anda mendaftar (Contoh: https://domain-panel-anda.com/api/qr)
$api_url = "https://fr3newera.com/api/qr"; 

$error_message = "";
$qr_string = "";
$total_bayar = 0;

if (isset($_POST['bayar'])) {
    $amount = intval($_POST['amount']);
    
    if ($amount < 1000) {
        $error_message = "Minimal pembayaran adalah Rp 1.000";
    } else {
        // Tambahkan kode unik 3 angka acak agar mutasi otomatis mudah terbaca oleh sistem
        $unique_code = rand(1, 499);
        $total_bayar = $amount + $unique_code;
        $invoice_id = "INV" . time();

        // Menyusun Payload sesuai standar API Gateway Merchant QRIS Lokal
        $payload = [
            'key'     => $api_key,
            'nominal' => $total_bayar,
            'isi'     => $invoice_id
        ];

        // Eksekusi CURL dengan Bypass SSL Guard (mencegah error SSL handshake di hosting)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_message = "Koneksi Gagal: " . curl_error($ch);
        } else {
            // Coba decode response (mengantisipasi jika response berupa JSON)
            $result = json_decode($response, true);
            
            if (isset($result['status']) && ($result['status'] == 'success' || $result['status'] == true)) {
                // Jika response JSON sukses dan memberikan string QRIS
                $qr_string = isset($result['qr_code']) ? $result['qr_code'] : $result['data'];
            } elseif (is_string($response) && strpos($response, '000201') !== false) {
                // Jika response dari FR3NEWERA berupa string teks QRIS mentah langsung (dimulai dari 000201...)
                $qr_string = trim($response);
            } else {
                // Jika response berupa error message dari server
                $error_message = !empty($response) ? "Respon Server: " . strip_tags($response) : "API Key salah atau server sedang maintenance.";
            }
        }
        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem QRIS Otomatis - FR3NEWERA</title>
    <!-- Tailwind CSS Terintegrasi -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-slate-800 p-6 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold tracking-tight text-white">Pembayaran QRIS</h2>
            <p class="text-xs text-slate-400 mt-1">Metode Instan & Otomatis</p>
        </div>

        <!-- NOTIFIKASI ERROR -->
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded-xl text-sm mb-4">
                <strong>Gagal:</strong> <?= $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- TAMPILAN JIKA QRIS BERHASIL DIBUAT -->
        <?php if (!empty($qr_string)): ?>
            <div class="bg-slate-700/50 p-4 rounded-xl border border-slate-600 text-center animate-fadeIn">
                <p class="text-xs text-slate-400 mb-3">Silahkan scan QRIS menggunakan E-Wallet atau Mobile Banking Anda:</p>
                
                <!-- Generator QR Code via API Terbuka Google/QRServer -->
                <div class="bg-white p-3 rounded-lg inline-block shadow-inner mb-4">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=<?= urlencode($qr_string); ?>" alt="QRIS" class="w-60 h-60">
                </div>
                
                <div class="text-slate-300 text-sm">TOTAL TAGIHAN</div>
                <div class="text-2xl font-extrabold text-emerald-400 mt-1">Rp <?= number_format($total_bayar, 0, ',', '.'); ?></div>
                <p class="text-[10px] text-amber-400 mt-1">*Transfer harus sama persis sampai 3 digit terakhir agar otomatis terbaca.</p>
                
                <div class="mt-4 pt-3 border-t border-slate-600/50 flex justify-center items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span class="text-xs text-amber-400 font-medium">Menunggu Pembayaran Masuk...</span>
                </div>
            </div>
            
            <a href="index.php" class="block text-center text-xs text-slate-400 hover:text-white mt-4 bg-slate-700 py-2 rounded-lg transition">
                ← Buka Tagihan Baru
            </a>

        <!-- FORM INPUT UTAMA -->
        <?php else: ?>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Masukkan Nominal Pembayaran</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">Rp</span>
                        <input type="number" name="amount" required min="1000" autofocus
                               class="w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white font-medium text-lg placeholder-slate-600" 
                               placeholder="Contoh: 10000">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5">Minimal transaksi Rp 1.000</p>
                </div>

                <button type="submit" name="bayar" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3 px-4 rounded-xl transition duration-200 shadow-lg transform active:scale-[0.98]">
                    Dapatkan QRIS Sekarang
                </button>
            </form>
        <?php endif; ?>

        <div class="mt-6 pt-4 border-t border-slate-700/50 flex justify-between items-center text-[10px] text-slate-500">
            <span>Secure Connection</span>
            <span>Gateway ID: FR3_NEWERA</span>
        </div>
    </div>

</body>
</html>
