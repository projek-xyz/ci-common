<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Projek\CI\Common\Library;

class Auths extends Library
{
    /**
     * Basic configurations
     *
     * @var array
     */
    protected $configs = [];

    /**
     * class constructor
     */
    public function __construct()
    {
        $this->load->language('auths');
        $this->load->helper('auths');

        $this->load->library('session');

        // Initializing configurations
        $this->initialize();

        if (!$this->is_logged_in()) {
            $this->validate_autologin();
        }

        log_message('debug', 'Authentication library initiated');
    }

    public function initialize()
    {
        $configs = [];

        if ($this->load->config('common/auths', true, true)) {
            $configs = $this->config->item('common/auths');
        }

        if ($this->load->config('auths', true, true)) {
            $configs = array_merge($configs, $this->config->item('auths'));
        }

        foreach ($configs as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    $configs[$name.'.'.$key] = $val;
                }
                unset($configs[$name]);
            }
        }

        $this->configs = $configs;
    }

    public function config($name)
    {
        return $this->configs[$name];
    }

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    /**
     * Register new user
     *
     * @return bool
     */
    public function register($username, $email, $password)
    {
        $this->load->model('users');
        $new_user = $this->users->add([
            'email'    => $email,
            'username' => $username,
            'display'  => $username,
            'password' => $password,
        ], true);

        if (!$user) {
            return false;
        }

        $this->load->model('users_details');

        $details = $this->users_details->add([
            'fullname' => $username,
            '_request' => serialize(['activation' => $this->token()]),
        ]);
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    /**
     * Create new user session
     *
     * @return bool
     */
    public function login($username, $password, $remember = false)
    {
        $this->load->model('users');

        if (!$user = $this->users->get($username)) {
            $this->set_error('Login incorrect');
            return false;
        }

        $user_obj = $user->result();

        if (!$user->is_activated()) {
            $loggin_attempts = $user_obj->loggin_attempts++;
            $user->edit(['loggin_attempts' => $loggin_attempts]);

            $this->session->set_userdata([
                'username' => $user_obj->username,
                'status'   => false,
            ]);

            $this->set_error('Your account is not activated yet.');
            return false;
        }

        if (!$user->is_banned()) {
            $loggin_attempts = $user_obj->loggin_attempts++;
            $user->edit(['loggin_attempts' => $loggin_attempts]);

            $this->session->set_userdata([
                'username' => $user_obj->username,
                'status'   => false,
            ]);

            $this->set_error('Your account is banned with reason '.$user_obj->ban_reason);
            return false;
        }

        if (!password_verify($password, $user_obj->password)) {
            $this->set_error('Login incorrect');
            return false;
        }

        $user->edit(['loggin_attempts' => 0]);

        if ($remember === true) {
            $this->set_autologin();
        }

        $this->session->set_userdata([
            'user_id'     => $user_obj->{$user->primary()},
            'username'    => $user_obj->username,
            'display'     => $user_obj->display,
            'permissions' => $user_obj->permissions
        ]);

        return true;
    }

    /**
     * Cleanup user session
     *
     * @return bool
     */
    public function logout()
    {
        delete_cookie('autologin');
        // See http://codeigniter.com/forums/viewreply/662369/ as the reason for the next line
        $this->session->set_userdata([
            'user_id'     => null,
            'username'    => null,
            'display'     => null,
            'status'      => null,
            'permissions' => []
        ]);

        $this->session->sess_destroy();

        redirect($this->config('routes.login'));
    }

    /**
     * Validatate user credential
     *
     * @return bool
     */
    public function is_logged_in()
    {
        return (bool) $this->session->userdata('status') === true;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Generate random 16 chars of string
     *
     * @return string
     */
    protected function random_strings()
    {
        $cookie_name = $this->config->item('sess_cookie_name');
        return substr(md5(uniqid(mt_rand().$this->input->cookie($cookie_name))), 0, 16);
    }

    // -------------------------------------------------------------------------
    // Autologin
    // -------------------------------------------------------------------------

    /**
     * Logging in user automaticaly when their cookies is valid
     *
     * @param string $value
     */
    public function set_autologin($value)
    {
        $this->input->set_cookie('autologin', $value);
    }

    /**
     * Retrieve autologin data
     *
     * @return array
     */
    public function get_autologin()
    {
        $cookies = $this->input->cookie('autologin', true);

        if ($cookies) {
            return unserialize($cookies);
        }
    }

    /**
     * Validate autologin data
     *
     * @return void
     */
    public function validate_autologin()
    {
        return;
    }

    /**
     * Is current user allowed to $permission
     *
     * @param  string $permission
     * @return bool
     */
    public function user_can($permission)
    {
        return in_array($permission, $this->get_current('permissions'));
    }

    /**
     * Get current logged in user data
     *
     * @param  string $key Data key
     * @return mixed
     */
    public function get_current($key = null)
    {
        if (!$this->is_logged_in()) {
            return false;
        }

        $user_data = $this->session->all_userdata();

        if (!is_null($key) and isset($user_data[$key]))
        {
            return $user_data[$key];
        }

        return $user_data;
    }
}
