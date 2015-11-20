<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['console_install_desc'] = 'Run installation process';
$lang['console_install_done'] = 'Congratulation! Installation succesful and everything is ready to go';

$lang['console_install_interactive']    = 'You run this app on non-instactive shell.';
$lang['console_install_manualy']        = 'Please run "./app/cli install" manualy.';
$lang['console_install_env_already']    = 'You already have .env in your \'app/\' directory.';
$lang['console_install_env_ready']      = 'New .env generated';
$lang['console_install_setup_intro']    = 'We need to create the main configuration.';
$lang['console_install_setup_appurl']   = '   Application base url: ';
$lang['console_install_setup_dbhost']   = '      Database Hostname: ';
$lang['console_install_setup_dbuser']   = '      Database Username: ';
$lang['console_install_setup_dbpass']   = '      Database Password: ';
$lang['console_install_setup_dbname']   = '          Database Name: ';
$lang['console_install_setup_dbpref']   = '  Database Table Prefix: ';
$lang['console_install_setup_server']   = '     Server Application: ';
$lang['console_install_heroku_env']     = 'Heroku environments detected';
$lang['console_install_heroku_already'] = 'Heroku Procfile already generated';
$lang['console_install_heroku_ready']   = 'New Heroku Procfile generated';
$lang['console_install_apache_already'] = 'You already have .htaccess in your \'public/\' directory';
$lang['console_install_apache_ready']   = 'New .htaccess generated in your \'public/\' directory';
$lang['console_install_nginx_ready']    = 'Currently we have no pre-installed config for nginx';

$lang['console_migration_desc'] = 'Manage database migration';
$lang['console_migration_arg_list'] = 'Display All migraiton list';
$lang['console_migration_arg_current'] = 'Display current migraiton version';
$lang['console_migration_arg_to'] = 'Migrate to certain version. See --list for available versions.';

$lang['console_migration_label_version'] = 'Version';
$lang['console_migration_label_filename'] = 'File name';
$lang['console_migration_label_latest'] = 'You are already in latest version. Which is: %s';
$lang['console_migration_label_installed'] = 'You have installed version: %s';
$lang['console_migration_label_help'] = 'Use --help for more information';
$lang['console_migration_label_migrated'] = 'Done! migrated to version %s';
