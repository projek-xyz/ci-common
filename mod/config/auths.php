<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setup default routes for authentication library.
 */
$config['routes'] = [
    'dashboard' => '',
    'login'     => 'common/login',
    'logout'    => 'common/logout',
    'register'  => 'common/register',
    'activate'  => 'common/activate',
    'resend'    => 'common/resend',
    'forgot'    => 'common/forgot',
    'reset'     => 'common/reset',
];

/**
 * Autologin cookie name
 */
$config['autologin_cookie_name'] = 'auth_autologin';
