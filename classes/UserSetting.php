<?php
class UserSetting {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function getUserById($id_user) {
        $stmt = $this->db->prepare("SELECT * FROM tbl_user WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id_user, $nama, $password_baru, $file_foto) {
        try {
            $user_lama = $this->getUserById($id_user);
            $foto_nama = $user_lama['foto']; 

            if (isset($file_foto) && $file_foto['error'] == 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                $file_ext = strtolower(pathinfo($file_foto['name'], PATHINFO_EXTENSION));
                $file_size = $file_foto['size'];

                if (!in_array($file_ext, $allowed_extensions)) {
                    return ['status' => 'error', 'message' => 'Format file harus JPG, JPEG, atau PNG.'];
                }

                if ($file_size > 2097152) {
                    return ['status' => 'error', 'message' => 'Ukuran file maksimal adalah 2MB.'];
                }

                $foto_nama = time() . '_' . uniqid() . '.' . $file_ext;
                
                $upload_dir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                if (move_uploaded_file($file_foto['tmp_name'], $upload_dir . $foto_nama)) {
                    if (!empty($user_lama['foto']) && file_exists($upload_dir . $user_lama['foto'])) {
                        unlink($upload_dir . $user_lama['foto']);
                    }
                } else {
                    return ['status' => 'error', 'message' => 'Gagal mengunggah foto ke server.'];
                }
            }

            if (!empty($password_baru)) {
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql = "UPDATE tbl_user SET nama = :nama, password = :password, foto = :foto WHERE id_user = :id_user";
                $params = [
                    'nama' => $nama,
                    'password' => $hashed_password,
                    'foto' => $foto_nama,
                    'id_user' => $id_user
                ];
            } else {
                $sql = "UPDATE tbl_user SET nama = :nama, foto = :foto WHERE id_user = :id_user";
                $params = [
                    'nama' => $nama,
                    'foto' => $foto_nama,
                    'id_user' => $id_user
                ];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return ['status' => 'success', 'message' => 'Profil berhasil diperbarui!'];

        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Gagal memperbarui database: ' . $e->getMessage()];
        }
    }
}
?>