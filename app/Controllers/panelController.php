<?php
    namespace App\Controllers;

    class panelController extends BaseController {
        public function index() {
            $data['title'] = 'Panel Administrateur';

            echo view('includes/header_view', $data);
            echo view('panel');
            echo view('includes/footer');
        }
    }