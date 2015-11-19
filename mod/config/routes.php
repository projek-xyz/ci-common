<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['assets'] = 'common/assets';

$route['admin'] = 'common/admin';

$route['login']    = 'common/auth/login';
$route['register'] = 'common/auth/register';
$route['resend']   = 'common/auth/resend';
$route['forgot']   = 'common/auth/forgot';
$route['activate'] = 'common/auth/activate';
$route['reset']    = 'common/auth/reset';
$route['logout']   = 'common/auth/logout';
