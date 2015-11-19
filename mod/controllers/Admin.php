<?php
use Projek\CI\Common\Controller\PrivateController;

class Admin extends PrivateController
{
    public function index()
    {
        dd($this->auths->get_current('status'));

        $this->load->view('welcome', []);
    }
}
