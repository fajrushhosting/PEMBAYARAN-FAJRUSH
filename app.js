document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const amount = document.getElementById('amount').value;
    const method = document.getElementById('paymentMethod').value;
    const btnSubmit = document.getElementById('btnSubmit');
    const resultArea = document.getElementById('resultArea');
    const instructions = document.getElementById('paymentInstructions');

    // Ubah text tombol saat loading
    btnSubmit.innerText = "Memproses...";
    btnSubmit.disabled = true;

    // Token/API Key Anda
    const apiKey = "FR3_ziyqk6312052026pmccfzpuhbdnyw";

    try {
        // PERHATIAN: Sesuaikan URL endpoint ini dengan dokumentasi API fr3newera Anda
        const response = await fetch('https://api.fr3newera.com/v1/transaction', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                amount: parseInt(amount),
                method: method,
                description: "Pembayaran Otomatis Website",
                callback_url: "https://websiteanda.com/callback" // Ubah sesuai domain Anda
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Tampilkan instruksi atau QR Code jika metodenya QRIS
            resultArea.classList.remove('hidden');
            instructions.innerHTML = `
                <p class="text-green-600 font-bold">Transaksi Berhasil Dibuat!</p>
                <p><strong>ID Transaksi:</strong> ${data.transaction_id}</p>
                <p><strong>Total Bayar:</strong> Rp ${data.total_amount}</p>
                <div class="my-4 flex justify-center">
                    ${data.qr_url ? `<img src="${data.qr_url}" alt="QRIS" class="w-48 h-48">` : '<p>Silahkan cek aplikasi e-wallet Anda atau ikuti link berikut.</p>'}
                </div>
                <a href="${data.checkout_url}" target="_blank" class="block text-center bg-green-500 text-white py-2 rounded-lg font-semibold mt-2">
                    Buka Halaman Pembayaran
                </a>
            `;
        } else {
            alert("Gagal membuat pembayaran: " + (data.message || "Terjadi kesalahan sistem."));
        }

    } catch (error) {
        console.error("Error:", error);
        alert("Gagal terhubung ke server pembayaran.");
    } finally {
        btnSubmit.innerText = "Bayar Sekarang";
        btnSubmit.disabled = false;
    }
});
