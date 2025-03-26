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

        public function getKeypointsByCountry(string $country) {
            $keypoints = Keypoint::join('cities', 'keypoints.city_id', '=', 'cities.id')
                ->where('cities.city_country', '=', $country)
                ->select('keypoints.*') // Pour récupérer uniquement les colonnes de keypoints
                ->get();

            return $this->respond($keypoints);
        }

        public function getKeypointsByCity(int $city_id) {
            $keypoints = Keypoint::where('city_id', '=', $city_id)->get();
            return $this->respond($keypoints);
        }

        public function getNearestKeypointPosition(float $latitude, float $longitude) {
            $keypoint = Keypoint::selectRaw(
                "*, ((key_point_gps_x - ?) * (key_point_gps_x - ?) + (key_point_gps_y - ?) * (key_point_gps_y - ?)) as distance",
                [$latitude, $latitude, $longitude, $longitude]
            )
            ->orderBy("distance", "asc")
            ->first();

            if (!$keypoint) {
                return $this->failNotFound("Aucun keypoint trouvé");
            }
            
            return $this->respond($keypoint);
        }
    }