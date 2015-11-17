<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Auths_install extends APP_Migration
{
    private $settings_table = [
        self::FLAG_AUTOTIMESTAMP => false,
        self::FLAG_DESTRUCTIVE => true,
        self::DB_DATA => [
            ['belong_to' => 0, 'name' => 'auth_username_length',         'value' => '4-20'],
            ['belong_to' => 0, 'name' => 'auth_password_length',         'value' => '4-20'],
            ['belong_to' => 0, 'name' => 'auth_registration_public',     'value' => '1'],
            ['belong_to' => 0, 'name' => 'auth_registration_captcha',    'value' => '1'],
            ['belong_to' => 0, 'name' => 'auth_registration_activation', 'value' => '0'],
            ['belong_to' => 0, 'name' => 'auth_login_by_email',          'value' => '1'],
            ['belong_to' => 0, 'name' => 'auth_login_by_username',       'value' => '1'],
            ['belong_to' => 0, 'name' => 'auth_login_record_ip',         'value' => '1'],
            ['belong_to' => 0, 'name' => 'auth_login_attempts_count',    'value' => '1'],
            ['belong_to' => 0, 'name' => 'auth_login_attempts_max',      'value' => '5'],
            ['belong_to' => 0, 'name' => 'auth_login_attempts_expire',   'value' => '259200'],
            ['belong_to' => 0, 'name' => 'auth_blacklist_username',      'value' => 'admin, administrator, mod, moderator, root'],
            ['belong_to' => 0, 'name' => 'auth_blacklist_prefix',        'value' => 'the, sys, system, site, super'],
            ['belong_to' => 0, 'name' => 'auth_blacklist_exceptions',    'value' => null],
        ],
    ];

    private $users_table = [
        self::DB_SCHEMAS => [
            'id'             => ['type' => 'int', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true, 'key' => true],
            'email'          => ['type' => 'varchar', 'constraint' => 100, 'unique' => true],
            'username'       => ['type' => 'varchar', 'constraint' => 50,  'unique' => true],
            'password'       => ['type' => 'varchar', 'constraint' => 60],
            'salt'           => ['type' => 'binary', 'constraint' => 22],
            'display'        => ['type' => 'varchar', 'constraint' => 100],
            'photo'          => ['type' => 'varchar', 'constraint' => 100],
            'assign_to'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'key' => 'users.groups_id'],
            'extra_perms'    => ['type' => 'varchar', 'constraint' => 50, 'key' => 'users.perms_id', 'default' => null],
            'activated'      => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'banned'         => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'ban_reason'     => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'login_attempts' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
        ],
    ];

    private $usersdetail_table = [
        self::FLAG_AUTOTIMESTAMP => false,
        self::FLAG_DESTRUCTIVE => true,
        self::DB_SCHEMAS => [
            'id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true, 'key' => true],
            'belong_to' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'key' => 'meta_users_id'],
            'name'      => ['type' => 'varchar', 'constraint' => 100],
            'value'     => ['type' => 'text', 'null' => true],
        ],
    ];

    private $userslevels_table = [
        self::DB_SCHEMAS => [
            'id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true, 'key' => true],
            'child_of'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'key' => 'groups.parent_id'],
            'name'      => ['type' => 'varchar', 'constraint' => 50, 'unique' => true],
            'desc'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'perms'     => ['type' => 'varchar', 'constraint' => 100, 'default' => null, 'key' => 'groups.permissions'],
            'default'   => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'deletable' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
        ],
        self::DB_DATA => [
            ['name' => 'admin', 'desc' => 'lang:auths_desc_group_admin', 'default' => 0, 'deletable'  => 0],
            ['name' => 'default', 'desc' => 'lang:auths_desc_group_default', 'default' => 1, 'deletable'  => 0],
        ],
    ];

    private $usersperms_table = [
        self::FLAG_AUTOTIMESTAMP => false,
        self::DB_SCHEMAS => [
            'id'   => ['type' => 'int', 'constraint' => 11, 'auto_increment' => true, 'key' => true],
            'path' => ['type' => 'varchar', 'constraint' => 100, 'default' => null],
            'name' => ['type' => 'varchar', 'constraint' => 100, 'unique' => true],
            'desc' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
        ],
        self::DB_DATA => [
            ['path' => 'system', 'name' => 'system_debug',           'desc' => 'lang:auths_desc_system_debug'],
            ['path' => 'system', 'name' => 'system_manage_settings', 'desc' => 'lang:auths_desc_system_setting'],
            ['path' => 'users',  'name' => 'users_manage_self',      'desc' => 'lang:auths_desc_users_manage_self'],
            ['path' => 'users',  'name' => 'users_manage_childs',    'desc' => 'lang:auths_desc_users_manage_childs'],
            ['path' => 'users',  'name' => 'users_manage_siblings',  'desc' => 'lang:auths_desc_users_manage_siblings'],
            ['path' => 'users',  'name' => 'users_create_new',       'desc' => 'lang:auths_desc_users_create_other'],
            ['path' => 'users',  'name' => 'users_assign_sibling',   'desc' => 'lang:auths_desc_users_assign_sibling'],
            ['path' => 'users',  'name' => 'users_assign_childs',    'desc' => 'lang:auths_desc_users_assign_childs'],
            ['path' => 'groups', 'name' => 'groups_manage_self',     'desc' => 'lang:auths_desc_groups_manage_self'],
            ['path' => 'groups', 'name' => 'groups_manage_childs',   'desc' => 'lang:auths_desc_groups_manage_childs'],
            ['path' => 'groups', 'name' => 'groups_manage_siblings', 'desc' => 'lang:auths_desc_groups_manage_siblings'],
            ['path' => 'groups', 'name' => 'groups_create_childs',   'desc' => 'lang:auths_desc_groups_create_childs'],
            ['path' => 'groups', 'name' => 'groups_create_siblings', 'desc' => 'lang:auths_desc_groups_create_siblings'],
            ['path' => 'perms',  'name' => 'perms_manage_self',      'desc' => 'lang:auths_desc_perms_manage_self'],
            ['path' => 'perms',  'name' => 'perms_manage_siblings',  'desc' => 'lang:auths_desc_perms_manage_siblings'],
        ],
    ];

    private $usersessions_table = [
        self::FLAG_AUTOTIMESTAMP => false,
        self::FLAG_DESTRUCTIVE => true,
        self::DB_SCHEMAS => [
            'key_id'     => ['type' => 'char',    'constraint' => 32, 'key' => true],
            'user_id'    => ['type' => 'int',     'constraint' => 11, 'default' => 0],
            'user_agent' => ['type' => 'varchar', 'constraint' => 150],
            'last_ip'    => ['type' => 'varchar', 'constraint' => 15],
            'device'     => ['type' => 'varchar', 'constraint' => 50],
            'last_login TIMESTAMP default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ],
    ];

    // -------------------------------------------------------------------------
    // Tables Declaration
    // -------------------------------------------------------------------------

    public function tables()
    {
        $install = [
            'username' => 'admin',
            'password' => 'password',
            'email'    => 'admin@example.com',
            'display'  => 'Admin',
            'fullname' => 'Administrator',
            'level_id' => 1,
        ];

        if (defined('MCRYPT_DEV_URANDOM')) {
            $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $salt = openssl_random_pseudo_bytes(22);
        }

        $this->users_table[self::DB_DATA][0] = [
            'email'     => $install['email'],
            'username'  => $install['username'],
            'password'  => password_hash($install['password'], PASSWORD_BCRYPT, ['salt' => $salt]),
            'salt'      => $salt,
            'display'   => $install['display'],
            'photo'     => '//www.gravatar.com/avatar/'.md5(strtolower(trim($install['email']))),
            'assign_to' => $install['level_id'],
            'activated' => 1,
        ];

        $this->usersdetail_table[self::DB_DATA] = [
            ['belong_to' => 1, 'name' => 'fullname', 'value' => $install['fullname']],
        ];

        $admin_perms = [];
        for ($i = 0; $i < count($this->usersperms_table[self::DB_DATA]); $i++) {
            $admin_perms[] = $i;
        }

        $this->userslevels_table[self::DB_DATA][0] += ['perms' => implode(',', $admin_perms)];
        $this->userslevels_table[self::DB_DATA][1] += ['perms' => null];

        return [
            'settings'     => $this->settings_table,
            'users'        => $this->users_table,
            'users_detail' => $this->usersdetail_table,
            'users_levels' => $this->userslevels_table,
            'users_perms'  => $this->usersperms_table,
        ];
    }
}
