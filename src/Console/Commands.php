<?php
namespace Projek\CI\Common\Console;

use Projek\CI\Common\Console;

abstract class Commands
{
    /**
     * Command name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Command description
     *
     * @var string
     */
    protected $description = null;

    /**
     * Codeigniter instance
     *
     * @var mixed
     */
    protected $CI;

    public function __construct($ci)
    {
        $this->CI =& $ci;
    }

    /**
     * Get command name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get command description
     *
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * Register arguments
     *
     * @param League\Climate\Arguments\Manager
     */
    abstract protected function register(ArgumentManager $arguments);

    /**
     * Execute command
     *
     * @param Projek\CI\Common\Console $console
     * @param League\Climate\Arguments\Manager
     */
    abstract protected function execute(Console $console);

    /**
     * Initialize commands
     *
     * @param  array                 $args    Arguments
     * @param  Projek\CI\Common\Console $console
     * @return mixed
     */
    public function initialize($args, Console $console)
    {
        if (! class_exists('CI_Model')) {
            load_class('Model', 'core');
        }

        $arguments = $console->argumentManager();

        $arguments->description($this->description);
        $this->register($arguments);

        try {
            $arguments->parse($args);
            $executed = $this->execute($console);
        } catch (\Exception $e) {
            $executed = false;
            return EXIT_USER_INPUT;
        }

        if (!$executed or $arguments->defined('help')) {
            return $console->usage($args, $this->name);
        }
    }
}
