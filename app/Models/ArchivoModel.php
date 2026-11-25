<?php namespace App\Models;

use CodeIgniter\Model;

/*
 * MODELO de la entidad "archivo" (metadatos de los archivos subidos)
 */
class ArchivoModel extends Model
{
    protected $table         = 'archivo';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user', 'nombre_original', 'nombre_guardado', 'tipo', 'tamano'];
    protected $returnType    = 'array';

    // Todos los archivos de un usuario, los más recientes primero
    public function delUsuario($user)
    {
        return $this->where('user', $user)->orderBy('id', 'DESC')->findAll();
    }

    // Un archivo, solo si pertenece al usuario
    public function buscar($id, $user)
    {
        return $this->where(['id' => $id, 'user' => $user])->first();
    }

    // Suma del tamaño de todos los archivos del usuario (en bytes)
    public function espacioUsado($user)
    {
        $fila = $this->selectSum('tamano')->where('user', $user)->first();
        return (int) ($fila['tamano'] ?? 0);
    }
}
