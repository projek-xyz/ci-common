<?php
namespace Projek\CI\Common;

use CI_Model;

class Model extends CI_Model
{
    /**
     * Make use of common traits
     */
    use Utils\ErrorHandlerTrait;
    use Utils\HooksHandlerTrait;

    const FLAG_DESTRUCTIVE   = 'destructive';
    const FLAG_AUTOTIMESTAMP = 'auto_timestamp';

    /**
     * Table name (default: null)
     *
     * @var  string|null
     */
    public $table = null;

    /**
     * Table column list
     *
     * @var  array
     */
    public $cols = [];

    /**
     * Table primary key
     *
     * @var  string
     */
    protected $primary_key = 'id';

    /**
     * Table creation key
     *
     * @var  string
     */
    protected $creation_key = 'created';

    /**
     * Table modification key
     *
     * @var  string
     */
    protected $modification_key = 'modified';

    /**
     * Time stamp format [datetime|unixtime]
     *
     * @var  string
     */
    protected $timestamp_format = 'unixtime';

    /**
     * Table join alias (default: null)
     *
     * @var  string|null
     */
    protected $join_alias = null;

    /**
     * Data result limit
     *
     * @var  int
     */
    protected $data_limit = 10;

    /**
     * Need data encryption?
     *
     * @var  array
     */
    protected $is_encrypted = false;

    /**
     * Row counts
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Flags this table
     *
     * @var array
     */
    protected $flags = [
        self::FLAG_DESTRUCTIVE => false,
        self::FLAG_AUTOTIMESTAMP => true,
    ];

    public function __construct()
    {
        if (null !== $this->table) {
            // Just in case it's not been loaded.
            $this->load->database();

            // Get list of columns from current table
            $this->cols = $this->db->list_fields($this->table);
        }

        // Overwrite key configuration if needed
        foreach (['primary', 'creation', 'modification'] as $key) {
            if ($config = $this->config->item('model_' . $key . '_key')) {
                $this->{$key . '_key'} = $config;
            }
            // Remove protected columns from list
            unset($this->cols[$this->{$key . '_key'}]);
        }

        if ($this->is_encrypted) {
            $this->load->library('encryption');
        }

        // Log the thing
        log_message('info', 'Model '.get_called_class().' Initialized');
    }

    /* -----------------------------------------------------------------------
     * CRUD
     * -----------------------------------------------------------------------*/

    /**
     * Insert new data for $this->table
     *
     * @param   array   $data        Data to store
     * @param   bool    $return_obj  Wanna see whole stored data? or just id
     * @return  mixed
     */
    public function add(array $data, $return_obj = false)
    {
        if (false === $this->has_table()) return false;

        if (empty($data)) {
            $this->set_error('No data to insert');
            return false;
        }

        if ($timestamp = $this->timestamp('creation')) {
            $data += $timestamp;
        }

        if (! $this->db->insert($this->table, $data)) {
            $this->set_error($this->db->error());
            return false;
        }

        $id = $this->db->insert_id();

        if ($return_obj) {
            return $this->get([$this->primary_key => $id], 1);
        }

        return $id;
    }

    /**
     * Fetching some data as per $term needed or just grab all
     *
     * @param   mixed  $term    Data terms: int|null
     * @param   mixed  $limit   Data result limitation: null|int|false
     * @param   int    $offset  Data result offset
     * @return  mixed
     */
    public function get($term = null, $limit = null, $offset = 0)
    {
        if (false === $this->has_table()) return false;

        $term = $this->normalize_term($term);

        $this->db->where($term);

        $limit !== null || $limit = $this->data_limit;

        // Limit the result if $limit is int and greater than 0
        if (false !== $limit and is_int($limit) and $limit > 0) {
            // Forget about offset for now :P
            $this->db->limit($limit, $offset);
        }

        if ( ! $data = $this->db->get($this->table)) {
            $this->set_error($this->db->error());
            return false;
        }

        return 1 === $limit ? $data->row() : $data->result();
    }

