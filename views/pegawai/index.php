<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pegawai = new Pegawai();
    if (isset($_POST['tambah'])) {
        $pegawai->create($_POST, $_FILES['foto']);
        header('Location: ?page=pegawai&msg=added');
        exit;
    } elseif (isset($_POST['edit'])) {
        $pegawai->update($_POST['id_pegawai'], $_POST, $_FILES['foto']);
        header('Location: ?page=pegawai&msg=updated');
        exit;
    }
}
if (isset($_GET['hapus'])) {
    $pegawai = new Pegawai();
    $pegawai->delete($_GET['hapus']);
    header('Location: ?page=pegawai&msg=deleted');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'p.id_pegawai';
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

$pegawai = new Pegawai();
$totalData = $pegawai->getTotalCount($search);
$totalPages = ($limit === 'all') ? 1 : ceil($totalData / $limitValue);

$startPage = max(1, $page - 1);
$endPage = min($totalPages, $startPage + 3);
if ($endPage - $startPage < 3 && $startPage > 1) {
    $startPage = max(1, $endPage - 3);
}

$data = $pegawai->getData($search, $sort, $order, $limitValue, $offset);
$editData = isset($_GET['edit']) ? $pegawai->getById($_GET['edit']) : null;

$showAddModal = isset($_GET['action']) && $_GET['action'] == 'tambah';
$showEditModal = ($editData !== null);
$showModal = $showAddModal || $showEditModal;

$msg = '';
$msgType = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':   $msg = 'Data pegawai berhasil ditambahkan!'; $msgType = 'success'; break;
        case 'updated': $msg = 'Data pegawai berhasil diupdate!'; $msgType = 'success'; break;
        case 'deleted': $msg = 'Data pegawai berhasil dihapus!'; $msgType = 'success'; break;
        case 'error':   $msg = 'Terjadi kesalahan, silakan coba lagi.'; $msgType = 'error'; break;
    }
}

$deptModel = new Departemen();
$jabModel = new Jabatan();
$departemenList = $deptModel->getAll();
$jabatanList = $jabModel->getAll();

