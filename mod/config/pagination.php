<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Continents
|--------------------------------------------------------------------------
*/
$config['pagination'] = [
    'uri_segment'          => 3,
    'num_links'            => 5,
    'use_page_numbers'     => TRUE,
    'page_query_string'    => TRUE,
    'reuse_query_string'   => TRUE,
    'query_string_segment' => 'page',
    'full_tag_open'        => '<ul class="pagination pull-right">',
    'full_tag_close'       => '</ul>',
    'first_tag_open'       => '<li>',
    'first_tag_close'      => '</li>',
    'last_tag_open'        => '<li>',
    'last_tag_close'       => '</li>',
    'next_tag_open'        => '<li>',
    'next_tag_close'       => '</li>',
    'prev_tag_open'        => '<li>',
    'prev_tag_close'       => '</li>',
    'cur_tag_open'         => '<li class="active"><span>',
    'cur_tag_close'        => '</span></li>',
    'num_tag_open'         => '<li>',
    'num_tag_close'        => '</li>',
];
