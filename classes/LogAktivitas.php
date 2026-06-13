<?php
class LogAktivitas {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function catatLog($id_user, $aktivitas) {
        $stmt = $this->db->prepare("INSERT INTO tbl_log_aktivitas (id_user, aktivitas) VALUES (:id_user, :aktivitas)");
        return $stmt->execute([
            'id_user' => $id_user,
            'aktivitas' => $aktivitas
        ]);
    }

    public function getLatestLogs($limit = 5) {
        $sql = "SELECT l.*, u.nama FROM tbl_log_aktivitas l 
                LEFT JOIN tbl_user u ON l.id_user = u.id_user 
                ORDER BY l.waktu DESC LIMIT :limit";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>