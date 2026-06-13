<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = Database::getInstance()->getConnection();
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO tbl_user (id_user, nama_user, password, role, nama) VALUES (?,?,?,?,?)");
    if ($stmt->execute([$_POST['id_user'], $_POST['nama_user'], $hashed, $_POST['role'], $_POST['nama']])) {
        echo "<script>alert('Registrasi berhasil! Silakan login.');
              window.location='index.php?page=login';</script>";
    } else $error = "Registrasi gagal!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="register-form">
    <h2>Daftar Akun Baru</h2>
    <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="id_user" placeholder="ID User (contoh: ADM001)" required>
        <input type="text" name="nama_user" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="admin">Admin</option>
            <option value="hrd">HRD</option>
            <option value="manager">Manager</option>
            <option value="staff">Staff</option>
        </select>
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
        <button type="submit" class="btn btn-success">Daftar</button>
        <p>Sudah punya akun? <a href="index.php?page=login">Login</a></p>
    </form>
</div>
</body>
</html>