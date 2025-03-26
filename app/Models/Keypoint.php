<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;

    class Keypoint extends Model {
        protected $table = 'keypoints';
        protected $primaryKey = 'id';
        public $timestamps = false;

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
