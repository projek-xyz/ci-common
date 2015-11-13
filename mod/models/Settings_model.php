<?php
use Projek\CI\Common\Base\Model;

class Settings_model extends Model
{
    public $table = 'settings';

    public $is_destructive = true;

    public $is_autotimestamp = false;
}
