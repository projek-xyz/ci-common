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
        $this->load->helper(['url', 'html']);

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
        $this->views->add_data([
            'page_name' => 'Aplikasi',
            'path_name' => '/',
        ]);

        if ($this->load->config('common/lang_codes', true, true)) {
            $codes = $this->config->item('common/lang_codes', 'lang_codes');
            $lang = $this->config->item('language');

            $code = array_search($lang, $codes) ?: 'en';
        } else {
            $code = 'en';
        }

        $this->views->add_data([
            'lang' => $code,
            'charset' => strtolower($this->config->item('charset')),
        ]);
    }

    /**
     * Verify login credentials
     *
     * @return void
     */
    protected function verify_login()
    {
        if (! $this->auths->is_logged_in()) {
            $this->auths->redirect('login');
        } elseif (! $this->auths->is_logged_in(false)) {
            $this->auths->redirect('resend');
        }
    }

    /**
     * Verify login credentials
     *
     * @return void
     */
    protected function verify_logged_in()
    {
        if ($this->auths->is_logged_in()) {
            $this->auths->redirect('dashboard');
        } elseif (! $this->auths->is_logged_in(false)) {
            $this->auths->redirect('resend');
        }
    }
}
