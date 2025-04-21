<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;

    class City extends Model {
        public $timestamps = false;
        protected $fillable = ['city_name', 'city_country'];

        public function keypoints() {
            return $this->hasMany('App\Models\Keypoint', 'city_id');
        }        
    }
