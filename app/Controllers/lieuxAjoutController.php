<?php
    namespace App\Controllers;
    use App\Models\Keypoint;
    use App\Models\City;
    use App\Models\Tag;
   
    class lieuxAjoutController extends BaseController {
        public function index() {
            helper(['form']);

            $data['title'] = "Créer un nouveau lieu - Travist";
            $data['tags'] = Tag::all();

            echo view('includes/header_view', $data);
            echo view('addKeyPoint');
            echo view('includes/footer');
        }

        public function createKeyPoint() {
            $rules = [
                'key_point_name' => 'required|min_length[3]|max_length[40]|is_unique[keypoints.key_point_name]|alpha_space',
                'key_point_price' => 'required|decimal',
                'key_point_start_date' => 'required|min_length[10]|max_length[10]|valid_date',
                'key_point_end_date' => 'required|min_length[10]|max_length[10]|valid_date',
                'key_point_nearest_city' => 'required|min_length[1]|max_length[75]|alpha_space',
                'key_point_gps_x' => 'required|max_length[200]|is_unique[keypoints.key_point_gps_x]|decimal',
                'key_point_gps_y' => 'required|max_length[200]|is_unique[keypoints.key_point_gps_y]|decimal',
            ];

            if ($this->validate($rules)) {
                $keypoint = new Keypoint();

                $keypoint->key_point_name = $this->request->getVar('key_point_name');
                $keypoint->key_point_price = $this->request->getVar('key_point_price');
                $keypoint->key_point_start_date = $this->request->getVar('key_point_start_date');
                $keypoint->key_point_end_date = $this->request->getVar('key_point_end_date');
                $keypoint->key_point_gps_x = $this->request->getVar('key_point_gps_x');
                $keypoint->key_point_gps_y = $this->request->getVar('key_point_gps_y');
                
                $image = $this->request->getFile('key_point_cover');

                $countryId = $this->request->getVar('country');
                $cityId = $this->request->getVar('city');
                $tags = $this->request->getVar('tags');
                
                $imageData = file_get_contents($image->getTempName());
                $base64Image = base64_encode($imageData);

                $keypoint->key_point_cover = $base64Image;

                $city = City::find($cityId);
                $keypoint->city()->attach($city->id, ['city_id' => $city->id]);

                $keypoint->save();

                foreach ($tags as $tag => $id) {
                    $keypoint->tags()->attach($id, ['tag_id' => $id]);
                }

                return redirect()->to(base_url() . 'liste_des_lieux');
            } else {
                $data['validation'] = $this->validator;
                $this->index();
            }
        }
    }