<?php
namespace Projek\CI\Common\Controller;

use Projek\CI\Common\Controller;

class PrivateController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->auths->is_logged_in()) {
            $this->redirect_to('login');
        }
    }

    /**
     * Shortcut to redirect url based on Auths config.routes
     *
     * @param  string $route
     * @return void
     */
    protected function redirect_to($route)
    {
        $route = $this->auths->config('$routes.'.$route);
        redirect($route);
    }

    /**
     * Verify login status
     *
     * @return void
     */
    protected function verify_login()
    {
        if (! $this->auths->is_logged_in()) {
            $this->redirect_to('login');
        } elseif (! $this->auths->is_logged_in(false)) {
            $this->redirect_to('resend');
        }
    }

    /**
     * Verify login status
     *
     * @return void
     */
    protected function verify_logged_in()
    {
        if ($this->auths->is_logged_in()) {
            $this->redirect_to('dashboard');
        } elseif (! $this->auths->is_logged_in(false)) {
            $this->redirect_to('resend');
        }
    }
}
