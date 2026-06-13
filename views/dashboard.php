<?php 
include __DIR__ . '/layouts/header.php'; 

$pegawai = new Pegawai();
$departemen = new Departemen();
$jabatan = new Jabatan();
$cuti = new Cuti();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; background: #ffffff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <div>
        <h3>📅 Selamat Datang di Sistem Kepegawaian</h3>
        <p>Gunakan menu sidebar untuk mengelola data master, transaksi, dan laporan.</p>
    </div>
    
    <div style="display: flex; align-items: center; gap: 15px; background: #f8f9fa; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; min-width: 240px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.02);">
        <div style="font-size: 30px; color: #2980b9;">
            🕒
        </div>
        <div>
            <div id="live-clock" style="font-size: 22px; font-weight: bold; color: #2c3e50; font-family: 'Courier New', Courier, monospace; letter-spacing: 1px;">
                00:00:00
            </div>
            <div id="live-date" style="font-size: 12px; color: #7f8c8d; font-weight: 500; margin-top: 2px;">
                Memuat tanggal...
            </div>
        </div>
    </div>
</div>

<script>
function updateClock() {
    const now = new Date();
    
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds}`;
    
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    const dayName = days[now.getDay()];
    const dayOfMonth = now.getDate();
    const monthName = months[now.getMonth()];
    const year = now.getFullYear();
    
    document.getElementById('live-date').textContent = `${dayName}, ${dayOfMonth} ${monthName} ${year}`;
}

setInterval(updateClock, 1000);

updateClock();
</script>

<?php
$modelPengumuman = new Pengumuman();
$pengumumanTerbaru = $modelPengumuman->getLatest();

if ($pengumumanTerbaru): 
?>
<div class="card" style="background: #eaf2f8; border-left: 5px solid #2980b9; border-right: 1px solid #d4e6f1; border-top: 1px solid #d4e6f1; border-bottom: 1px solid #d4e6f1; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div style="display: flex; align-items: flex-start; gap: 15px;">
        <div style="font-size: 24px; color: #2980b9; margin-top: 2px;">
            📢
        </div>
        <div style="flex: 1;">
            <h4 style="margin: 0 0 5px 0; color: #2c3e50; font-weight: 600; font-size: 16px;">
                <?= htmlspecialchars($pengumumanTerbaru['judul']) ?>
            </h4>
            <p style="margin: 0 0 8px 0; color: #555; font-size: 14px; line-height: 1.5;">
                <?= htmlspecialchars($pengumumanTerbaru['isi']) ?>
            </p>
            <small style="color: #7f8c8d; font-size: 11px; display: block;">
                🗓️ Diposting pada: <?= date('d M Y, H:i', strtotime($pengumumanTerbaru['tanggal'])) ?> WIB
            </small>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3><?= $pegawai->countAll() ?></h3>
        <p>Total Pegawai</p>
    </div>
    <div class="stat-card">
        <h3><?= $departemen->countAll() ?></h3>
        <p>Departemen</p>
    </div>
    <div class="stat-card">
        <h3><?= $jabatan->countAll() ?></h3>
        <p>Jabatan</p>
    </div>
    <div class="stat-card">
        <h3><?= $cuti->countPending() ?></h3>
        <p>Cuti Pending</p>
    </div>
</div>

<div class="row">
    <div class="col-half">
        <div class="card">
            <h3>📋 Pegawai Terbaru</h3>
            <table class="table-mini">
                <thead>
                    <tr><th>Nama</th><th>Departemen</th><th>Jabatan</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pegawai->getLatest(5) as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nama']) ?></td>
                        <td><?= htmlspecialchars($p['departemen']) ?></td>
                        <td><?= htmlspecialchars($p['jabatan']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($pegawai->countAll() == 0): ?>
                    <tr><td colspan="3">Belum ada data pegawai</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-half">
        <div class="card">
            <h3>⏳ Cuti Menunggu Persetujuan</h3>
            <table class="table-mini">
                <thead>
                    <tr><th>Pegawai</th><th>Mulai</th><th>Selesai</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cuti->getPendingList(5) as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['pegawai_nama']) ?></td>
                        <td><?= $c['tgl_mulai'] ?></td>
                        <td><?= $c['tgl_selesai'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($cuti->countPending() == 0): ?>
                    <tr><td colspan="3">Tidak ada cuti pending</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$penghargaanModel = new Penghargaan();
$achiever = $penghargaanModel->getLatestAchiever();

if ($achiever):
?>
<div class="card" style="background: linear-gradient(135deg, #2c3e50, #2980b9); color: white; border: none; position: relative; overflow: hidden; padding: 25px; margin-bottom: 20px;">
    
    <div style="position: absolute; right: 30px; bottom: -10px; font-size: 110px; color: rgba(255, 255, 255, 0.1); pointer-events: none;">
        <i class="fas fa-trophy"></i>
    </div>

    <div style="position: relative; z-index: 2;">
        <span style="background: #f1c40f; color: #2c3e50; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
            🏆 Employee Spotlight
        </span>
        
        <h2 style="margin-top: 15px; margin-bottom: 5px; font-size: 24px; font-weight: 600; color: #ffffff !important;">
            Selamat kepada <?= htmlspecialchars($achiever['nama_pegawai']) ?>!
        </h2>
        
        <p style="margin: 0; font-size: 14px; color: #ecf0f1; font-style: italic;">
            <?= htmlspecialchars($achiever['nama_jabatan'] ?? 'Pegawai') ?> yang baru saja menerima penghargaan: 
            <strong style="color: #f1c40f; font-style: normal;">"<?= htmlspecialchars($achiever['nama_penghargaan']) ?>"</strong> 
            pada tanggal <?= date('d M Y', strtotime($achiever['tgl_penghargaan'])) ?>.
        </p>

        <p style="margin-top: 10px; margin-bottom: 0; font-size: 12px; color: #bdc3c7;">
            Terima kasih atas dedikasi dan kerja keras yang luar biasa untuk kemajuan perusahaan!
        </p>
    </div>
</div>
<?php endif; ?>

<div class="card" style="margin-top: 20px; background: #fffcf4; border-left: 5px solid #f1c40f; border-radius: 6px; padding: 20px;">
    <h3 style="margin-top: 0; margin-bottom: 15px; color: #d35400; font-size: 18px; display: flex; align-items: center; gap: 8px;">
        🏅 Apresiasi Masa Kerja Pegawai (Bulan Ini)
    </h3>
    
    <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php
        $celebrations = $pegawai->getWorkAnniversariesThisMonth();
        
        if (!empty($celebrations)):
            foreach ($celebrations as $c): 
        ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: rgba(241, 196, 15, 0.1); border-radius: 6px;">
                <div>
                    <strong style="color: var(--text-main); font-size: 15px;"><?= htmlspecialchars($c['nama']) ?></strong>
                    <span style="display: block; font-size: 12px; color: var(--text-muted);">
                        Mulai bekerja: <?= date('d M Y', strtotime($c['tgl_mulai_kerja'])) ?>
                    </span>
                </div>
                <div style="text-align: right;">
                    <span style="background: #f1c40f; color: #7f6000; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                        🎉 Genap <?= $c['lama_kerja'] ?> Tahun
                    </span>
                </div>
            </div>
        <?php 
            endforeach; 
        else:
        ?>
            <p style="color: var(--text-muted); font-size: 14px; italic;">Tidak ada pegawai yang merayakan anniversary kerja di bulan ini.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3 style="margin-top: 0; margin-bottom: 15px; color: var(--text-main); font-size: 18px;">
        📝 Catatan Pengingat Tugas Anda
    </h3>
    
    <form action="index.php?page=proses_catatan&action=tambah" method="POST" style="margin-bottom: 20px;">
        <div style="display: flex; gap: 10px;">
            <input type="text" name="isi_catatan" placeholder="Ketik pengingat tugas baru di sini... (Contoh: Koreksi berkas cuti Budi)" required style="margin: 0; flex: 1;">
            <button type="submit" class="btn-primary" style="padding: 10px 20px; font-weight: bold;">+ Tambah</button>
        </div>
    </form>

    <div style="max-height: 250px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding-right: 5px;">
        <?php
        $modelCatatan = new Catatan();
        $myNotes = $modelCatatan->getByUserId(Session::get('user_id'));
        
        if (!empty($myNotes)):
            foreach ($myNotes as $note): 
        ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--table-hover); border-radius: 6px; border-left: 4px solid #3498db;">
                <div style="flex: 1; padding-right: 15px;">
                    <p style="margin: 0; font-size: 14px; color: var(--text-body); line-height: 1.4;">
                        <?= htmlspecialchars($note['isi_catatan']) ?>
                    </p>
                    <small style="color: var(--text-muted); font-size: 11px; display: block; margin-top: 4px;">
                        🕒 <?= date('d M, H:i', strtotime($note['waktu'])) ?>
                    </small>
                </div>
                <div>
                    <a href="index.php?page=proses_catatan&action=hapus&id=<?= $note['id_catatan'] ?>" 
                       onclick="return confirm('Hapus catatan pengingat ini?')" 
                       style="color: #e74c3c; text-decoration: none; font-size: 16px; font-weight: bold; padding: 5px 10px;" 
                       title="Hapus Catatan">
                       ✕
                    </a>
                </div>
            </div>
        <?php 
            endforeach; 
        else:
        ?>
            <p style="color: var(--text-muted); font-size: 14px; font-style: italic; text-align: center; margin: 15px 0;">
                Belum ada catatan pengingat. Ketik di atas untuk membuat tugas mandiri.
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4" style="margin-top: 20px;">
    <h3 style="margin-bottom: 15px; color: #333;">⏱️ Riwayat Aktivitas Terbaru</h3>
    <table class="table-mini" style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 10px;">Waktu</th>
                <th style="padding: 10px;">Pengguna</th>
                <th style="padding: 10px;">Aktivitas</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Instansiasi class dan panggil fungsi ambil data
            $logAktivitas = new LogAktivitas();
            $logs = $logAktivitas->getLatestLogs(5);
            
            foreach ($logs as $l): 
            ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; font-size: 13px; color: #777;">
                    <?= date('d M Y, H:i', strtotime($l['waktu'])) ?>
                </td>
                <td style="padding: 10px; font-weight: bold; color: #4e73df;">
                    <?= htmlspecialchars($l['nama'] ?? 'System') ?>
                </td>
                <td style="padding: 10px; font-size: 14px;">
                    <?= htmlspecialchars($l['aktivitas']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($logs)): ?>
            <tr><td colspan="3" style="padding: 10px; text-align: center; color: #999;">Belum ada aktivitas tercatat</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/layouts/footer.php'; ?>
