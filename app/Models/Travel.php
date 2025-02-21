<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    
    class Travel extends Model {
        public $timestamps = false;

        public function currentUser() {
            return $this->belongsToMany('App\Models\User', 'travel');
        }

        public function keypoints() {
            return $this->belongsToMany('App\Models\Keypoint', 'assigned');
        }
    }