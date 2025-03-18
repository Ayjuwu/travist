<?php
    namespace App\Controllers;
    use CodeIgniter\API\ResponseTrait;

    use App\Models\Travel;
    use App\Models\Keypoint;
    use App\Models\Tag;

    class WebServiceController extends BaseController {
        use ResponseTrait;
        
        public function getKeypoints() {
            $keypoints = Keypoint::all();
            return $this->respond($keypoints);
        }

        public function getTravels() {
            $travels = Travel::all();
            return $this->respond($travels);
        }

        public function getTags() {
            $tags = Tag::all();
            return $this->respond($tags);
        }

        public function getTravelsByUser(int $user_id) {
            $travels = Travel::where('user_id', '=', $user_id)->get();
            return $this->respond($travels);
        }

        public function getKeypointsByTag(int $tag_id) {
            $tag = Tag::find($tag_id);

            if(!is_null($tag)) {
                $keypoints = $tag->keypoints()->get();
                return $this->respond($keypoints);
            }
        }

        public function getKeypointsByCountry(int $country_id) {
            
        }

        public function getKeypointsByCity(int $city_id) {
            
        }
    }