<?php
class Pengumuman {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function getLatest() {
    $stmt = $this->db->prepare("SELECT * FROM tbl_pengumuman ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>