<?php
namespace Projek\CI\Common\Console;

use Projek\CI\Console\Cli;
use Projek\CI\Console\Commands;
use Projek\CI\Console\Arguments\Manager;

class Migration extends Commands
{
    protected $name = 'migration';
    protected $description = 'lang:console_migration_desc';

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
            ],
            'list' => [
                'prefix' => 'l',
                'longPrefix' => 'list',
                'description' => Cli::lang('console_migration_arg_list'),
                'noValue' => true
            ],
            'current' => [
                'prefix' => 'c',
                'longPrefix' => 'current',
                'description' => Cli::lang('console_migration_arg_current'),
                'noValue' => true
            ],
            'to' => [
                'prefix' => 't',
                'longPrefix' => 'to',
                'description' => Cli::lang('console_migration_arg_to'),
                'castTo' => 'int'
            ]
        ]);
    }

    /**
     * {inheridoc}
     */
    public function execute(Cli $console)
    {
        $this->CI->load->library('migration');
        $arguments = $console->argument_manager();

        if ($arguments->defined('list')) {
            $this->get_current($console);
            if ($table = $this->get_list()) {
                return $console->table($table);
            }

            return $console->dump($this->get_list());
        }

        if ($arguments->defined('current')) {
            return $this->get_current($console);
        }

        if ($arguments->defined('to')) {
            $version = $arguments->get('to');

            return $this->jump_to($version, $console);
        }

        return false;
    }

    protected function get_list()
    {
        $table = [];
        $migrations = $this->CI->migration->find_migrations();

        foreach ($migrations as $version => $file) {
            $file = explode('_', basename($file, '.php'));
            array_shift($file);
            $table[] = [
                Cli::lang('console_migration_label_version') => $version,
                Cli::lang('console_migration_label_filename') => implode(' ', $file),
            ];
        }

        return $table;
    }

    protected function get_current($console)
    {
        $current = $this->CI->migration->get_version();

        if ($this->is_latest()) {
            return $this->print_latest();
        }

        $console->out(
            sprintf(Cli::lang('console_migration_label_installed'), '<green>'.$current.'</green>')
        );
        return $console->out(Cli::lang('console_migration_label_help'));
    }

    protected function jump_to($version = 0, $console)
    {
        if ($this->is_latest()) {
            return $this->print_latest();
        }

        if (!$version) {
            $version = $this->CI->migration->latest();
        }

        $this->CI->migration->version($version);

        return $console->out(
            sprintf(Cli::lang('console_migration_label_migrated'), '<green>'.$version.'</green>')
        );;
    }

    protected function is_latest()
    {
        $current = $this->CI->migration->get_version();
        $latest  = $this->CI->migration->get_count();

        return ($current == $latest);
    }

    private function print_latest($console)
    {
        $console->out('<green>'.Cli::lang('console_migration_label_latest').'</green>');
        return $console->out(
            sprintf(Cli::lang('console_migration_label_which'), '<green>'.$current.'</green>')
        );
    }
}
