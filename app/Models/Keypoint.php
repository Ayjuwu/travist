<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    
    class Keypoint extends Model {
        public $timestamps = false;

        public function travels() {
            return $this->belongsToMany('App\Models\Travel', 'assigned');
        }

        public function tags() {
            return $this->belongsToMany('App\Models\Tag', 'tagged');
        }
    }