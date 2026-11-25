<?php
namespace App\Controllers;

use App\Models\ImagenModel;
use App\Models\UserModel;

/*
 * CONTROLADOR de administración (solo usuarios con rol "admin")
 * El filtro Admin, declarado en Routes.php, impide que otros usuarios entren.
 * El administrador puede crear, modificar y borrar cualquier usuario,
 * revisar su galería y borrar sus imágenes.
 */
class Admin extends BaseController
{
    protected $helpers = ['archivos'];

    private $mensajes = [
        'name'     => ['required' => 'El nombre es obligatorio.', 'max_length' => 'El nombre es demasiado largo.'],
        'login'    => ['required' => 'El usuario (login) es obligatorio.', 'max_length' => 'El usuario (login) es demasiado largo.',
                       'is_unique' => 'Ese usuario (login) ya existe, elige otro.', 'alpha_dash' => 'El usuario solo puede tener letras, números, guiones y guion bajo.'],
        'password' => ['required' => 'La contraseña es obligatoria.', 'min_length' => 'La contraseña debe tener al menos 6 caracteres.'],
        'rol'      => ['in_list' => 'Elige un tipo de usuario válido.'],
    ];

    /* Listado de usuarios con sus estadísticas */
    public function usuarios()
    {
        $data['users'] = (new UserModel())->conEstadisticas();
        $data['roles'] = UserModel::ROLES;

        echo view('header', ['titulo' => 'Administración de usuarios']);
        echo view('Admin/usuarios', $data);
        echo view('footer');
    }

    /* Crear un usuario */
    public function crear()
    {
        $reglas = [
            'name'     => 'required|max_length[256]',
            'login'    => 'required|max_length[128]|alpha_dash|is_unique[user.login]',
            'password' => 'required|min_length[6]',
            'rol'      => 'in_list[' . implode(',', array_keys(UserModel::ROLES)) . ']',
        ];

        if ($this->validate($reglas, $this->mensajes))
        {
            (new UserModel())->save($this->request->getPost(['name', 'login', 'password', 'rol']));
            return redirect()->to(base_url('admin/usuarios'))->with('mensaje', 'Usuario creado correctamente.');
        }

        return redirect()->to(base_url('admin/usuarios'))->withInput()->with('errores', $this->validator->getErrors());
    }

    /* Modificar los datos de cualquier usuario */
    public function editar($id)
    {
        $model = new UserModel();
        $user  = $model->get($id);

        if ($user === null)
        {
            return redirect()->to(base_url('admin/usuarios'))->with('error', 'El usuario no existe.');
        }

        $esYo = (int) $id === (int) session()->get('user');
        $data['errores'] = [];

        if ($this->request->is('post'))
        {
            $datos       = $this->request->getPost(['name', 'login', 'password', 'rol']);
            $datos['id'] = $id;

            // El administrador no puede quitarse a sí mismo el rol (evita quedarse sin acceso)
            if ($esYo)
            {
                $datos['rol'] = 'admin';
            }

            $reglas = [
                'id'       => 'required|is_natural_no_zero',
                'name'     => 'required|max_length[256]',
                'login'    => 'required|max_length[128]|alpha_dash|is_unique[user.login,id,{id}]',
                'password' => 'permit_empty|min_length[6]',
                'rol'      => 'required|in_list[' . implode(',', array_keys(UserModel::ROLES)) . ']',
            ];

            if ($this->validateData($datos, $reglas, $this->mensajes))
            {
                $cambios = ['id' => $id, 'name' => $datos['name'], 'login' => $datos['login'], 'rol' => $datos['rol']];
                if (! empty($datos['password']))
                {
                    $cambios['password'] = $datos['password'];
                }
                $model->save($cambios);

                if ($esYo)
                {
                    session()->set(['name' => $datos['name'], 'login' => $datos['login']]);
                }

                return redirect()->to(base_url('admin/usuarios'))->with('mensaje', 'Los datos de ' . $datos['name'] . ' se actualizaron.');
            }

            $data['errores'] = $this->validator->getErrors();
            $user = array_merge($user, array_intersect_key($datos, array_flip(['name', 'login', 'rol'])));
        }

        $data['user']  = $user;
        $data['roles'] = UserModel::ROLES;
        $data['esYo']  = $esYo;

        echo view('header', ['titulo' => 'Editar usuario']);
        echo view('Admin/editar', $data);
        echo view('footer');
    }

    /* Borrar un usuario con todos sus datos */
    public function borrar($id)
    {
        $model = new UserModel();
        $user  = $model->get($id);

        if ($user === null)
        {
            return redirect()->to(base_url('admin/usuarios'))->with('error', 'El usuario no existe.');
        }
        if ((int) $id === (int) session()->get('user'))
        {
            return redirect()->to(base_url('admin/usuarios'))->with('error', 'No puedes borrar tu propia cuenta de administrador.');
        }

        $model->borrarConDatos($id);

        return redirect()->to(base_url('admin/usuarios'))->with('mensaje', 'Se eliminó a ' . $user['name'] . ' junto con todas sus imágenes y datos.');
    }

    /* Ver la galería de cualquier usuario */
    public function galeria($id)
    {
        $user = (new UserModel())->get($id);

        if ($user === null)
        {
            return redirect()->to(base_url('admin/usuarios'))->with('error', 'El usuario no existe.');
        }

        $data['usuario']  = $user;
        $data['imagenes'] = (new ImagenModel())->listar($id);

        echo view('header', ['titulo' => 'Galería de ' . $user['name']]);
        echo view('Admin/galeria', $data);
        echo view('footer', ['scripts' => ['galeria.js']]);
    }

    /* Borrar una imagen de cualquier usuario */
    public function borrarImagen($id)
    {
        $model  = new ImagenModel();
        $imagen = $model->find($id);

        if (! $imagen)
        {
            return redirect()->to(base_url('admin/usuarios'))->with('error', 'La imagen no existe.');
        }

        $model->borrarImagen($imagen);

        return redirect()->to(base_url('admin/usuarios/' . $imagen['user'] . '/galeria'))->with('mensaje', 'Se eliminó la imagen "' . $imagen['titulo'] . '".');
    }
}
