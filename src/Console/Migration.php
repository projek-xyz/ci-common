<?php
namespace Projek\CI\Common\Console;

use Projek\CI\Console\Cli;
use Projek\CI\Console\Commands;

class Migration extends Commands
{
    protected $name = 'migration';
    protected $description = 'lang:console_migration_desc';

    /**
     * {inheridoc}
     */
    public function register(Cli $command)
    {
        $command->add_arg([
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
    public function execute(Cli $command)
    {
        $this->CI->load->library('migration');

        if ($command->get_arg('list')) {
            $this->get_current($command);
            if ($table = $this->get_list()) {
                return $command->table($table);
            }
            return $command->dump($this->get_list());
        }

        if ($command->get_arg('current')) {
            return $this->get_current($command);
        }

        if ($command->get_arg('to')) {
            $version = $command->get('to');
            return $this->jump_to($version, $command);
        }

        return false;
    }

    /**
     * Get migration list
     *
     * @return array
     */
    protected function get_list()
    {
        $table = [];
        $migrations = $this->CI->migration->find_migrations();
        $type = config_item('migration_type');

        if ($type === null) {
            @include APPPATH.'config/migration.php';
            $type = isset($config['migration_type']) ? $config['migration_type'] : 'sequential';
        }

        $use_timestamp = $this->CI->config->item('migration_type') == 'timestamp' ? true : false;

        foreach ($migrations as $version => $file) {
            $file = explode('_', basename($file, '.php'));
            array_shift($file);

            if ($type == 'timestamp') {
                $version = date('d-m-Y h:m:s', strtotime($version));
            }

            $table[] = [
                Cli::lang('console_migration_label_version') => $version ,
                Cli::lang('console_migration_label_filename') => implode(' ', $file),
            ];
        }

        return $table;
    }

    /**
     * Get current migration version
     *
     * @param  \Projek\CI\Console\Cli $command
     * @return \Projek\CI\Console\Cli
     */
    protected function get_current(Cli $command)
    {
        $current = $this->CI->migration->get_version();

        if ($this->is_latest()) {
            return $this->print_latest($current, $command);
        }

        $command->out(
            sprintf(Cli::lang('console_migration_label_installed'), '<green>'.$current.'</green>')
        );
        return $command->out(Cli::lang('console_migration_label_help'));
    }

    /**
     * Jump to certain version
     *
     * @param  int                    $version Migration version
     * @param  \Projek\CI\Console\Cli $command
     * @return \Projek\CI\Console\Cli
     */
    protected function jump_to($version = 0, $command)
    {
        if ($this->is_latest()) {
            return $this->print_latest($current, $command);
        }

        if (!$version) {
            $version = $this->CI->migration->latest();
        }

        $this->CI->migration->version($version);

        return $command->out(
            sprintf(Cli::lang('console_migration_label_migrated'), '<green>'.$version.'</green>')
        );;
    }

    /**
     * Is migration in latest version
     *
     * @return bool
     */
    protected function is_latest()
    {
        $current = $this->CI->migration->get_version();
        $latest  = $this->CI->migration->get_count();
        return ($current == $latest);
    }

    /**
     * Print the latest one
     *
     * @param  int                    $current Current version
     * @param  \Projek\CI\Console\Cli $command
     * @return \Projek\CI\Console\Cli
     */
    private function print_latest($current, $command)
    {
        return $command->out(
            sprintf(Cli::lang('console_migration_label_latest'), '<green>'.$current.'</green>')
        );
    }
}
