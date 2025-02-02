<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class FiltreAdmin implements FilterInterface {
    public function before(RequestInterface $request, $arguments = null) {
        if (!session()->get('isLoggedIn') || session()->get('user_name') !== 'admin') {
            return redirect()->to(base_url() . 'connexion');
        } 
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {
        
    }
}