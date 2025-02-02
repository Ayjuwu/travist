<?php
    namespace App\Controllers;
    use App\Models\Keypoint;
    use App\Models\Tag;

    class KeyPointModifyController extends BaseController {
        public function index(int $id) {
            helper(['form']);

            $data['title'] = "Modifier un point clé - Travist";
            $data['current_keypoint'] = Keypoint::find($id);
            $data['tags'] = Tag::all();

            if(is_null($data['current_keypoint'])) {
                return redirect()->to(base_url() . 'liste_des_lieux');
            } else {
                echo view('includes/header_view', $data);
                echo view('modifyKeyPoint');
                echo view('includes/footer');
            }
        }

        public function modify(int $id) {
            if($id === null) {
                return redirect()->to(base_url() . 'liste_des_lieux');
            } else {
                helper(['form']);
                $key_point = Keypoint::find($id);
    
                $rules = [
                    'key_point_name' => 'required|min_length[3]|max_length[40]|alpha_space',
                    'key_point_price' => 'required|decimal',
                    'key_point_start_date' => 'required|min_length[10]|max_length[10]|valid_date',
                    'key_point_end_date' => 'required|min_length[10]|max_length[10]|valid_date',
                    'key_point_nearest_city' => 'required|min_length[1]|max_length[75]|alpha_space',
                    'key_point_cover' => 'required|max_length[20000]',
                    'key_point_gps_location' => 'required|max_length[200]'
                ];
    
                if ($this->validate($rules)) {
                    $key_point->key_point_name = $this->request->getVar('key_point_name');
                    $key_point->key_point_price = $this->request->getVar('key_point_price');
                    $key_point->key_point_start_date = $this->request->getVar('key_point_start_date');
                    $key_point->key_point_end_date = $this->request->getVar('key_point_end_date');
                    $key_point->key_point_nearest_city = $this->request->getVar('key_point_nearest_city');
                    $key_point->key_point_cover = $this->request->getVar('key_point_cover');
                    $key_point->key_point_gps_location = $this->request->getVar('key_point_gps_location');
    
                    $key_point->save();
                    $key_point->tags()->detach();
    
                    $tags = $this->request->getVar('tags');
    
                    foreach ($tags as $tags => $id) {
                        $key_point->tags()->attach($id, ['tag_id' => $id]);
                    }
    
                    return redirect()->to(base_url() . 'liste_des_lieux');
                } else {
                    $data['validation'] = $this->validator;
                    $this->index($id);
                }
            }
        }
    }