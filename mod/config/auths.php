<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setup default routes for authentication library.
 */
$config['routes'] = [
    'dashboard' => 'admin',
    'login'     => 'login',
    'logout'    => 'logout',
    'register'  => 'register',
    'activate'  => 'activate',
    'resend'    => 'resend',
    'forgot'    => 'forgot',
    'reset'     => 'reset',
];

/**
 * Autologin cookie name
 */
$config['autologin_cookie_name'] = 'auth_autologin';
