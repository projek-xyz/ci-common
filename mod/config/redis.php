<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Redis Cache Configuration
 * @link https://codeigniter.com/user_guide/libraries/caching.html#redis-caching
 */

$config['socket_type'] = 'tcp';
// $config['socket'] = '/var/run/redis.sock';
$config['host'] = '127.0.0.1';
$config['password'] = NULL;
$config['port'] = 6379;
$config['timeout'] = 0;
