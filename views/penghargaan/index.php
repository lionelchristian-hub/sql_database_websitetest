<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $penghargaan = new Penghargaan();
    if (isset($_POST['tambah'])) {
        $result = $penghargaan->create($_POST);
        header('Location: ?page=penghargaan&msg=' . ($result ? 'added' : 'error'));
        exit;
    } elseif (isset($_POST['edit'])) {
        $result = $penghargaan->update($_POST['id_penghargaan'], $_POST);
        header('Location: ?page=penghargaan&msg=' . ($result ? 'updated' : 'error'));
        exit;
    }
}
if (isset($_GET['hapus'])) {
    $penghargaan = new Penghargaan();
    $penghargaan->delete($_GET['hapus']);
    header('Location: ?page=penghargaan&msg=deleted');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'p.tgl_penghargaan';
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

$penghargaan = new Penghargaan();
$totalData = $penghargaan->getTotalCount($search);
$totalPages = ($limit === 'all') ? 1 : ceil($totalData / $limitValue);

$startPage = max(1, $page - 1);
$endPage = min($totalPages, $startPage + 3);
if ($endPage - $startPage < 3 && $startPage > 1) {
    $startPage = max(1, $endPage - 3);
}

$data = $penghargaan->getData($search, $sort, $order, $limitValue, $offset);

$pegawaiModel = new Pegawai();
$pegawaiList = $pegawaiModel->getAll(); 

$showAddModal = isset($_GET['action']) && $_GET['action'] == 'tambah';
$showEditModal = isset($_GET['edit']) && (int)$_GET['edit'] > 0;
$editData = null;

if ($showEditModal) {
    $editData = $penghargaan->getById((int)$_GET['edit']);
    if (!$editData) $showEditModal = false;
}

$msg = '';
$msgType = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':   $msg = 'Data penghargaan berhasil ditambahkan!'; $msgType = 'success'; break;
        case 'updated': $msg = 'Data penghargaan berhasil diupdate!'; $msgType = 'success'; break;
        case 'deleted': $msg = 'Data penghargaan berhasil dihapus!'; $msgType = 'success'; break;
        case 'error':   $msg = 'Terjadi kesalahan sistem.'; $msgType = 'error'; break;
    }
}

