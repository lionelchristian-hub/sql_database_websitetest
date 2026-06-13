<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $izin = new Izin();
    if (isset($_POST['tambah'])) {
        $result = $izin->create($_POST);
        if ($result['status']) { 
            header('Location: ?page=izin&msg=added');
        } else {
            header('Location: ?page=izin&msg=error&error=' . urlencode($result['message']));
        }
        exit;
    } elseif (isset($_POST['update_status'])) {
        $izin->updateStatus($_POST['id_izin'], $_POST['status']);
        header('Location: ?page=izin&msg=updated');
        exit;
    }
}
if (isset($_GET['hapus'])) {
    $izin = new Izin();
    $izin->delete($_GET['hapus']);
    header('Location: ?page=izin&msg=deleted');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'c.created_at';
$order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'desc' : 'asc';
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$page = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;

$allowedLimits = [10, 25, 50, 100, 'all'];
if (!in_array($limit, $allowedLimits)) $limit = 10;

if ($limit === 'all') {
    $limitValue = 999999;
    $offset = 0;
} else {
    $limitValue = (int)$limit;
    $offset = ($page - 1) * $limitValue;
}

$izin = new Izin();
$totalData = $izin->getTotalCount($search);
$totalPages = ($limit === 'all') ? 1 : ceil($totalData / $limitValue);

$startPage = max(1, $page - 1);
$endPage = min($totalPages, $startPage + 3);
if ($endPage - $startPage < 3 && $startPage > 1) {
    $startPage = max(1, $endPage - 3);
}

$data = $izin->getData($search, $sort, $order, $limitValue, $offset);

$pegawaiModel = new Pegawai();
$pegawaiList = $pegawaiModel->getAll(); 

$showAddModal = isset($_GET['action']) && $_GET['action'] == 'tambah';

$msg = '';
$msgType = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':   $msg = 'Pengajuan izin berhasil!'; $msgType = 'success'; break;
        case 'updated': $msg = 'Status izin berhasil diupdate!'; $msgType = 'success'; break;
        case 'deleted': $msg = 'Data izin berhasil dihapus!'; $msgType = 'success'; break;
        case 'error':   $msg = isset($_GET['error']) ? urldecode($_GET['error']) : 'Terjadi kesalahan'; $msgType = 'error'; break;
    }
}

