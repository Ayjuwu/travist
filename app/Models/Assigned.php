<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assigned extends Model
{
    protected $table = 'assigned';
    public $timestamps = false;

    protected $fillable = [
        'travel_id',
        'keypoint_id',
        'start_date',
        'end_date'
    ];
}
