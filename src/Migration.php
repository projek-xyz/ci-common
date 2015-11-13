<?php
namespace Projek\CI\Common;

use CI_Migration;
use Projek\CI\Common\Model;

class Migration extends CI_Migration
{
    const DB_SCHEMAS = 'schema';
    const DB_DATA    = 'data';

    const FLAG_OVERRIDE      = 'override_table';
    const FLAG_ALTERTABLE    = 'alter_table';
    const FLAG_DESTRUCTIVE   = Model::FLAG_DESTRUCTIVE;
    const FLAG_AUTOTIMESTAMP = Model::FLAG_AUTOTIMESTAMP;

    private $_auto_timestamp = FALSE;

    // abstract public function tables();

    public function up()
    {
        $this->load->helper('date');
        $is_datetime = strtolower(Model::TIMESTAMP_FORMAT) == 'datetime';

        foreach ($this->tables() as $name => $prop) {
            $keys = [];
            $is_auto_timestamp = element(self::FLAG_AUTOTIMESTAMP, $prop, true);

            if ($schemas = element(self::DB_SCHEMAS, $prop, [])) {
                if (true === $is_auto_timestamp) {
                    $this->_auto_timestamp = true;
                    $creator = ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => null];

                    if ($is_datetime) {
                        $timestamp = ['type' => 'datetime', 'default' => '0000-00-00 00:00:00'];
                    } else {
                        $timestamp = ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0];
                    }

                    $schemas[Model::CREATION_KEY.'_by']     = $creator;
                    $schemas[Model::CREATION_KEY.'_at']     = $timestamp;
                    $schemas[Model::MODIFICATION_KEY.'_by'] = $creator;
                    $schemas[Model::MODIFICATION_KEY.'_at'] = $timestamp;
                }

                if (false === element(self::FLAG_DESTRUCTIVE, $prop, false)) {
                    $schemas[Model::DELETION_KEY] = ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'DEFAULT' => 0];
                }

                foreach ($schemas as $column => $schema) {
                    if (isset($schema['key'])) {
                        $keys[$column] = $schema['key'];
                        unset($schema['key']);
                    }
                }

                if (true === element(self::FLAG_OVERRIDE, $prop, true)) {
                    $this->dbforge->drop_table($name, true);
                }

                $this->dbforge->add_field($schemas);

                if (! empty($keys)) {
                    foreach ($keys as $key => $value) {
                        $this->dbforge->add_key($key, $value);
                    }
                }

                if (false === element(self::FLAG_ALTERTABLE, $prop, false)) {
                    if ($this->dbforge->create_table($name, true, ['engine' => 'InnoDB'])) {
                        log_message('info', 'Migration successfully create table '.$name);
                    } else {
                        log_message('error', 'Migration failed to create table '.$name);
                    }
                }
            }

            if ($data = element(self::DB_DATA, $prop, [])) {
                if (true === $is_auto_timestamp) {
                    $now = $is_datetime ? date('Y-m-d H:i:s') : time();

                    for ($d = 0; $d < count($data); $d++) {
                        $data[$d] += [
                            Model::CREATION_KEY.'_by'     => null,
                            Model::CREATION_KEY.'_at'     => $now,
                            Model::MODIFICATION_KEY.'_by' => null,
                            Model::MODIFICATION_KEY.'_at' => $now,
                        ];
                    }
                }

                if ($this->db->insert_batch($name, $data)) {
                    log_message('info', 'Migration successfully insert data to table '.$name);
                }
            }
        }
    }

    public function down()
    {
        foreach ($this->tables() as $column => $structure) {
            $this->dbforge->drop_table($column, true);
        }
    }

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
