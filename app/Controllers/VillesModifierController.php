<?php
    namespace App\Controllers;
    use App\Models\City;

    class VillesModifierController extends BaseController {
        public function index(int $id) {
            helper(['form']);

            $data['title'] = "Modifier une ville - Travist";
            $data['current_city'] = City::find($id);

            if(!is_null($data['current_city'])) {
                echo view('includes/header_view', $data);
                echo view('modifier_ville');
                echo view('includes/footer');
            } else {
                return redirect()->to(base_url() . 'liste_des_villes'); 
            }
        }

        public function modify(int $id) {
            if(!is_null($id)) {
                helper(['form']);

                $city = City::find($id);
                $rules = [
                    'city_name' => 'required|min_length[2]|max_length[25]|regex_match[/^[\p{L}\s]+$/u]',
                    'city_country' => 'required|min_length[2]|max_length[30]|regex_match[/^[\p{L}\s]+$/u]',
                ];
    
                if ($this->validate($rules)) {
                    $city->city_name = $this->request->getVar('city_name');
                    $city->city_country = $this->request->getVar('city_country');

                    $city->save();
    
                    return redirect()->to(base_url() . 'liste_des_villes');
                } else {
                    $data['validation'] = $this->validator;
                    $this->index($city->id);
                }
            } else {
                return redirect()->to(base_url() . 'liste_des_villes');
            }
        }
    }