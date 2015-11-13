<?php
namespace Projek\CI\Common\Console\Extensions;

use League\CLImate\TerminalObject\Basic\Tab as BasicTab;

class TabExtension extends BasicTab
{
    /**
     * {inheritdoc}
     */
    public function result()
    {
        return str_repeat('  ', $this->count);
    }
}
