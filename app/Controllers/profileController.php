<?php
    namespace App\Controllers;
    use CodeIgniter\Controller;

    class profileController extends BaseController {
        public function index() {
            helper(['form']);
            $session = session();

            $data['username'] = $session->get('user_name');
            $data['title'] = "Accueil - Travist";
            
            echo view('includes/header_view', $data);
            echo view('profile');
            echo view('includes/footer');
        }
    }