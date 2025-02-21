<?php
    namespace App\Controllers;
    use CodeIgniter\Controller;
    use App\Models\Travel;

    class profilController extends BaseController {
        public function index() {
            helper(['form']);
            $userID = session()->get('user_id');

            $data['username'] = session()->get('user_name');
            $data['title'] = "Accueil - Travist";
            $data['travels'] = Travel::where('user_id', $userID)->get();
            
            echo view('includes/header_view', $data);
            echo view('profile');
            echo view('includes/footer');
        }
    }