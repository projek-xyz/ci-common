<?php
namespace Projek\CI\Common;

use CI_Controller;

class Controller extends CI_Controller
{
    protected $data = [];

    public function __construct()
    {
        parent::__construct();

        // Load common used language
        // $this->load->language('common/app_common');

        // Load common used helpers
        $this->load->helper('url', 'html');

        if (!is_cli()) {
            // Load libraries & drivers
            $this->load->library('common/auths');

            // Use Redis cache
            $this->load->driver('cache', [
                'adapter'    => 'redis',
                'backup'     => 'file',
                'key_prefix' => 'creasi_'
            ]);
        }

        // Set default data keys
        $this->data['page_name'] = '';
        $this->data['path_name'] = '';

        if ($this->load->config('common/lang_codes', true, true)) {
            $codes = $this->config->item('common/lang_codes', 'lang_codes');
            $lang = $this->config->item('language');

            if (($code = array_search($lang, $code)) !== false) {
                $this->data['lang'] = $code;
            } else {
                $this->data['lang'] = 'en';
            }
        } else {
            $this->data['lang'] = 'en';
        }

        $this->data['charset'] = $this->config->item('charset');
    }
}
