<?php
$file_test = 'views/setting/index.php';
require_once 'config/config.php';

$page = $_GET['page'] ?? 'login';
$auth = new User();

if ($page != 'login' && $page != 'register' && !$auth->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

switch ($page) {
    case 'login': include 'views/login.php'; break;
    case 'register': include 'views/register.php'; break;
    case 'dashboard': include 'views/dashboard.php'; break;

    case 'proses_catatan':
        $catatanModel = new Catatan();
        $user_id = Session::get('user_id');
        $action = $_GET['action'] ?? '';

        if ($action == 'tambah' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $isi = trim($_POST['isi_catatan']);
            if (!empty($isi)) {
                $catatanModel->simpan($user_id, $isi);
            }
        } 
        
        if ($action == 'hapus') {
            $id_del = $_GET['id'] ?? 0;
            $catatanModel->hapus($id_del, $user_id);
        }

        header('Location: index.php?page=dashboard');
        exit;
        break;

    case 'usaha': $obj = new Usaha(); include 'views/usaha/index.php'; break;
    case 'departemen': $obj = new Departemen(); include 'views/departemen/index.php'; break;
    case 'jabatan': $obj = new Jabatan(); include 'views/jabatan/index.php'; break;
    case 'pegawai': $obj = new Pegawai(); include 'views/pegawai/index.php'; break;

    case 'cuti': $obj = new Cuti(); include 'views/cuti/index.php'; break;
    case 'izin': $obj = new Izin(); include 'views/izin/index.php'; break;
    case 'peringatan': $obj = new Peringatan(); include 'views/peringatan/index.php'; break;
    case 'penghargaan': $obj = new Penghargaan(); include 'views/penghargaan/index.php'; break;

    case 'report_pegawai': include 'views/report/print_pegawai.php'; break;
    case 'report_sp': include 'views/report/print_sp.php'; break;
    case 'report_cuti': include 'views/report/print_cuti.php'; break;
    case 'report_izin': include 'views/report/print_izin.php'; break;
    case 'report_penghargaan': include 'views/report/print_penghargaan.php'; break;

    case 'setting': include "views/setting/index.php"; break;
        
    case 'logout': 
        $auth->logout(); 
        header('Location: index.php?page=login'); 
        break;

    default: include 'views/404.php';
}
?>