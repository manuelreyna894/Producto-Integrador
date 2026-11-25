<?php namespace App\Controllers;

/*
 * CONTROLADOR de la página de inicio (formulario de inicio de sesión)
 */
class Home extends BaseController
{
    public function index()
    {
        // Si ya hay sesión, el usuario va directo a su panel
        if (session()->has('user'))
        {
            return redirect()->to(base_url('panel'));
        }

        // COOKIE "recordar mi usuario": su login aparece ya escrito
        $data['login_recordado'] = $this->request->getCookie('recordar_login') ?? '';

        echo view('header', ['titulo' => 'Iniciar sesión']);
        echo view('home', $data);
        echo view('footer');
    }
}
