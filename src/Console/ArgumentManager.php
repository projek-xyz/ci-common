<?php
namespace Projek\CI\Common\Console;

use League\CLImate\Argument\Manager;

class ArgumentManager extends Manager
{
    public function __construct()
    {
        parent::__construct();

        $this->summary = new Summary();
    }
}
