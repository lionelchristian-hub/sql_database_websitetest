<?php
// 1. Perbaikan Autentikasi: Gunakan class User ($auth sudah diinstansiasi di root index.php)
if (!$auth->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$userSetting = new UserSetting();

// 2. Perbaikan Session ID: Ambil dari helper Session bawaan proyek Anda
$id_user_aktif = Session::get('user_id'); 

$message = '';
$message_type = '';

// Eksekusi ketika tombol Simpan Perubahan ditekan
if (isset($_POST['btn_simpan'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $password_baru = $_POST['password_baru'];
    $file_foto = $_FILES['foto_profil'];

    $proses = $userSetting->updateProfile($id_user_aktif, $nama, $password_baru, $file_foto);
    
    $message = $proses['message'];
    $message_type = $proses['status'];

    // Sinkronisasi: Jika update nama sukses, perbarui session nama yang tampil di header
    if ($message_type == 'success') {
        Session::set('nama', $nama);
    }
}

// Ambil data user ter-update dari database
$data_user = $userSetting->getUserById($id_user_aktif);

// 3. Perbaikan Path Foto: Menggunakan validasi bertingkat yang aman
if (!empty($data_user['foto'])) {
    $target_file_fisik = __DIR__ . '/../../assets/uploads/' . $data_user['foto'];
    if (file_exists($target_file_fisik)) {
        $foto_path = BASE_URL . 'assets/uploads/' . $data_user['foto'];
    } else {
        $foto_path = 'https://via.placeholder.com/150'; 
    }
} else {
    $foto_path = 'https://via.placeholder.com/150'; 
}
?>

<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid" style="padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    
    <?php if (!empty($message)): ?>
        <div style="padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; 
                    color: <?= $message_type == 'success' ? '#155724' : '#721c24'; ?>; 
                    background-color: <?= $message_type == 'success' ? '#d4edda' : '#f8d7da'; ?>; 
                    border-color: <?= $message_type == 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?= $message; ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border: 1px solid #e3e6f0; border-radius: 8px; padding: 20px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);">
        <div class="card-body">
            <h3 style="margin-top: 0; margin-bottom: 25px; color: #333; font-weight: 600;">
                ⚙️ Pengaturan Profil
            </h3>
            
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div style="display: flex; align-items: center; mb-4; padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid #e3e6f0;">
                    <div style="margin-right: 25px;">
                        <img src="<?= $foto_path; ?>" alt="Foto Profil" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid #4e73df; padding: 3px;">
                    </div>
                    <div>
                        <table style="font-size: 14px; color: #333; line-height: 1.8;">
                            <tr>
                                <td style="font-weight: bold; width: 100px;">ID User</td>
                                <td>: <?= htmlspecialchars($data_user['id_user'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Username</td>
                                <td>: <?= htmlspecialchars($data_user['nama_user'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Role</td>
                                <td>: <span style="background: #858796; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; text-transform: capitalize;"><?= htmlspecialchars($data_user['role'] ?? ''); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; color: #4e73df;">Nama Lengkap</label>
                    <input type="text" name="nama" style="width: 100%; padding: 8px; border: 1px solid #d1d3e2; border-radius: 4px; box-sizing: border-box;" value="<?= htmlspecialchars($data_user['nama'] ?? ''); ?>" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; color: #4e73df;">Password Baru (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password_baru" style="width: 100%; padding: 8px; border: 1px solid #d1d3e2; border-radius: 4px; box-sizing: border-box;" placeholder="Masukkan password baru jika ingin mengganti">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; color: #4e73df;">Foto Profil</label>
                    <input type="file" name="foto_profil" style="display: block; margin-bottom: 5px;">
                    <small style="color: #858796; font-size: 11px; display: block;">Format: JPG, JPEG, PNG. Maksimal ukuran file 2MB.</small>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="btn_simpan" style="background: #2ecc71; color: #fff; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: 500; font-size: 14px;">
                        💾 Simpan Perubahan
                    </button>
                    <button type="reset" style="background: #f8f9fc; color: #4e73df; border: 1px solid #d1d3e2; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-size: 14px;">
                        🔄 Reset
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>