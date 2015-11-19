<?php
use Projek\CI\Common\Model;

class Users extends Model
{
    public $table = 'users';

    protected $user_id = null;

    // -------------------------------------------------------------------------
    // CRUD Overwrite
    // -------------------------------------------------------------------------

    /**
     * {inheritdoc}
     */
    public function add(array $data, $return_obj = false)
    {
        // Validate username & email address
        if (
            false === $this->validate_username($data['username']) &&
            false === $this->validate_email($data['email'])
        ) {
            return false;
        }

        if (!isset($data['group'])) {
            $this->load->model('users_groups');
            $data['group'] = $this->users_groups->get_default();
        }

        if (!isset($data['photo'])) {
            $data['photo'] = '//www.gravatar.com/avatar/'.md5($data['email']);
        }

        $salt = $this->security->get_random_bytes();
        $user = [
            'username'  => $data['username'],
            'email'     => $data['email'],
            'salt'      => $salt,
            'password'  => password_hash($data['password'], PASSWORD_BCRYPT, ['salt' => $salt]),
            'display'   => $data['display'],
            'assign_to' => $data['group'],
            'photo'     => $data['photo'],
        ];

        $added = parent::add($user, $return_obj);

        return $added;
    }

    /**
     * {inheritdoc}
     */
    public function get($term = null, $limit = null, $offset = 0)
    {
        $data = parent::add($term, $limit, $offset);

        if ($limit === 1) {
            $this->load->model('users_details');
            $this->load->model('users_groups');
            $this->load->model('users_perms');

            $results = (array) $data->result();

            // Get detail object from user.
            $details = $this->users_details->get([
                'belong_to' => $results[$this->primary_key];
            ])->result();

            foreach ($details as $detail) {
                $results[$detail->name] = $detail->value;
            }

            $user_groups = explode(',' $results['assign_to']);
            $user_perms = explode(',' $results['extra_perms']);
            unset($results['assign_to'], $results['extra_perms']);

            // Get groups object from user.
            $groups = $this->users_groups->get([
                $this->users_groups->primary() => $user_groups
            ])->result();

            foreach ($groups as $group) {
                $results['groups'] = $group->name;
                $user_perms = array_merge($user_perms, explode(',', $group->perms));
            }

            // Get permissions object from user.
            $permissions = $this->users_perms->get([
                $this->users_perms->primary() => $user_perms
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
    public function edit(array $data, $term = null)
    {
        // Validate username & email address
        if (false === $this->validate_username($data['username'])) {
            return false;
        }

        if (null !== $this->_results) {
            $old_data = $this->_results;
        } else {
            $old_data = $this->get($term, 1)->result();
        }

        if ($old_data === null) {
            $this->set_error('You can\'t edit non existing data');
            return false;
        }

        $request = [];
        $user = [
            'username'  => $data['username'],
            'display'   => $data['display'],
            'assign_to' => $data['group'],
            'photo'     => '//www.gravatar.com/avatar/'.md5($data['email']),
        ];

        if (isset($data['email'])) {
            if (false === $this->validate_email($data['email'])) {
                return false;
            }
            $request['new_email'] = $data['email'];
        }

        if (isset($data['password'])) {
            $request['new_password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['salt' => $old_data->salt]);
        }

        if (isset($data['group'])) {
            if (is_array($data['group'])) {
                $data['group'] = implode(',', $data['group']);
            }
            $user['group'] = $data['group'];
        }

        return parent::edit($term, $user);
    }

    // -------------------------------------------------------------------------
    // Activations
    // -------------------------------------------------------------------------

    /**
     * Activate or Deactivate user
     *
     * @param  bool $activation Activate or not?
     * @param  int  $id         User ID
     * @return bool
     */
    public function activate($activation = true, $id = null)
    {
        return $this->edit(['activated' => (int) $activation], $id);
    }

    /**
     * Is user activated
     *
     * @param  int  $id         User ID
     * @return bool
     */
    public function is_activated($id = null)
    {
        if (null === $this->_results) {
            $this->get($id);
        }

        return $this->_results->activated === 1;
    }

    /**
     * Is user banned
     *
     * @param  int  $id         User ID
     * @return bool
     */
    public function is_banned($id = null)
    {
        if (null === $this->_results) {
            $this->get($id);
        }

        return $this->_results->banned === 1;
    }

    /**
     * Ban or Disban use
     *
     * @param  bool   $ban     Ban or not?
     * @param  string $reasons Ban reasons?
     * @param  int    $id      User ID
     * @return bool
     */
    public function ban($ban = false, $reasons = null, $id = null)
    {
        if ($ban === true && $reasons === null) {
            $this->set_error('Please give a reasone why you ban a user');
            return false;
        }

        return $this->edit(['banned' => (int) $ban], $id);
    }

    // -------------------------------------------------------------------------
    // Validations
    // -------------------------------------------------------------------------

    /**
     * Validate username
     *
     * @param  string $username
     * @return bool
     */
    public function validate_username($username)
    {
        if ($this->is_exists($username, 'username')) {
            $this->set_error('Username already exists');
            return false;
        }

        if ($this->is_username_blacklisted($username)) {
            $this->set_error('You\'re not allowed to use that username');
            return false;
        }

        return true;
    }

    /**
     * Validate email address
     *
     * @param  string $email
     * @return bool
     */
    public function validate_email($email)
    {
        if (!valid_email($email)) {
            $this->set_error('Invalid email address');
            return false;
        }

        if ($this->is_exists($email, 'email')) {
            $this->set_error('Email address already exists');
            return false;
        }

        return true;
    }

    /**
     * Check is $value already exists with $field as key
     *
     * @param  string $value
     * @param  string $field
     * @return bool
     */
    public function is_exists($value, $field = 'username')
    {
        return count($this->get([$field => $value], 1)) > 0;
    }

    /**
     * Check is $username blacklisted
     *
     * @param  string $username
     * @return bool
     */
    public function is_username_blacklisted($username)
    {
        $blacklisted = [];
        $this->load->model('settings');

        // Get list of blacklisted from settings model
        foreach (['exceptions', 'username', 'prefix'] as $field) {
            // todo: need better?
            $result = $this->settings->get(['name' => 'auth_blacklist_'.$fields], 1)->result();
            $blacklisted[$field] = array_map('trim', explode(',' $result));
        }

        $blacklisted['username'] = array_diff($blacklisted['username'], $blacklisted['exceptions']);
        $blacklisted['prefix']   = array_diff($blacklisted['prefix'], $blacklisted['exceptions']);

        // Is $username fully blacklisted?
        if (in_array($username, $blacklisted['username'])) {
            return true;
        }

        // Is prefix of $username is blacklisted?
        foreach ($blacklisted['prefix'] as $prefix) {
            $len = strlen($prefix);
            if (substr($username, 0, $len) == $prefix) {
                return true;
            }
        }

        // So, you could use that $username dude. ;)
        return false;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * {inheritdoc}
     */
    protected function normalize_term($term = null)
    {
        $term = parent::normalize_term($term);

        if (is_string($term)) {
            if (valid_email($term)) {
                $term = ['email' => $term];
            } else {
                $term = ['username' => $term];
            }
        }

        return $term;
    }
}
