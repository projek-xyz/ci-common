<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['console_install_desc'] = 'Menjalankan proses installasi';
$lang['console_install_done'] = 'Selamat! Instalasi berhasil dan semua sudah siap digunakan';

$lang['console_install_interactive']    = 'Anda menjalankan program ini di non-instactive shell.';
$lang['console_install_manualy']        = 'Silahkan jalankan perintah "./app/cli install" secara manual.';
$lang['console_install_env_already']    = 'Anda sudah memiliki .env didalam direktori \'app/\'.';
$lang['console_install_env_ready']      = '.env baru telah dibuat.';
$lang['console_install_setup_intro']    = 'Kita perlu membuat konfigurasi utama.';
$lang['console_install_setup_appurl']   = '     Basis url aplikasi: ';
$lang['console_install_setup_dbhost']   = '      Hostname Database: ';
$lang['console_install_setup_dbuser']   = '      Username Database: ';
$lang['console_install_setup_dbpass']   = '      Password Database: ';
$lang['console_install_setup_dbname']   = '          Nama Database: ';
$lang['console_install_setup_dbpref']   = '  Awalan Tabel Database: ';
$lang['console_install_setup_server']   = '     Aplikasi server: ';
$lang['console_install_heroku_env']     = 'Environments Heroku terdeteksi';
$lang['console_install_heroku_already'] = 'Anda sudah memilki file Procfile di root direktori';
$lang['console_install_heroku_ready']   = 'Heroku Procfile baru telah dibuat';
$lang['console_install_apache_already'] = 'Anda sudah memiliki .htaccess didalam direktori \'public/\'';
$lang['console_install_apache_ready']   = '.htaccess baru telah dibuat didalam direktori \'public/\'';
$lang['console_install_nginx_ready']    = 'Saat ini kami tidak memiliki pre-install konfigurasi untuk nginx';

$lang['console_migration_desc'] = 'Menata migrasi database';
$lang['console_migration_arg_list'] = 'Menampilkan semua daftar migrasi';
$lang['console_migration_arg_current'] = 'Menampilkan versi migrasi saat ini';
$lang['console_migration_arg_to'] = 'Migrasi ke versi tertentu. Lihat --list untuk versi tersedia.';

$lang['console_migration_label_version'] = 'Versi';
$lang['console_migration_label_filename'] = 'Nama file';
$lang['console_migration_label_latest'] = 'Anda sudah ada dalam versi terakhir. Yaitu: %s';
$lang['console_migration_label_installed'] = 'Versi yang telah anda install: %s';
$lang['console_migration_label_help'] = 'Gunakan --help untuk info lebih lanjut';
$lang['console_migration_label_migrated'] = 'Selesai! telah bermigrasi ke versi %s';
