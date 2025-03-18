<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    
    class City extends Model {
        public $timestamps = false;

        public function keypoints() {
            return $this->hasMany('App\Models\Keypoint', 'located_city');
        }
    }