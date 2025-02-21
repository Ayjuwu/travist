<?php
    namespace App\Controllers;
    use App\Models\User;

    class connexionController extends BaseController {
        public function index() {
            helper(['form']);
            $data['title']= "Connexion - Travist";

            echo view('includes/header_view', $data);
            echo view('connexion');
            echo view('includes/footer');
        }

        public function loginAccount() {
            $session = session();

            $user_email = $this->request->getVar('user_email');
            $user_password = $this->request->getVar('user_password');
            $user = User::where('user_email', $user_email)->first();

            if ($user) {
                $password = $user->user_password;
                $authenticatePassword = password_verify($user_password, $password);
                if ($authenticatePassword) {
                    $session_data = [
                        'user_id' => $user->id,
                        'user_name' => $user->user_name,
                        'user_email' => $user->user_email,
                        'isLoggedIn' => TRUE
                    ];
                    $session->set($session_data);
                    if($user->user_name === 'admin') {
                        return redirect()->to(base_url() . 'panel_administrateur');
                    } else {
                        return redirect()->to(base_url() . 'profil');
                    }
                } else {
                    $session->setFlashdata('msg', 'Le mot de passe est incorrect.');
                    return redirect()->to(base_url() . 'connexion');
                }
            } else {
                $session->setFlashdata('msg', "Le mail n'existe pas.");
                return redirect()->to(base_url() . 'connexion');
            }
        }

        public function disconnect() {
            $session = session();

            if($session->get('isLoggedIn')) {
                session_destroy();
                return redirect()->to(base_url() . 'connexion');
            } else {
                return redirect()->to(base_url() . 'connexion');
            }
        }
    }