include __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="card">
    <h2><i class="fas fa-calendar-check"></i> Manajemen Izin</h2>
    
    <?php if ($msg): ?>
    <div class="alert <?= $msgType ?>">
        <span><?= $msg ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="get" class="search-form">
            <input type="hidden" name="page" value="izin">
            <input type="hidden" name="sort" value="<?= $sort ?>">
            <input type="hidden" name="order" value="<?= $order ?>">
            <input type="hidden" name="limit" value="<?= $limit ?>">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari nama pegawai atau status..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>
                <?php if ($search): ?>
                    <a href="?page=izin" class="reset-btn">Reset</a>
                <?php endif; ?>
            </div>
        </form>
        <div class="filter-add">
            <div class="filter-form">
                <span>Tampilkan:</span>
                <select name="limit" onchange="this.form.submit()" form="filterForm">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    <option value="all" <?= $limit == 'all' ? 'selected' : '' ?>>Semua</option>
                </select>
                <form id="filterForm" method="get" style="display: none;">
                    <input type="hidden" name="page" value="izin">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="order" value="<?= $order ?>">
                </form>
            </div>
            <a href="?page=izin&action=tambah" class="btn-add"><i class="fas fa-plus"></i> Ajukan Izin</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><a href="?page=izin&search=<?= urlencode($search) ?>&sort=c.id_izin&order=<?= $sort == 'c.id_izin' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">ID <?= $sort == 'c.id_izin' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=izin&search=<?= urlencode($search) ?>&sort=p.nama&order=<?= $sort == 'p.nama' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Pegawai <?= $sort == 'p.nama' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=izin&search=<?= urlencode($search) ?>&sort=c.tgl_izin&order=<?= $sort == 'c.tgl_izin' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Tanggal Izin <?= $sort == 'c.tgl_izin' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=izin&search=<?= urlencode($search) ?>&sort=c.status&order=<?= $sort == 'c.status' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Status <?= $sort == 'c.status' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= $row['id_izin'] ?></td>
                        <td><?= htmlspecialchars($row['pegawai_nama']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tgl_izin']??'')) ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php elseif ($row['status'] == 'disetujui'): ?>
                                <span class="badge badge-success">Disetujui</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Ditolak</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <?php if ($row['status'] == 'pending'): ?>
                                <button class="btn-status" data-id="<?= $row['id_izin'] ?>" data-status="disetujui" title="Setujui"><i class="fas fa-check-circle" style="color:#2ecc71;"></i></button>
                                <button class="btn-status" data-id="<?= $row['id_izin'] ?>" data-status="ditolak" title="Tolak"><i class="fas fa-times-circle" style="color:#e74c3c;"></i></button>
                            <?php endif; ?>
                            <a href="#" class="btn-delete" data-id="<?= $row['id_izin'] ?>" title="Hapus"><i class="fas fa-trash-alt" style="color:#e74c3c;"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Tidak ada data izin</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($limit !== 'all' && $totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=izin&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page-1 ?>" class="page-link">« Prev</a>
        <?php endif; ?>
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=izin&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=izin&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page+1 ?>" class="page-link">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modalForm" class="modal" style="display: <?= $showAddModal ? 'flex' : 'none' ?>;">
    <div class="modal-content" style="width: 500px;">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3><i class="fas fa-calendar-plus"></i> Ajukan Izin</h3>
        <form method="post">
            <input type="hidden" name="tambah" value="1">
            <div class="form-group">
                <label>Pegawai</label>
                <select name="id_pegawai" required>
                    <option value="">Pilih Pegawai</option>
                    <?php foreach ($pegawaiList as $peg): ?>
                        <option value="<?= $peg['id_pegawai'] ?>"><?= htmlspecialchars($peg['nama']) ?> (Sisa izin: <?= $peg['jumlah_cuti'] ?> hari)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Izin</label>
                <input type="date" name="tgl_izin" required>
            </div>
            <div class="form-group">
                <label>Alasan Izin</label>
                <textarea name="alasan" rows="3" required></textarea>
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn-save">Ajukan</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 350px;">
        <span class="close" onclick="closeConfirmModal()">&times;</span>
        <h3>Konfirmasi Hapus</h3>
        <p>Apakah Anda yakin ingin menghapus data izin ini?</p>
        <div class="form-buttons">
            <button id="confirmDeleteBtn" class="btn-save" style="background:#e74c3c;">Hapus</button>
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Batal</button>
        </div>
    </div>
</div>

<form id="statusForm" method="post" style="display: none;">
    <input type="hidden" name="update_status" value="1">
    <input type="hidden" name="id_izin" id="status_id">
    <input type="hidden" name="status" id="status_value">
</form>

<style>
.badge { display: inline-block; padding: 4px 8px; border-radius: 20px; font-size: 12px; font-weight: 500; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-success { background: #d4edda; color: #155724; }
.badge-danger { background: #f8d7da; color: #721c24; }
.btn-status { background: none; border: none; cursor: pointer; margin: 0 5px; font-size: 18px; }

/* DARK MODE COMPATIBLE VARIABLES */
.card { background: var(--bg-card); color: var(--text-body); border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.toolbar { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin: 20px 0; }
.search-form { flex: 2; min-width: 250px; }
.search-box { display: flex; align-items: center; background: var(--input-bg); border-radius: 8px; padding: 0 10px; border: 1px solid var(--input-border); height: 38px; }
.search-box i { color: var(--text-muted); }
.search-box input { flex: 1; border: none; background: transparent; padding: 0 10px; height: 36px; outline: none; color: var(--text-body); }
.search-box button { background: #3498db; border: none; color: white; padding: 0 16px; border-radius: 6px; height: 32px; cursor: pointer; }
.reset-btn { margin-left: 10px; background: var(--border-color); color: var(--text-body); padding: 0 12px; border-radius: 6px; text-decoration: none; line-height: 32px; display: inline-block; text-align: center; }
.filter-add { display: flex; gap: 10px; align-items: center; }
.filter-form { display: flex; align-items: center; gap: 8px; background: var(--input-bg); color: var(--text-body); padding: 0 12px; border-radius: 8px; height: 38px; border: 1px solid var(--input-border); }
.filter-form select { border: none; background: transparent; padding: 0 8px; height: 36px; cursor: pointer; color: var(--text-body); outline: none; }
.filter-form select option { background: var(--bg-card); color: var(--text-body); }
.btn-add { background: #2ecc71; border: none; color: white; padding: 0 18px; border-radius: 8px; cursor: pointer; height: 38px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-size: 14px; }
.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-body); }
.table th { background: var(--bg-card); color: var(--text-main); font-weight: 600; border-bottom: 2px solid var(--border-color); }
.table th a { color: var(--text-main); text-decoration: none; }
.table th a:hover { color: #3498db; }
.table tbody tr:hover { background: var(--table-hover); }
.actions { display: flex; gap: 12px; align-items: center; }
.pagination { margin-top: 20px; display: flex; justify-content: center; gap: 8px; }
.page-link { background: var(--input-bg); color: var(--text-body); padding: 6px 12px; border-radius: 6px; text-decoration: none; border: 1px solid var(--input-border); }
.page-link.active { background: #3498db; color: white; border-color: #3498db; }
.alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; position: relative; }
.alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.alert.error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
.close-alert { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 20px; cursor: pointer; color: inherit; }
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: var(--bg-card); color: var(--text-body); border: 1px solid var(--border-color); padding: 25px; border-radius: 16px; width: 450px; max-width: 90%; }
.close { float: right; font-size: 28px; cursor: pointer; color: var(--text-muted); }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: var(--text-main); }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid var(--input-border); background: var(--input-bg); color: var(--text-body); border-radius: 8px; outline: none; }
.form-group select option { background: var(--bg-card); color: var(--text-body); }
.form-buttons { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.btn-save { background: #2ecc71; color: white; border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; }
.btn-cancel { background: var(--border-color); color: var(--text-muted); border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; }
.text-center { text-align: center; }
@media (max-width: 640px) { .toolbar { flex-direction: column; } .filter-add { justify-content: space-between; } }
</style>

<script>
function closeModal() {
    const url = new URL(window.location.href);
    url.searchParams.delete('action');
    window.location.href = url.toString();
}
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
let deleteId = null;
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        deleteId = this.getAttribute('data-id');
        document.getElementById('confirmModal').style.display = 'flex';
    });
});
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteId) {
        window.location.href = '?page=izin&hapus=' + deleteId + '&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page ?>';
    }
});
document.querySelectorAll('.btn-status').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.getAttribute('data-id');
        let status = this.getAttribute('data-status');
        document.getElementById('status_id').value = id;
        document.getElementById('status_value').value = status;
        document.getElementById('statusForm').submit();
    });
});
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        if (event.target.id === 'modalForm') {
            closeModal();
        } else {
            closeConfirmModal();
        }
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>