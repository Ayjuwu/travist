<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    
    class Tag extends Model {
        public $timestamps = false;
        protected $fillable = ['tag_name'];

        public function keypoints() {
            return $this->belongsToMany('App\Models\Keypoint', 'tagged');
        }
    }