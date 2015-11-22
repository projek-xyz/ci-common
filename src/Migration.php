<?php
namespace Projek\CI\Common;

use CI_Migration;
use Projek\CI\Common\Model;

class Migration extends CI_Migration
{
    public function get_path()
    {
        return $this->_migration_path;
    }

    public function get_version()
    {
        return $this->_get_version();
    }

    public function get_count()
    {
        return count($this->find_migrations());
    }
}
