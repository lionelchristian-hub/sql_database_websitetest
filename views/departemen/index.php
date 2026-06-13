<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $departemen = new Departemen();
    if (isset($_POST['tambah'])) {
        $departemen->create($_POST);
        header('Location: ?page=departemen&msg=added');
        exit;
    } elseif (isset($_POST['edit'])) {
        $departemen->update($_POST['id_departemen'], $_POST);
        header('Location: ?page=departemen&msg=updated');
        exit;
    }
}
if (isset($_GET['hapus'])) {
    $departemen = new Departemen();
    $departemen->delete($_GET['hapus']);
    header('Location: ?page=departemen&msg=deleted');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_departemen';
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

$departemen = new Departemen();
$totalData = $departemen->getTotalCount($search);
$totalPages = ($limit === 'all') ? 1 : ceil($totalData / $limitValue);

$startPage = max(1, $page - 1);
$endPage = min($totalPages, $startPage + 3);
if ($endPage - $startPage < 3 && $startPage > 1) {
    $startPage = max(1, $endPage - 3);
}

$data = $departemen->getData($search, $sort, $order, $limitValue, $offset);
$editData = isset($_GET['edit']) ? $departemen->getById($_GET['edit']) : null;

$showAddModal = isset($_GET['action']) && $_GET['action'] == 'tambah';
$showEditModal = ($editData !== null);
$showModal = $showAddModal || $showEditModal;

$msg = '';
$msgType = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':   $msg = 'Data berhasil ditambahkan!'; $msgType = 'success'; break;
        case 'updated': $msg = 'Data berhasil diupdate!'; $msgType = 'success'; break;
        case 'deleted': $msg = 'Data berhasil dihapus!'; $msgType = 'success'; break;
    }
}

include __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="card">
    <h2><i class="fas fa-building"></i> Manajemen Departemen</h2>
    
    <?php if ($msg): ?>
    <div class="alert <?= $msgType ?>">
        <span><?= $msg ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="get" class="search-form">
            <input type="hidden" name="page" value="departemen">
            <input type="hidden" name="sort" value="<?= $sort ?>">
            <input type="hidden" name="order" value="<?= $order ?>">
            <input type="hidden" name="limit" value="<?= $limit ?>">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari kode atau nama departemen..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>
                <?php if ($search): ?>
                    <a href="?page=departemen" class="reset-btn">Reset</a>
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
                    <input type="hidden" name="page" value="departemen">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="order" value="<?= $order ?>">
                </form>
            </div>
            <a href="?page=departemen&action=tambah" class="btn-add"><i class="fas fa-plus"></i> Tambah</a>
            <a href="views/report/print_departemen.php?search=<?= urlencode($search) ?>" class="btn-print"><i class="fas fa-print"></i> Print</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><a href="?page=departemen&search=<?= urlencode($search) ?>&sort=id_departemen&order=<?= $sort == 'id_departemen' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Kode <?= $sort == 'id_departemen' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=departemen&search=<?= urlencode($search) ?>&sort=departemen&order=<?= $sort == 'departemen' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Nama Departemen <?= $sort == 'departemen' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_departemen']) ?></td>
                        <td><?= htmlspecialchars($row['departemen']) ?></td>
                        <td class="actions">
                            <a href="?page=departemen&edit=<?= $row['id_departemen'] ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="#" class="btn-delete" data-id="<?= $row['id_departemen'] ?>" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">Tidak ada data departemen</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($limit !== 'all' && $totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=departemen&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page-1 ?>" class="page-link">« Prev</a>
        <?php endif; ?>
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=departemen&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=departemen&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page+1 ?>" class="page-link">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modalForm" class="modal" style="display: <?= $showModal ? 'flex' : 'none' ?>;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3><?= $showEditModal ? 'Edit Departemen' : 'Tambah Departemen' ?></h3>
        <form method="post">
            <?php if ($showEditModal): ?>
                <input type="hidden" name="id_departemen" value="<?= $editData['id_departemen'] ?>">
                <input type="hidden" name="edit" value="1">
            <?php else: ?>
                <input type="hidden" name="tambah" value="1">
            <?php endif; ?>
            <div class="form-group">
                <label>Nama Departemen</label>
                <input type="text" name="departemen" value="<?= $showEditModal ? htmlspecialchars($editData['departemen']) : '' ?>" required autofocus>
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn-save">Simpan</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 350px;">
        <span class="close" onclick="closeConfirmModal()">&times;</span>
        <h3>Konfirmasi Hapus</h3>
        <p>Apakah Anda yakin ingin menghapus departemen ini?</p>
        <div class="form-buttons">
            <button id="confirmDeleteBtn" class="btn-save" style="background:#e74c3c;">Hapus</button>
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Batal</button>
        </div>
    </div>
