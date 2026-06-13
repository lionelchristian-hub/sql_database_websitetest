<?php
class Catatan {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function getByUserId($id_user) {
        $stmt = $this->db->prepare("SELECT * FROM tbl_catatan WHERE id_user = ? ORDER BY waktu DESC");
        $stmt->execute([$id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function simpan($id_user, $isi) {
        $stmt = $this->db->prepare("INSERT INTO tbl_catatan (id_user, isi_catatan) VALUES (?, ?)");
        return $stmt->execute([$id_user, $isi]);
    }

    public function hapus($id_catatan, $id_user) {
        $stmt = $this->db->prepare("DELETE FROM tbl_catatan WHERE id_catatan = ? AND id_user = ?");
        return $stmt->execute([$id_catatan, $id_user]);
    }
}
?>