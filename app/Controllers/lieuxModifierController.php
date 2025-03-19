<?php
    namespace App\Controllers;
    use App\Models\Keypoint;
    use App\Models\City;
    use App\Models\Tag;

    class lieuxModifierController extends BaseController {
        public function index(int $id) {
            helper(['form']);

            $data['title'] = "Modifier un point clé - Travist";
            $data['current_keypoint'] = Keypoint::find($id);
            $data['cities'] = City::all();
            $data['tags'] = Tag::all();

            if(!is_null($data['current_keypoint'])) {
                echo view('includes/header_view', $data);
                echo view('modifier_lieux');
                echo view('includes/footer');
            } else {
                return redirect()->to(base_url() . 'liste_des_lieux');
            }
        }

        public function modify(int $id) {
            if(!is_null($id)) {
                helper(['form']);

                $keypoint = Keypoint::find($id);
                $rules = [
                    'key_point_name' => 'required|min_length[3]|max_length[40]|alpha_space',
                    'key_point_price' => 'required|decimal',
                    'key_point_start_date' => 'required|min_length[10]|max_length[10]|valid_date',
                    'key_point_end_date' => 'required|min_length[10]|max_length[10]|valid_date',
                    'city' => 'required|integer',
                    'key_point_gps_x' => 'required|max_length[50]|decimal',
                    'key_point_gps_y' => 'required|max_length[50]|decimal',
                ];
    
                if ($this->validate($rules)) {
                    $keypoint->key_point_name = $this->request->getVar('key_point_name');
                    $keypoint->key_point_price = $this->request->getVar('key_point_price');
                    $keypoint->key_point_start_date = $this->request->getVar('key_point_start_date');
                    $keypoint->key_point_end_date = $this->request->getVar('key_point_end_date');
                    $keypoint->key_point_gps_x = $this->request->getVar('key_point_gps_x');
                    $keypoint->key_point_gps_y = $this->request->getVar('key_point_gps_y');
            
                    $image = $this->request->getFile('key_point_cover');
                    $imageData = file_get_contents($image->getTempName());
                    $base64Image = base64_encode($imageData);
                    $keypoint->key_point_cover = $base64Image;
            
                    $keypoint->city_id = $this->request->getVar('city'); // Assigner directement l'ID de la ville

                    $keypoint->tags()->detach();
                    $keypoint->save();
            
                    // Assigner les tags
                    $tags = $this->request->getVar('tags');
                    if (!empty($tags)) {
                        foreach ($tags as $id) {
                            $keypoint->tags()->attach($id);
                        }
                    }
            
                    return redirect()->to(base_url() . 'liste_des_lieux');
                } else {
                    $data['validation'] = $this->validator;
                    $this->index($id);
                }
            } else {
                return redirect()->to(base_url() . 'liste_des_lieux');
            }
        }
    }