<?php
use Projek\CI\Common\Controller;

class Auth extends Controller
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
        // $this->verify_logged_in();

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
            $this->auths->redirect('login');
        }

        $this->load->view('welcome', $this->data);
    }

    public function reset($email_key = null, $user_id = null)
    {
        if (is_null($email_key)) {
            $this->auths->redirect('login');
        }

        $this->load->view('welcome', $this->data);
    }

    /**
     * Logout current user
     *
     * @return void
     */
    public function logout()
    {
        // Clean up user data
        $this->auths->logout();

        // Back to login page
        $this->auths->redirect('login');
    }
}
