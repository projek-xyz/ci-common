<?php
use Projek\CI\Common\Controller\PrivateController;

class Admin extends PrivateController
{
    public function index()
    {
        $this->load->view('welcome', []);
    }
}