include __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="card">
    <h2><i class="fas fa-trophy"></i> Manajemen Penghargaan Pegawai</h2>
    
    <?php if ($msg): ?>
    <div class="alert <?= $msgType ?>">
        <span><?= $msg ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="get" class="search-form">
            <input type="hidden" name="page" value="penghargaan">
            <input type="hidden" name="sort" value="<?= $sort ?>">
            <input type="hidden" name="order" value="<?= $order ?>">
            <input type="hidden" name="limit" value="<?= $limit ?>">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari nama pegawai atau penghargaan..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>
                <?php if ($search): ?>
                    <a href="?page=penghargaan" class="reset-btn">Reset</a>
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
                    <input type="hidden" name="page" value="penghargaan">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="order" value="<?= $order ?>">
                </form>
            </div>
            <a href="?page=penghargaan&action=tambah" class="btn-add"><i class="fas fa-plus"></i> Tambah Penghargaan</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><a href="?page=penghargaan&search=<?= urlencode($search) ?>&sort=p.id_penghargaan&order=<?= $sort == 'p.id_penghargaan' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">ID <?= $sort == 'p.id_penghargaan' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=penghargaan&search=<?= urlencode($search) ?>&sort=e.nama&order=<?= $sort == 'e.nama' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Pegawai <?= $sort == 'e.nama' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=penghargaan&search=<?= urlencode($search) ?>&sort=p.nama_penghargaan&order=<?= $sort == 'p.nama_penghargaan' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Nama Penghargaan <?= $sort == 'p.nama_penghargaan' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=penghargaan&search=<?= urlencode($search) ?>&sort=p.tgl_penghargaan&order=<?= $sort == 'p.tgl_penghargaan' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Tanggal <?= $sort == 'p.tgl_penghargaan' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th>Keterangan / Deskripsi</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= $row['id_penghargaan'] ?></td>
                        <td><?= htmlspecialchars($row['pegawai_nama']) ?></td>
                        <td>
                            <span class="badge badge-success"><i class="fas fa-award"></i> <?= htmlspecialchars($row['nama_penghargaan']) ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($row['tgl_penghargaan']??'')) ?></td>
                        <td><?= htmlspecialchars($row['keterangan'] ?? '') ?></td>
                        <td class="actions">
                            <a href="?page=penghargaan&edit=<?= $row['id_penghargaan'] ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page ?>" class="btn-edit-action" title="Edit"><i class="fas fa-edit" style="color:#3498db;"></i></a>
                            <a href="#" class="btn-delete" data-id="<?= $row['id_penghargaan'] ?>" title="Hapus"><i class="fas fa-trash-alt" style="color:#e74c3c;"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Tidak ada data penghargaan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($limit !== 'all' && $totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=penghargaan&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page-1 ?>" class="page-link">« Prev</a>
        <?php endif; ?>
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=penghargaan&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=penghargaan&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page+1 ?>" class="page-link">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modalForm" class="modal" style="display: <?= $showAddModal ? 'flex' : 'none' ?>;">
    <div class="modal-content" style="width: 500px;">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3><i class="fas fa-plus-circle"></i> Tambah Penghargaan</h3>
        <form method="post">
            <input type="hidden" name="tambah" value="1">
            <div class="form-group">
                <label>Pegawai</label>
                <select name="id_pegawai" required>
                    <option value="">Pilih Pegawai</option>
                    <?php foreach ($pegawaiList as $peg): ?>
                        <option value="<?= $peg['id_pegawai'] ?>"><?= htmlspecialchars($peg['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Penghargaan</label>
                <input type="text" name="nama_penghargaan" required placeholder="Contoh: Karyawan Terbaik Bulanan, dll.">
            </div>
            <div class="form-group">
                <label>Tanggal Penghargaan</label>
                <input type="date" name="tgl_penghargaan" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Keterangan / Deskripsi Prestasi</label>
                <textarea name="keterangan" rows="4" required placeholder="Tuliskan detail pencapaian atau alasan pemberian penghargaan..."></textarea>
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn-save">Simpan</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditForm" class="modal" style="display: <?= $showEditModal ? 'flex' : 'none' ?>;">
    <div class="modal-content" style="width: 500px;">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3><i class="fas fa-edit"></i> Edit Penghargaan</h3>
        <form method="post">
            <input type="hidden" name="edit" value="1">
            <input type="hidden" name="id_penghargaan" value="<?= $editData['id_penghargaan'] ?? '' ?>">
            <div class="form-group">
                <label>Pegawai</label>
                <select name="id_pegawai" required>
                    <option value="">Pilih Pegawai</option>
                    <?php foreach ($pegawaiList as $peg): ?>
                        <option value="<?= $peg['id_pegawai'] ?>" <?= (isset($editData['id_pegawai']) && $editData['id_pegawai'] == $peg['id_pegawai']) ? 'selected' : '' ?>><?= htmlspecialchars($peg['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Penghargaan</label>
                <input type="text" name="nama_penghargaan" value="<?= htmlspecialchars($editData['nama_penghargaan'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Tanggal Penghargaan</label>
                <input type="date" name="tgl_penghargaan" value="<?= $editData['tgl_penghargaan'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Keterangan / Deskripsi Prestasi</label>
                <textarea name="keterangan" rows="4" required><?= htmlspecialchars($editData['keterangan'] ?? '') ?></textarea>
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn-save">Update</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 350px;">
        <span class="close" onclick="closeConfirmModal()">&times;</span>
        <h3>Konfirmasi Hapus</h3>
        <p>Apakah Anda yakin ingin menghapus data penghargaan ini?</p>
        <div class="form-buttons">
            <button id="confirmDeleteBtn" class="btn-save" style="background:#e74c3c;">Hapus</button>
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Batal</button>
        </div>
    </div>
</div>

<style>
.badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.badge-success i { margin-right: 3px; }
.btn-edit-action { background: none; border: none; cursor: pointer; font-size: 16px; text-decoration: none; }

/* DARK MODE VARIABLE INTEGRATION */
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
.btn-add { background: #2ecc71; border: none; color: white; padding: 0 18px; border-radius: 8px; cursor: pointer; height: 38px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-size: 14px; font-weight: 500; }
.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-body); }
.table th { background: var(--bg-card); color: var(--text-main); font-weight: 600; border-bottom: 2px solid var(--border-color); }
.table th a { color: var(--text-main); text-decoration: none; }
.table th a:hover { color: #3498db; }
.table tbody tr:hover { background: var(--table-hover); }
.actions { display: flex; gap: 15px; align-items: center; }
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
        window.location.href = '?page=penghargaan&hapus=' + deleteId + '&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page ?>';
    }
});
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        if (event.target.id === 'modalForm' || event.target.id === 'modalEditForm') {
            closeModal();
        } else {
            closeConfirmModal();
        }
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>