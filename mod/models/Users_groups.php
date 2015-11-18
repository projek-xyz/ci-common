<?php
use Projek\CI\Common\Model;

class Users_groups extends Model
{
    public $table = 'users_groups';

    public function get_default()
    {
        return $this->get(['default' => 1], 1)->result()->id;
    }

    /**
     * {inheritdoc}
     */
    public function get($term = null, $limit = null, $offset = 0)
    {
        $data = parent::add($term, $limit, $offset);

        if ($limit === 1) {
            $this->load->model('users_perms');
            $results = (array) $data->result();

            $group_perms = explode(',' $results['perms']);
            unset($results['perms']);

            // Get permissions object from user.
            $permissions = $this->users_perms->get([
                $this->users_perms->primary() => $group_perms
            ])->result();

            foreach ($permissions as $permission) {
                $results['permissions'] = $permission->name;
            }

            $this->user_id = $results[$this->primary_key];
            $this->_results = (object) $results;
        }

        return $this;
    }

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
