<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/*
 * FILTRO de autenticación
 * Se ejecuta ANTES del controlador en todas las rutas privadas
 * (ver app/Config/Routes.php):
 *   1. Si no hay un usuario en la sesión, regresa al inicio de sesión.
 *   2. Si la cuenta fue borrada por el administrador, cierra la sesión.
 *   3. Actualiza el rol en la sesión, por si el administrador lo cambió.
 */
class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->has('user'))
        {
            session()->setFlashdata('login_error', 'Debes iniciar sesión para entrar a esa sección.');
            return redirect()->to(base_url());
        }

        $user = (new UserModel())->find(session()->get('user'));

        if ($user === null)
        {
            // Se quitan los datos del usuario de la sesión (el aviso sí se conserva)
            session()->remove(['user', 'name', 'login', 'rol', 'inicio_sesion', 'acceso_anterior']);
            session()->setFlashdata('login_error', 'Tu cuenta ya no existe. Comunícate con el administrador.');
            return redirect()->to(base_url());
        }

        session()->set(['rol' => $user['rol'] ?? 'usuario', 'name' => $user['name']]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
