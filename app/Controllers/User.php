<?php
namespace App\Controllers;
use App\Models\UserModel;

/*
 * CONTROLADOR de usuario
 * - Inicio y cierre de sesión (sesiones y cookies)
 * - Mi cuenta: cada usuario modifica sus propios datos
 * La administración de todos los usuarios está en el controlador Admin.
 */
class User extends BaseController
{
    /* ================= INICIO DE SESIÓN ================= */
    public function login()
    {
        if ($this->request->is('post') && $this->validate(['login' => 'required', 'password' => 'required']))
        {
            $login = $this->request->getPost('login');
            $user  = (new UserModel())->login($login, $this->request->getPost('password'));

            if ($user !== null)
            {
                // COOKIE: fecha del acceso anterior (se lee antes de actualizarla)
                $nombre_cookie   = 'ultimo_acceso_' . $user['id'];
                $acceso_anterior = $this->request->getCookie($nombre_cookie);

                // SESIÓN: datos del usuario, incluido su rol
                session()->regenerate();
                session()->set([
                    'user'            => $user['id'],
                    'name'            => $user['name'],
                    'login'           => $user['login'],
                    'rol'             => $user['rol'] ?? 'usuario',
                    'inicio_sesion'   => time(),
                    'acceso_anterior' => $acceso_anterior,
                ]);

                $this->response->setCookie($nombre_cookie, (string) time(), 60 * 60 * 24 * 30);

                if ($this->request->getPost('recordar'))
                {
                    $this->response->setCookie('recordar_login', $login, 60 * 60 * 24 * 30);
                }
                else
                {
                    $this->response->deleteCookie('recordar_login');
                }

                return redirect()->to(base_url('panel'))->withCookies();
            }

            session()->setFlashdata('login_error', 'El usuario o la contraseña no son correctos.');
        }
        else
        {
            session()->setFlashdata('login_error', 'Escribe tu usuario y tu contraseña.');
        }

        return redirect()->to(base_url());
    }

    /* ================= CERRAR SESIÓN ================= */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url());
    }

    /* ================= MI CUENTA ================= */
    public function cuenta()
    {
        $model = new UserModel();
        $id    = session()->get('user');
        $user  = $model->get($id);
        $data['errores'] = [];

        if ($this->request->is('post'))
        {
            $datos       = $this->request->getPost(['name', 'password_actual', 'password', 'password_confirmar']);
            $datos['id'] = $id;

            $reglas = [
                'name'               => 'required|max_length[256]',
                'password'           => 'permit_empty|min_length[6]',
                'password_confirmar' => 'matches[password]',
            ];
            $mensajes = [
                'name'               => ['required' => 'El nombre es obligatorio.', 'max_length' => 'El nombre es demasiado largo.'],
                'password'           => ['min_length' => 'La nueva contraseña debe tener al menos 6 caracteres.'],
                'password_confirmar' => ['matches' => 'La confirmación no coincide con la nueva contraseña.'],
            ];

            if ($this->validateData($datos, $reglas, $mensajes))
            {
                $cambios = ['id' => $id, 'name' => $datos['name']];

                // Para cambiar la contraseña se pide la actual
                if (! empty($datos['password']))
                {
                    if (! $model->login($user['login'], (string) $datos['password_actual']))
                    {
                        $data['errores'][] = 'La contraseña actual no es correcta.';
                    }
                    else
                    {
                        $cambios['password'] = $datos['password'];
                    }
                }

                if (empty($data['errores']))
                {
                    $model->save($cambios);
                    session()->set('name', $datos['name']);
                    return redirect()->to(base_url('cuenta'))->with('mensaje', 'Tus datos se guardaron correctamente.');
                }
            }
            else
            {
                $data['errores'] = $this->validator->getErrors();
            }

            $user['name'] = $datos['name'];
        }

        $data['user']  = $user;
        $data['roles'] = UserModel::ROLES;

        echo view('header', ['titulo' => 'Mi cuenta']);
        echo view('User/cuenta', $data);
        echo view('footer');
    }
}
