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
        $this->views->add_data(['page_name' => 'Aplikasi']);
        $this->views->add_data(['path_name' => '']);

        if ($this->load->config('common/lang_codes', true, true)) {
            $codes = $this->config->item('common/lang_codes', 'lang_codes');
            $lang = $this->config->item('language');

            $code = array_search($lang, $codes) ?: 'en';
            $this->views->add_data(['lang' => $code]);
        } else {
            $this->views->add_data(['lang' => 'en']);
        }

        $charset = strtolower($this->config->item('charset'));
        $this->views->add_data(['charset' => $charset]);
    }
}