include __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="card">
    <h2><i class="fas fa-users"></i> Manajemen Pegawai</h2>
    
    <?php if ($msg): ?>
    <div class="alert <?= $msgType ?>">
        <span><?= $msg ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="get" class="search-form">
            <input type="hidden" name="page" value="pegawai">
            <input type="hidden" name="sort" value="<?= $sort ?>">
            <input type="hidden" name="order" value="<?= $order ?>">
            <input type="hidden" name="limit" value="<?= $limit ?>">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari ID, nama, departemen, jabatan..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>
                <?php if ($search): ?>
                    <a href="?page=pegawai" class="reset-btn">Reset</a>
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
                    <input type="hidden" name="page" value="pegawai">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="order" value="<?= $order ?>">
                </form>
            </div>
            <a href="?page=pegawai&action=tambah" class="btn-add"><i class="fas fa-plus"></i> Tambah</a>
            <a href="views/report/print_pegawai.php?search=<?= urlencode($search) ?>" class="btn-print"><i class="fas fa-print"></i> Print</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th><a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=p.id_pegawai&order=<?= $sort == 'p.id_pegawai' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">ID Pegawai <?= $sort == 'p.id_pegawai' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=p.nama&order=<?= $sort == 'p.nama' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Nama <?= $sort == 'p.nama' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=d.departemen&order=<?= $sort == 'd.departemen' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Departemen <?= $sort == 'd.departemen' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=j.jabatan&order=<?= $sort == 'j.jabatan' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Jabatan <?= $sort == 'j.jabatan' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th><a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=p.status_kerja&order=<?= $sort == 'p.status_kerja' && $order == 'asc' ? 'desc' : 'asc' ?>&limit=<?= $limit ?>&page_num=<?= $page ?>">Status Kerja <?= $sort == 'p.status_kerja' ? ($order == 'asc' ? '▲' : '▼') : '' ?></a></th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td class="foto-cell">
                            <?php 
                            $fotoFile = $row['foto'];
                            if (!empty($fotoFile)) {
                                $fotoPath = $_SERVER['DOCUMENT_ROOT'] . '/proyek_kepegawaian/assets/uploads/' . $fotoFile;
                                if (file_exists($fotoPath)) {
                                    echo '<img src="' . BASE_URL . 'assets/uploads/' . $fotoFile . '" class="rounded-circle" width="45" height="45" style="object-fit: cover;">';
                                } else {
                                    echo '<div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:45px;height:45px;"><i class="fas fa-user text-white"></i></div>';
                                }
                            } else {
                                echo '<div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:45px;height:45px;"><i class="fas fa-user text-white"></i></div>';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['id_pegawai']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['departemen']) ?></td>
                        <td><?= htmlspecialchars($row['jabatan']) ?></td>
                        <td><?= $row['status_kerja'] ?></td>
                        <td class="actions">
                            <a href="?page=pegawai&edit=<?= $row['id_pegawai'] ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="#" class="btn-delete" data-id="<?= $row['id_pegawai'] ?>" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Tidak ada data pegawai</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($limit !== 'all' && $totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page-1 ?>" class="page-link">« Prev</a>
        <?php endif; ?>
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=pegawai&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page+1 ?>" class="page-link">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modalForm" class="modal" style="display: <?= $showModal ? 'flex' : 'none' ?>;">
    <div class="modal-content" style="width: 750px; max-width: 95%;">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3><?= $showEditModal ? 'Edit Pegawai' : 'Tambah Pegawai' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?php if ($showEditModal): ?>
                <input type="hidden" name="id_pegawai" value="<?= $editData['id_pegawai'] ?>">
                <input type="hidden" name="edit" value="1">
            <?php else: ?>
                <input type="hidden" name="tambah" value="1">
            <?php endif; ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= $showEditModal ? htmlspecialchars($editData['nama']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Departemen</label>
                    <select name="id_departemen" required>
                        <option value="">Pilih Departemen</option>
                        <?php foreach ($departemenList as $dept): ?>
                        <option value="<?= $dept['id_departemen'] ?>" <?= ($showEditModal && $editData['id_departemen'] == $dept['id_departemen']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['departemen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" required>
                        <option value="">Pilih Jabatan</option>
                        <?php foreach ($jabatanList as $jab): ?>
                        <option value="<?= $jab['id_jabatan'] ?>" <?= ($showEditModal && $editData['id_jabatan'] == $jab['id_jabatan']) ? 'selected' : '' ?>><?= htmlspecialchars($jab['jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="2"><?= $showEditModal ? htmlspecialchars($editData['alamat']) : '' ?></textarea>
                </div>
                <div class="form-group">
                    <label>Telepon</label>
                    <input type="text" name="telepon" value="<?= $showEditModal ? htmlspecialchars($editData['telepon']) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $showEditModal ? htmlspecialchars($editData['email']) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Gaji</label>
                    <input type="number" step="0.01" name="gaji" value="<?= $showEditModal ? $editData['gaji'] : '' ?>">
                </div>
                <div class="form-group">
                    <label>Status Pernikahan</label>
                    <select name="status_pernikahan">
                        <option value="Menikah" <?= ($showEditModal && $editData['status_pernikahan'] == 'Menikah') ? 'selected' : '' ?>>Menikah</option>
                        <option value="Belum" <?= ($showEditModal && $editData['status_pernikahan'] == 'Belum') ? 'selected' : '' ?>>Belum</option>
                        <option value="Berpisah" <?= ($showEditModal && $editData['status_pernikahan'] == 'Berpisah') ? 'selected' : '' ?>>Berpisah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin">
                        <option value="Laki-Laki" <?= ($showEditModal && $editData['jenis_kelamin'] == 'Laki-Laki') ? 'selected' : '' ?>>Laki-Laki</option>
                        <option value="Perempuan" <?= ($showEditModal && $editData['jenis_kelamin'] == 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Kerja</label>
                    <select name="status_kerja">
                        <option value="Tetap" <?= ($showEditModal && $editData['status_kerja'] == 'Tetap') ? 'selected' : '' ?>>Tetap</option>
                        <option value="Kontrak" <?= ($showEditModal && $editData['status_kerja'] == 'Kontrak') ? 'selected' : '' ?>>Kontrak</option>
                        <option value="Pensiun" <?= ($showEditModal && $editData['status_kerja'] == 'Pensiun') ? 'selected' : '' ?>>Pensiun</option>
                        <option value="Keluar" <?= ($showEditModal && $editData['status_kerja'] == 'Keluar') ? 'selected' : '' ?>>Keluar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Cuti</label>
                    <input type="number" name="jumlah_cuti" value="<?= $showEditModal ? $editData['jumlah_cuti'] : 0 ?>">
                </div>
                <div class="form-group">
                    <label>Jenjang Pendidikan</label>
                    <select name="jenjang_pendidikan">
                        <option value="SD" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'SD') ? 'selected' : '' ?>>SD</option>
                        <option value="SMP" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'SMP') ? 'selected' : '' ?>>SMP</option>
                        <option value="SMA" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'SMA') ? 'selected' : '' ?>>SMA</option>
                        <option value="SMK" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'SMK') ? 'selected' : '' ?>>SMK</option>
                        <option value="D1" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'D1') ? 'selected' : '' ?>>D1</option>
                        <option value="D2" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'D2') ? 'selected' : '' ?>>D2</option>
                        <option value="D3" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'D3') ? 'selected' : '' ?>>D3</option>
                        <option value="D4" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'D4') ? 'selected' : '' ?>>D4</option>
                        <option value="S1" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'S1') ? 'selected' : '' ?>>S1</option>
                        <option value="S2" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'S2') ? 'selected' : '' ?>>S2</option>
                        <option value="S3" <?= ($showEditModal && $editData['jenjang_pendidikan'] == 'S3') ? 'selected' : '' ?>>S3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Mulai Kerja</label>
                    <input type="date" name="tgl_mulai_kerja" value="<?= $showEditModal ? $editData['tgl_mulai_kerja'] : '' ?>">
                </div>
                <div class="form-group">
                    <label>Foto</label>
                    <input type="file" name="foto" accept="image/*">
                    <?php if ($showEditModal && !empty($editData['foto'])): ?>
                        <div style="margin-top: 5px;">
                            <img src="<?= BASE_URL ?>assets/uploads/<?= $editData['foto'] ?>" width="50" style="border-radius: 50%;">
                            <small>Kosongkan jika tidak ingin mengganti</small>
                        </div>
                    <?php endif; ?>
                </div>
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
        <p>Apakah Anda yakin ingin menghapus pegawai ini?</p>
        <div class="form-buttons">
            <button id="confirmDeleteBtn" class="btn-save" style="background:#e74c3c;">Hapus</button>
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Batal</button>
        </div>
    </div>
