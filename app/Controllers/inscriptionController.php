<?php
    namespace App\Controllers;
    use App\Models\User;

    class inscriptionController extends BaseController {
        public function index() {
            helper(['form']);
            $data['title']= "Créer un compte - Travist";

            echo view('includes/header_view', $data);
            echo view('inscription');
            echo view('includes/footer');
        }

        function createAccount() {
            helper(["form"]);
            $rules = [
                'user_name' => 'required|min_length[3]|max_length[20]|is_unique[users.user_name]|alpha_numeric_space',
                'user_email' => 'required|max_length[30]|is_unique[users.user_email]|valid_email',
                'user_password' => 'required|min_length[10]|max_length[30]|alpha_numeric',
                'confirm_password' => 'matches[user_password]'
            ];

            if ($this->validate($rules)) {
                $user = new User();

                $user->user_name = $this->request->getVar('user_name');
                $user->user_email = $this->request->getVar('user_email');
                $user->user_password = password_hash($this->request->getVar('user_password'), PASSWORD_DEFAULT);

                $user->save();

                return redirect()->to(base_url() . 'connexion');
            } else {
                $data['validation'] = $this->validator;
                echo view('inscription', $data);
            }
        }
    }

    