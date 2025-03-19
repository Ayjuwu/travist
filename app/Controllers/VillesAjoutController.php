<?php
    namespace App\Controllers;
    use App\Models\City;

    class VillesAjoutController extends BaseController {
        function index() {
            helper(['form']);
            $data['title']= "Ajouter une nouvelle ville - Travist";

            echo view('includes/header_view', $data);
            echo view('ajouter_ville');
            echo view('includes/footer');
        }

        public function createCity() {
            $rules = [
                'city_name' => 'required|min_length[2]|max_length[25]|regex_match[/^[\p{L}\s]+$/u]',
                'city_country' => 'required|min_length[2]|max_length[30]|regex_match[/^[\p{L}\s]+$/u]',
            ];

            if ($this->validate($rules)) {
                $city = new City();

                $city->city_name = $this->request->getVar('city_name');
                $city->city_country = $this->request->getVar('city_country');
                $city->save();

                return redirect()->to(base_url() . 'liste_des_villes');
            } else {
                $data['validation'] = $this->validator;
                $this->index();
            }
        }
    }