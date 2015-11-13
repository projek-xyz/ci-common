<?php
namespace Projek\CI\Common\Controller;

use Projek\CI\Common\Console;
use Projek\CI\Common\Controller;

class CLI extends Controller
{
    private $_app_cli_config = [];

    public function __construct()
    {
        is_cli() or die('This class should be called via CLI only');

        parent::__construct();

        if ($this->load->config('cli', true, true)) {
            $this->_app_cli_config = $this->config->item('cli');
        }
    }

    public function index()
    {
        $args = func_get_args();
        $console = new Console($this->_app_cli_config);

        return $console->execute($args);
    }
}
