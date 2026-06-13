<?php
class Cuti {
    private $db;
    public function __construct() { 
        $this->db = Database::getInstance()->getConnection(); 
    }

    private function generateId() {
        $tahunBulan = date('Ym'); // contoh: 202504
        $prefix = 'CT' . $tahunBulan;
        $stmt = $this->db->prepare("SELECT id_cuti FROM tbl_cuti WHERE id_cuti LIKE ? ORDER BY id_cuti DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($last) {
            $lastNum = (int)substr($last['id_cuti'], strlen($prefix));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        return $prefix . str_pad($newNum, 3, '0', STR_PAD_LEFT);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT c.*, p.nama as pegawai_nama, p.jumlah_cuti as sisa_cuti 
                                  FROM tbl_cuti c 
                                  JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                                  ORDER BY c.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT c.*, p.nama as pegawai_nama, p.jumlah_cuti as sisa_cuti 
                                    FROM tbl_cuti c 
                                    JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                                    WHERE c.id_cuti = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $id_pegawai = $data['id_pegawai'];
        $tgl_mulai = $data['tgl_mulai'];
        $tgl_selesai = $data['tgl_selesai'];
        
        $start = new DateTime($tgl_mulai);
        $end = new DateTime($tgl_selesai);
        $end->modify('+1 day'); 
        $interval = $start->diff($end);
        $hari = $interval->days;
        
        $stmt = $this->db->prepare("SELECT jumlah_cuti FROM tbl_pegawai WHERE id_pegawai = ?");
        $stmt->execute([$id_pegawai]);
        $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
        $sisa = $pegawai['jumlah_cuti'];
        
        if ($hari > $sisa) {
            return ['status' => false, 'message' => "Sisa cuti tidak mencukupi (sisa: $sisa hari, diminta: $hari hari)"];
        }
        
        $id_cuti = $this->generateId();
        $sql = "INSERT INTO tbl_cuti (id_cuti, id_pegawai, tgl_mulai, tgl_selesai, alasan, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$id_cuti, $id_pegawai, $tgl_mulai, $tgl_selesai, $data['alasan']]);
        
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
        
        $start = new DateTime($cuti['tgl_mulai']);
        $end = new DateTime($cuti['tgl_selesai']);
        $end->modify('+1 day');
        $hari = $start->diff($end)->days;
        
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
            
            $stmt = $this->db->prepare("UPDATE tbl_cuti SET status = ? WHERE id_cuti = ?");
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
                $start = new DateTime($cuti['tgl_mulai']);
                $end = new DateTime($cuti['tgl_selesai']);
                $end->modify('+1 day');
                $hari = $start->diff($end)->days;
                $stmt = $this->db->prepare("UPDATE tbl_pegawai SET jumlah_cuti = jumlah_cuti + ? WHERE id_pegawai = ?");
                $stmt->execute([$hari, $cuti['id_pegawai']]);
            }
            $stmt = $this->db->prepare("DELETE FROM tbl_cuti WHERE id_cuti = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getTotalCount($search = '') {
        $sql = "SELECT COUNT(*) as total FROM tbl_cuti c 
                JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                WHERE p.nama LIKE :search OR c.status LIKE :search";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getData($search = '', $orderBy = 'c.created_at', $orderDir = 'DESC', $limit = 10, $offset = 0) {
        $allowedColumns = ['c.id_cuti', 'p.nama', 'c.tgl_mulai', 'c.tgl_selesai', 'c.status', 'c.created_at'];
        if (!in_array($orderBy, $allowedColumns)) $orderBy = 'c.created_at';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT c.*, p.nama as pegawai_nama 
                FROM tbl_cuti c 
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
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM tbl_cuti WHERE status = 'pending'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getPendingList($limit = 5) {
        $stmt = $this->db->prepare("SELECT c.*, p.nama as pegawai_nama 
                                    FROM tbl_cuti c 
                                    JOIN tbl_pegawai p ON c.id_pegawai = p.id_pegawai
                                    WHERE c.status = 'pending' 
                                    ORDER BY c.created_at DESC LIMIT ?");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
