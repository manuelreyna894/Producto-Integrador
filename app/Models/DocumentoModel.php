<?php namespace App\Models;

use CodeIgniter\Model;

/*
 * MODELO de la entidad "documento"
 */
class DocumentoModel extends Model
{
    protected $table         = 'documento';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user', 'titulo', 'tipo', 'contenido'];
    protected $returnType    = 'array';

    // Tipos de documento permitidos
    public const TIPOS = ['Nota', 'Aviso', 'Carta', 'Reporte'];

    public function delUsuario($user)
    {
        return $this->where('user', $user)->orderBy('id', 'DESC')->findAll();
    }

    public function buscar($id, $user)
    {
        return $this->where(['id' => $id, 'user' => $user])->first();
    }
}
