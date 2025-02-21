<?php
    namespace App\Controllers;
    use App\Models\Keypoint;
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
                'key_point_cover' => 'required|max_length[20000]|is_unique[keypoints.key_point_cover]',
                'key_point_gps_location' => 'required|max_length[200]|is_unique[keypoints.key_point_gps_location]',
            ];

            if ($this->validate($rules)) {
                $key_point = new Keypoint();

                $key_point->key_point_name = $this->request->getVar('key_point_name');
                $key_point->key_point_price = $this->request->getVar('key_point_price');
                $key_point->key_point_start_date = $this->request->getVar('key_point_start_date');
                $key_point->key_point_end_date = $this->request->getVar('key_point_end_date');
                $key_point->key_point_nearest_city = $this->request->getVar('key_point_nearest_city');
                $key_point->key_point_cover =  base64_encode($this->request->getVar('key_point_cover'));
                $key_point->key_point_gps_location = $this->request->getVar('key_point_gps_location');

                $key_point->save();

                $tags = $this->request->getVar('tags');

                foreach ($tags as $tag => $id) {
                    $key_point->tags()->attach($id, ['tag_id' => $id]);
                }

                return redirect()->to(base_url() . 'liste_des_lieux');
            } else {
                $data['validation'] = $this->validator;
                $this->index();
            }
        }
    }