</div>

<style>
.card { 
    background: var(--bg-card); 
    color: var(--text-body);
    border-radius: 12px; 
    padding: 20px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
}
.card h2 {
    color: var(--text-main);
}
.toolbar { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin: 20px 0; }
.search-form { flex: 2; min-width: 250px; }
.search-box { 
    display: flex; 
    align-items: center; 
    background: var(--input-bg); 
    border-radius: 8px; 
    padding: 0 10px; 
    border: 1px solid var(--input-border); 
    height: 38px; 
}
.search-box i { color: var(--text-muted); }
.search-box input { 
    flex: 1; 
    border: none; 
    background: transparent; 
    padding: 0 10px; 
    height: 36px; 
    outline: none; 
    color: var(--text-body);
}
.search-box button { background: #3498db; border: none; color: white; padding: 0 16px; border-radius: 6px; height: 32px; cursor: pointer; }
.reset-btn { 
    margin-left: 10px; 
    background: var(--border-color); 
    color: var(--text-main); 
    padding: 0 12px; 
    border-radius: 6px; 
    text-decoration: none; 
    line-height: 32px; 
}
.filter-add { display: flex; gap: 10px; align-items: center; }
.filter-form { 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    background: var(--input-bg); 
    padding: 0 12px; 
    border-radius: 8px; 
    height: 38px; 
    border: 1px solid var(--input-border);
}
.filter-form span { color: var(--text-muted); }
.filter-form select { border: none; background: transparent; padding: 0 8px; height: 36px; cursor: pointer; color: var(--text-body); }
.filter-form select option { background: var(--bg-card); color: var(--text-body); }

.btn-add, .btn-print { background: #2ecc71; border: none; color: white; padding: 0 18px; border-radius: 8px; cursor: pointer; height: 38px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-size: 14px; }
.btn-print { background: #3498db; }
.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
.table th { background: var(--bg-card); color: var(--text-main); font-weight: 600; border-bottom: 2px solid var(--border-color); }
.table th a { color: var(--text-main); text-decoration: none; }
.table th a:hover { color: #3498db; }
.table td { color: var(--text-body); }
.actions { display: flex; gap: 12px; }
.btn-edit, .btn-delete { text-decoration: none; font-size: 18px; }
.btn-edit { color: #f39c12; }
.btn-delete { color: #e74c3c; }

.pagination { margin-top: 20px; display: flex; justify-content: center; gap: 8px; }
.page-link { 
    background: var(--input-bg); 
    color: var(--text-body); 
    padding: 6px 12px; 
    border-radius: 6px; 
    text-decoration: none; 
    border: 1px solid var(--input-border);
}
.page-link.active { background: #3498db; color: white; border-color: #3498db; }

.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { 
    background: var(--bg-card); 
    color: var(--text-body);
    padding: 25px; 
    border-radius: 16px; 
    width: 450px; 
    max-width: 90%; 
    border: 1px solid var(--border-color);
}
.modal-content h3 { color: var(--text-main); }
.close { float: right; font-size: 28px; cursor: pointer; color: var(--text-muted); }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: var(--text-main); }
.form-group input { 
    width: 100%; 
    padding: 10px; 
    background: var(--input-bg);
    color: var(--text-body);
    border: 1px solid var(--input-border); 
    border-radius: 8px; 
}
.form-buttons { display: flex; gap: 10px; justify-content: flex-end; }
.btn-save { background: #2ecc71; color: white; border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; }
.btn-cancel { 
    background: var(--border-color); 
    color: var(--text-main); 
    border: none; 
    padding: 8px 20px; 
    border-radius: 30px; 
    cursor: pointer; 
}
.text-center { text-align: center; }

.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    position: relative;
}
.alert.success {
    background: rgba(46, 204, 113, 0.15);
    color: #2ecc71;
    border-left: 4px solid #2ecc71;
}
@media (max-width: 640px) { .toolbar { flex-direction: column; } .filter-add { justify-content: space-between; } }
</style>

<script>
function closeModal() {
    const url = new URL(window.location.href);
    url.searchParams.delete('edit');
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
        window.location.href = '?page=departemen&hapus=' + deleteId + '&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page ?>';
    }
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

<?php include __DIR__ . '/../layouts/header.php'; ?>