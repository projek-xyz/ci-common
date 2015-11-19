<?php
use Projek\CI\Common\Controller\PublicController;

class Auth extends PublicController
{
    public function index()
    {
        $this->login();
    }

    public function login()
    {
        $this->verify_logged_in();

        $this->load->view('welcome', $this->data);
    }

    public function register()
    {
        $this->verify_logged_in();

        $this->load->view('welcome', $this->data);
    }

    public function resend()
    {
        $this->verify_logged_in();

        $this->load->view('welcome', $this->data);
    }

    public function forgot()
    {
        $this->verify_login();

        $this->load->view('welcome', $this->data);
    }

    public function activate($email_key = null, $user_id = null)
    {
        if (is_null($email_key)) {
            $this->redirect_to('login');
        }

        $this->load->view('welcome', $this->data);
    }

    public function reset($email_key = null, $user_id = null)
    {
        if (is_null($email_key)) {
            $this->redirect_to('login');
        }

        $this->load->view('welcome', $this->data);
    }

    public function logout()
    {
        $this->auths->logout();
    }
}
