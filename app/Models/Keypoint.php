<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;

    class Keypoint extends Model {
        protected $table = 'keypoints';
        protected $primaryKey = 'id';
        public $timestamps = false;

        protected $fillable = [
            'key_point_name',
            'key_point_price',
            'key_point_start_date',
            'key_point_end_date',
            'key_point_cover',
            'key_point_gps_x',
            'key_point_gps_y',
            'is_altered_keypoint',
            'city_id'
        ];        

        public function travels() {
            return $this->belongsToMany(Travel::class, 'assigned')
                        ->withPivot('start_date', 'end_date');
        }

        public function tags() {
            return $this->belongsToMany('App\Models\Tag', 'tagged');
        }

        public function city() {
            return $this->belongsTo('App\Models\City', 'city_id');
        }
    }
