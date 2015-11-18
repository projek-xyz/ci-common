<?php
use Projek\CI\Common\Model;

class Users_perms extends Model
{
    public $table = 'users_perms';

    protected $auto_timestamp = false;

    /**
     * {inheritdoc}
     */
    public function result()
    {
        if (isset($this->_results->desc)) {
            $this->_results->desc = $this->translate($this->_results->desc);
        } else {
            foreach ($this->_results as &$row) {
                $row->desc = $this->translate($row->desc);
            }
        }

        return $this->_results;
    }

    /**
     * Try to translate value
     *
     * @param  string $string Language key
     * @return string
     */
    protected function translate($string) {
        if (substr($string, 0, 5) === 'lang:') {
            $string = substr($string, 5);
            $string = $this->lang->line($string);
        }

        return $string;
    }
}
