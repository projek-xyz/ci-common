<?php
use Projek\CI\Common\Model;

class Settings extends Model
{
    public $table = 'settings';

    public $is_destructive = true;

    public $is_autotimestamp = false;
}
