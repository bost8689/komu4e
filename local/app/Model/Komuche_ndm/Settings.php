<?php

namespace Komu4e\Model\Komuche_ndm;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';
    public $timestamps = False;
    protected $primaryKey = 'id';
    protected $fillable = array('id', 'name', 'value1', 'value2', 'value3', 'value4');
}
