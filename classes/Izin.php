<?php
class Izin {
    private $db;
    public function __construct() { 
        $this->db = Database::getInstance()->getConnection(); 
    }

    private function generateId() {
        $tahunBulan = date('Ym'); 
        $prefix = 'CT' . $tahunBulan;
        $stmt = $this->db->prepare("SELECT id_izin FROM tbl_izin WHERE id_izin LIKE ? ORDER BY id_izin DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($last) {
            $lastNum = (int)substr($last['id_izin'], strlen($prefix));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        return $prefix . str_pad($newNum, 3, '0', STR_PAD_LEFT);
    }
    public function getAll() {
        $stmt = $this->db->query("SELECT c.*, p.nama as pegawai_nama, p.jumlah_cuti as sisa_cuti
                                  FROM tbl_izin c 
                                  JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                                  ORDER BY c.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT c.*, p.nama as pegawai_nama, p.jumlah_cuti as sisa_cuti
                                    FROM tbl_izin c 
                                    JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                                    WHERE c.id_izin = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create($data) {
        $id_pegawai = $data['id_pegawai'];
        $tgl_izin = $data['tgl_izin'];
        
        $stmt = $this->db->prepare("SELECT jumlah_cuti FROM tbl_pegawai WHERE id_pegawai = ?");
        $stmt->execute([$id_pegawai]);
        $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
        $sisa = $pegawai['jumlah_cuti'];
        
        $id_izin= $this->generateId();
        $sql = "INSERT INTO tbl_izin (id_izin, id_pegawai, tgl_izin, alasan, status) 
                VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$id_izin, $id_pegawai, $tgl_izin, $data['alasan']]);
        
        if ($result) {
            return ['status' => true, 'message' => 'Pengajuan cuti berhasil, menunggu persetujuan'];
        } else {
            return ['status' => false, 'message' => 'Gagal mengajukan cuti'];
        }
    }
    public function updateStatus($id, $status) {
        $cuti = $this->getById($id);
        if (!$cuti) return false;
        
        $old_status = $cuti['status'];
        $id_pegawai = $cuti['id_pegawai'];
        
        $this->db->beginTransaction();
        try {
            if ($status == 'disetujui' && $old_status != 'disetujui') {
                $stmt = $this->db->prepare("UPDATE tbl_pegawai SET jumlah_cuti = jumlah_cuti - ? WHERE id_pegawai = ? AND jumlah_cuti >= ?");
                $result = $stmt->execute([$hari, $id_pegawai, $hari]);
                if ($stmt->rowCount() == 0) {
                    throw new Exception("Sisa cuti tidak mencukupi untuk disetujui");
                }
            }
            elseif ($old_status == 'disetujui' && $status != 'disetujui') {
                $stmt = $this->db->prepare("UPDATE tbl_pegawai SET jumlah_cuti = jumlah_cuti + ? WHERE id_pegawai = ?");
                $stmt->execute([$hari, $id_pegawai]);
            }
            
            $stmt = $this->db->prepare("UPDATE tbl_izin SET status = ? WHERE id_izin = ?");
            $stmt->execute([$status, $id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    public function delete($id) {
        $cuti = $this->getById($id);
        if (!$cuti) return false;
        
        $this->db->beginTransaction();
        try {
            if ($cuti['status'] == 'disetujui') {
                $start = new DateTime($cuti['tgl_izin']);
                $end = new DateTime($row['tgl_izin']);
                $end->modify('+1 day');
                $hari = $start->diff($end)->days;
                $stmt = $this->db->prepare("UPDATE tbl_pegawai SET jumlah_cuti = jumlah_cuti + ? WHERE id_pegawai = ?");
                $stmt->execute([$hari, $cuti['id_pegawai']]);
            }
            $stmt = $this->db->prepare("DELETE FROM tbl_izin WHERE id_izin = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }}

    public function getTotalCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM tbl_izin c 
                JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                WHERE p.nama LIKE :search OR c.status LIKE :search";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getData($search = '', $orderBy = 'c.created_at', $orderDir = 'DESC', $limit = 10, $offset = 0) {
        $allowedColumns = ['c.id_izin', 'p.nama', 'c.tgl_izin', 'c.status', 'c.created_at'];
        if (!in_array($orderBy, $allowedColumns)) $orderBy = 'c.created_at';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT c.*, p.nama as pegawai_nama 
                FROM tbl_izin c 
                JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                WHERE p.nama LIKE :search OR c.status LIKE :search
                ORDER BY $orderBy $orderDir 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPending() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM tbl_izin WHERE status = 'pending'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getPendingList($limit = 5) {
        $stmt = $this->db->prepare("SELECT c.*, p.nama as pegawai_nama 
                                    FROM tbl_izin c 
                                    JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                                    WHERE c.status = 'pending' 
                                    ORDER BY c.created_at DESC LIMIT ?");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
