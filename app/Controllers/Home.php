<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $session = session();

        if($session->get('isLoggedIn')) {
            if($session->get('user_name') !== 'admin') {
                return redirect()->to(base_url() . 'profil');
            } else {
                return redirect()->to(base_url() . 'panel_administrateur');
            }
            
        } else {
            $data['title']= "Accueil - Travist";

            echo view('includes/header_view', $data);
            echo view('Home');
            echo view('includes/footer');
        }
    }
}