</div>

<style>
.foto-cell img { border-radius: 50%; object-fit: cover; width: 45px; height: 45px; }
.foto-cell .bg-secondary { background-color: #6c757d; display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; }
.card { background: var(--bg-card); color: var(--text-body); border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.toolbar { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin: 20px 0; }
.search-form { flex: 2; min-width: 250px; }
.search-box { display: flex; align-items: center; background: var(--input-bg); border-radius: 8px; padding: 0 10px; border: 1px solid var(--input-border); height: 38px; }
.search-box i { color: var(--text-muted); }
.search-box input { flex: 1; border: none; background: transparent; padding: 0 10px; height: 36px; outline: none; color: var(--text-body); }
.search-box button { background: #3498db; border: none; color: white; padding: 0 16px; border-radius: 6px; height: 32px; cursor: pointer; }
.reset-btn { margin-left: 10px; background: var(--table-hover); color: var(--text-main); padding: 0 12px; border-radius: 6px; text-decoration: none; line-height: 32px; }
.filter-add { display: flex; gap: 10px; align-items: center; }
.filter-form { display: flex; align-items: center; gap: 8px; background: var(--input-bg); border: 1px solid var(--input-border); padding: 0 12px; border-radius: 8px; height: 38px; color: var(--text-body); }
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
.table tr:hover { background: var(--table-hover); }
.actions { display: flex; gap: 12px; }
.btn-edit, .btn-delete { text-decoration: none; font-size: 18px; }
.btn-edit { color: #f39c12; }
.btn-delete { color: #e74c3c; }
.pagination { margin-top: 20px; display: flex; justify-content: center; gap: 8px; }
.page-link { background: var(--table-hover); color: var(--text-body); padding: 6px 12px; border-radius: 6px; text-decoration: none; }
.page-link.active { background: #3498db; color: white; }
.alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; position: relative; }
.alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.alert.error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
html.dark-mode .alert.success { background: rgba(16, 185, 129, 0.2); color: #34d399; }
html.dark-mode .alert.error { background: rgba(239, 68, 68, 0.2); color: #f87171; }
.close-alert { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 20px; cursor: pointer; color: inherit; }
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: var(--bg-card); color: var(--text-body); padding: 25px; border-radius: 16px; width: 750px; max-width: 95%; border: 1px solid var(--border-color); }
.close { float: right; font-size: 28px; cursor: pointer; color: var(--text-muted); }
.form-group { margin-bottom: 20px; }
.form-group label { color: var(--text-main); font-weight: 500; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid var(--input-border); border-radius: 8px; background: var(--input-bg); color: var(--text-body); }
.form-buttons { display: flex; gap: 10px; justify-content: flex-end; }
.btn-save { background: #2ecc71; color: white; border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; }
.btn-cancel { background: var(--table-hover); color: var(--text-main); border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; }
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
        window.location.href = '?page=pegawai&hapus=' + deleteId + '&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>&limit=<?= $limit ?>&page_num=<?= $page ?>';
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>