<?php namespace App\Models;

use CodeIgniter\Model;

/*
 * MODELO de la entidad "user"
 * Consultas de usuarios, verificación de credenciales y borrado
 * completo de un usuario con todos sus datos.
 */
class UserModel extends Model
{
    protected $table         = 'user';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'login', 'password', 'rol'];
    protected $returnType    = 'array';

    // Tipos de usuario: clave => texto para mostrar
    public const ROLES = ['usuario' => 'Usuario', 'admin' => 'Administrador'];

    protected $beforeInsert = ['cifrarPassword'];
    protected $beforeUpdate = ['cifrarPassword'];

    public function get($id = false)
    {
        if ($id === false)
        {
            return $this->findAll();
        }

        return $this->asArray()->where(['id' => $id])->first();
    }

    /*
     * Usuarios con el número de imágenes y el espacio que ocupan:
     * SELECT user.*, (SELECT COUNT(*) FROM imagen WHERE imagen.user = user.id) AS imagenes, ...
     */
    public function conEstadisticas()
    {
        return $this->select('user.id, user.name, user.login, user.rol')
            ->select('(SELECT COUNT(*) FROM imagen WHERE imagen.user = user.id) AS imagenes', false)
            ->select('(SELECT COALESCE(SUM(tamano), 0) FROM imagen WHERE imagen.user = user.id) AS espacio', false)
            ->select('(SELECT COUNT(*) FROM archivo WHERE archivo.user = user.id) AS archivos', false)
            ->orderBy('user.name', 'ASC')
            ->findAll();
    }

    public function contarAdmins()
    {
        return $this->where('rol', 'admin')->countAllResults();
    }

    /*
     * Verifica login y contraseña. Regresa el usuario o null.
     */
    public function login($login, $password)
    {
        $user = $this->asArray()->where(['login' => $login])->first();

        if ($user === null)
        {
            return null;
        }

        if (password_verify($password, $user['password']))
        {
            return $user;
        }

        // Usuarios del tutorial con contraseña en md5: se actualiza a password_hash
        if ($user['password'] === md5($password))
        {
            $this->save(['id' => $user['id'], 'password' => $password]);
            return $user;
        }

        return null;
    }

    /*
     * Borra a un usuario y TODOS sus datos: imágenes, álbumes, archivos,
     * documentos y publicaciones (registros y archivos en el disco).
     */
    public function borrarConDatos($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();   // Todo se borra junto, o no se borra nada

        foreach (['imagen', 'album', 'archivo', 'documento', 'publication'] as $tabla)
        {
            $db->table($tabla)->where('user', $id)->delete();
        }
        $this->delete($id);

        $db->transComplete();

        // Carpetas del usuario en el servidor
        helper('filesystem');
        foreach (['imagenes', 'archivos'] as $carpeta)
        {
            $ruta = WRITEPATH . 'uploads/' . $carpeta . '/' . (int) $id;
            if (is_dir($ruta))
            {
                delete_files($ruta, true);   // Borra los archivos y subcarpetas (miniaturas)
                rmdir($ruta);                // Borra la carpeta, ya vacía
            }
        }

        return $db->transStatus();
    }

    protected function cifrarPassword(array $data)
    {
        if (isset($data['data']['password']))
        {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        return $data;
    }
}
