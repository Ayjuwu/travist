<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;

    class Travel extends Model {
        protected $table = 'travel';
        protected $primaryKey = 'id';
        public $timestamps = false;
        protected $fillable = ['travel_name', 'people_number', 'user_id', 'travel_start_date', 'travel_end_date', 'individual_price', 'total_price'];

        public function keypoints() {
            return $this->belongsToMany(Keypoint::class, 'assigned')
                        ->withPivot('start_date', 'end_date');
        }
    }
