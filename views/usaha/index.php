<?php
$usaha = new Usaha();
$hasData = $usaha->hasData();
$dataUsaha = $hasData ? $usaha->getData() : null;

// Proses simpan (insert atau update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($hasData) {
        // Update data yang sudah ada
        $usaha->update($_POST);
        $msg = 'Data usaha berhasil diupdate!';
        $msgType = 'success';
    } else {
        // Insert data baru
        $usaha->insert($_POST);
        $msg = 'Data usaha berhasil disimpan!';
        $msgType = 'success';
    }
    // Redirect untuk menghindari form resubmit
    header('Location: ?page=usaha&msg=' . urlencode($msg) . '&type=' . $msgType);
    exit;
}

// Ambil pesan dari session/GET
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$msgType = isset($_GET['type']) ? $_GET['type'] : '';

include __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="card">
    <h2><i class="fas fa-building"></i> Profil Perusahaan</h2>
    
    <?php if ($msg): ?>
    <div class="alert <?= $msgType ?>">
        <span><?= htmlspecialchars($msg) ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    <?php endif; ?>

    <div class="info-text">
        <p><i class="fas fa-info-circle"></i> Halaman ini digunakan untuk mengelola data identitas perusahaan. Data ini akan digunakan sebagai kop surat pada laporan PDF.</p>
    </div>

    <form method="post" class="usaha-form">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label><i class="fas fa-building"></i> Nama Perusahaan</label>
                <input type="text" name="nama" value="<?= $hasData ? htmlspecialchars($dataUsaha['nama']) : '' ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                <textarea name="alamat" rows="3"><?= $hasData ? htmlspecialchars($dataUsaha['alamat']) : '' ?></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Nomor Telepon</label>
                <input type="text" name="nomor_telepon" value="<?= $hasData ? htmlspecialchars($dataUsaha['nomor_telepon']) : '' ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-fax"></i> Fax</label>
                <input type="text" name="fax" value="<?= $hasData ? htmlspecialchars($dataUsaha['fax']) : '' ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" value="<?= $hasData ? htmlspecialchars($dataUsaha['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-id-card"></i> NPWP</label>
                <input type="text" name="npwp" value="<?= $hasData ? htmlspecialchars($dataUsaha['npwp']) : '' ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-university"></i> Bank</label>
                <input type="text" name="bank" value="<?= $hasData ? htmlspecialchars($dataUsaha['bank']) : '' ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-credit-card"></i> Nomor Rekening</label>
                <input type="text" name="noaccount" value="<?= $hasData ? htmlspecialchars($dataUsaha['noaccount']) : '' ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-user-tie"></i> Atas Nama Rekening</label>
                <input type="text" name="atasnama" value="<?= $hasData ? htmlspecialchars($dataUsaha['atasnama']) : '' ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-user"></i> Pimpinan Perusahaan</label>
                <input type="text" name="pimpinan" value="<?= $hasData ? htmlspecialchars($dataUsaha['pimpinan']) : '' ?>">
            </div>
        </div>
        <?php if ($hasData): ?>
            <input type="hidden" name="id_usaha" value="<?= $dataUsaha['id_usaha'] ?>">
        <?php endif; ?>
        <div class="form-buttons">
            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
            <button type="reset" class="btn-cancel"><i class="fas fa-undo"></i> Reset</button>
        </div>
    </form>
</div>

<style>
.card {
    background: var(--bg-card); 
    color: var(--text-body);
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    max-width: 1000px;
    margin: 0 auto;
    transition: background 0.3s, color 0.3s;
}
.card h2 {
    color: var(--text-main);
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 24px;
}
.card h2 i {
    color: #3498db;
    margin-right: 10px;
}
.info-text {
    background: rgba(23, 162, 184, 0.1);
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: var(--text-main);
    border-left: 4px solid #17a2b8;
}
.info-text i {
    margin-right: 8px;
}
.usaha-form {
    margin-top: 10px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: var(--text-main); 
    font-size: 14px;
}
.form-group input, .form-group textarea {
    width: 100%;
    padding: 10px;
    background: var(--input-bg); 
    color: var(--text-body); 
    border: 1px solid var(--input-border); 
    border-radius: 8px;
    font-size: 14px;
    transition: background 0.3s, border 0.3s, color 0.3s;
}
.form-group textarea {
    resize: vertical;
}
.form-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}
.btn-save {
    background: #2ecc71;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 30px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
}
.btn-cancel {
    background: var(--border-color); 
    color: var(--text-main); 
    border: none;
    padding: 10px 24px;
    border-radius: 30px;
    cursor: pointer;
    font-size: 14px;
}
.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    position: relative;
}
.alert.success {
    background: rgba(16, 185, 129, 0.15); 
    color: #10b981;
    border-left: 4px solid #10b981;
}
.close-alert {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: inherit;
}
@media (max-width: 768px) {
    .usaha-form > div {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
