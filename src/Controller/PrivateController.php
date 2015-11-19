<?php
namespace Projek\CI\Common\Controller;

use Projek\CI\Common\Controller;

class PrivateController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Verify login credentials
        $this->verify_login();
    }
}
