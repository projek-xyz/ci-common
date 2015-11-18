<?php
namespace Projek\CI\Common\Controller;

use Projek\CI\Common\Controller;

class Private extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('common/auths');
    }
}
