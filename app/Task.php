<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $table = 'task';
	// protected $primaryKey = 'id';
    // public $timestamps = true;
    

	protected $fillable = [
		'judul_task',
		'detail_task',
		'id_status',
		'id_user_send',
		'id_user_assign',
	];
}