    /**
     * Edit existing data
     *
     * @param   mixed   $term  Data terms: int|array
     * @param   array   $data  Data to store
     * @return  mixed
     */
    public function edit($term, array $data = [])
    {
        if (false === $this->has_table()) return false;

        if (empty($data)) {
            $this->set_error('No data to update');
            return false;
        }

        if ($timestamp = $this->timestamp()) {
            $data += $timestamp;
        }

        $term = $this->normalize_term($term);

        if ( ! $return = $this->db->update($this->table, $data, $term)) {
            $this->set_error($this->db->error());
            return false;
        }

        return $return;
    }

    /**
     * Edit existing data
     *
     * @param   mixed  $term   Data terms: int|array
     * @param   mixed  $force  Wanna give a nuke? null|bool
     * @return  mixed
     */
    public function del($term, $force = null)
    {
        if (false === $this->has_table()) return false;

        $force !== null or $force = $this->is_destructive;

        $term = $this->normalize_term($term);

        if (true === $force) {
            $return = $this->db->delete($this->table, $term);
        } else {
            $return = $this->trash_it($term, true);
        }

        if ( ! $return) {
            $this->set_error($this->db->error());
            return false;
        }

        return $return;
    }

    /**
     * Restore all deleted data
     *
     * @param   mixed  $term  Data terms: int|array
     * @return  mixed
     */
    public function undel($term)
    {
        if (
            false === $this->has_table() or
            true === $this->is_destructive
        ) {
            $this->set_error('Model '.get_class($this).' has no recoverable data');
            return false;
        }

        $term = $this->normalize_term($term);

        return $this->trash_it($term, false);
    }

    /* -----------------------------------------------------------------------
     * Helpers
     * -----------------------------------------------------------------------*/

    /**
     * Count all rows
     *
     * @param   bool  $with_deleted  Including deleted data?
     * @return  int
     */
    public function count($with_deleted = null)
    {
        if (false === $this->has_table()) return false;

        if ($this->count === 0) {
            $with_deleted !== null or $with_deleted = (! $this->is_destructive);

            if (false === $with_deleted) {
                $db = $this->db->where($this->deletion_key, 0);
            }

            $this->count = $db->count_all_results($this->table);
        }

        return $this->count;
    }

    /**
     * Determine is this model has table assigned
     *
     * @return  bool
     */
    public function has_table()
    {
        if (null === $this->table) {
            $this->set_error('This model is has no table specified', true);
        } elseif (false === $this->table) {
            return false;
        }
    }

    /**
     * Wanna mark a thing as garbage?
     *
     * @param   bool  $flag  Only true or false should be passed
     * @return  bool
     */
    public function trash_it($term, $flag = true)
    {
        return $this->edit($term, [
            $this->deletion_key => (int) $flag
        ] + $this->timestamp('modification'));
    }

    /**
     * Generate creation & modification timestamp
     *
     * @param   string  $state    Timestamp type
     * @param   int     $user_id  ID of user who did it
     * @return  array
     */
    public function timestamp($state = 'modification', $user_id = null)
    {
        if ( ! in_array($state, ['creation', 'modification'])) {
            $this->set_error('Unsupported timestamp state '.$state, true);
        }

        $is_datetime = strtolower($this->timestamp_format) == 'datetime';
        $timestamp = $is_datetime ? date('Y-m-d H:i:s') : time();

        if (null === $user_id and !is_cli()) {
            $user_id = $this->session->user_data('user_id') ?: null;
        }

        $stamps = [$this->modification_key => $timestamp];

        if ($state == 'creation') {
            $stamps += [$this->creation_key => $timestamp];
        }

        if ($this->is_autotimestamp) {
            return $stamps;
        }

        return [];
    }

    /**
     * Basic query term normalization
     *
     * @param   mixed  $term  Data term
     * @return  array
     */
    protected function normalize_term($term = null)
    {
        if (null === $term) {
            return [];
        }

        if (is_int($term)) {
            $term = [$this->primary_key => $term];
        }

        if ($_term = $this->call_hook('term.after.normalized', $term)) {
            $term = $_term;
        }

        return $term;
    }
}
