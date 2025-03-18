<?php
    namespace App\Controllers;
    use App\Models\City;

    class VillesController extends BaseController {
        function index() {
            $data['title'] = "Liste des villes - Travist";
            $data['cities'] = City::all();

            echo view('includes/header_view', $data);
            echo view('liste_villes');
            echo view('includes/footer');
        }

        function delete(int $id) {
            $city = City::find($id);

            $city->keypoints()->detach();
            $city->delete();

            return redirect()->to(base_url() . 'liste_des_tags');
        }
    }
    