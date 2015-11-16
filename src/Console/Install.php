<?php
namespace Projek\CI\Common\Console;

use Projek\CI\Console\Cli;
use Projek\CI\Console\Commands;
use Projek\CI\Console\Arguments\Manager;

class Install extends Commands
{
    protected $name = 'install';
    protected $description = 'lang:console_install_desc';

    /**
     * {inheridoc}
     */
    public function register(Manager $arguments)
    {
        $arguments->add([
            'help' => [
                'prefix' => 'h',
                'longPrefix' => 'help',
                'description' => Cli::lang('console_display_help'),
                'noValue' => true
            ]
        ]);
    }

    /**
     * {inheridoc}
     */
    public function execute(Cli $console)
    {
        $this->CI->load->library('migration');

        if ( ! $this->CI->migration->latest()) {
            show_error($this->CI->migration->error_string());
        } else {
            $console->out('<green>Congratulation! everything is ready to go</green>');
        }

        return false;
    }
}
