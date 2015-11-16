<?php
use Projek\CI\Common\Console;
use Projek\CI\Console\Controller;

class Cli extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->available_commands = [
            Console\Install::class,
            Console\Migration::class,
        ];

        foreach ($this->config->item('modules_available') as $name => $mod) {
            if ($this->load->config($name.'/console', true, true)) {
                $new_commands = $this->config->item($name.'/console', 'available_commands');
                $this->available_commands = array_merge($this->available_commands, $new_commands);
            }
        }
    }
}
