<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'sender_userid', 'reciever_userid',
        'department_id', 'message', 'status',
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }
    
}
