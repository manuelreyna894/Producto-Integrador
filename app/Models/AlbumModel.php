<?php namespace App\Models;

use CodeIgniter\Model;

/*
 * MODELO de la entidad "album" (para organizar las imágenes)
 */
class AlbumModel extends Model
{
    protected $table         = 'album';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user', 'nombre'];
    protected $returnType    = 'array';

    /*
     * Álbumes del usuario con el número de imágenes de cada uno:
     * SELECT album.*, COUNT(imagen.id) AS total FROM album
     * LEFT JOIN imagen ON imagen.album = album.id
     * WHERE album.user = ? GROUP BY album.id ORDER BY album.nombre
     */
    public function conConteo($user)
    {
        return $this->select('album.*, COUNT(imagen.id) AS total')
            ->join('imagen', 'imagen.album = album.id', 'left')
            ->where('album.user', $user)
            ->groupBy('album.id')
            ->orderBy('album.nombre', 'ASC')
            ->findAll();
    }

    public function buscar($id, $user)
    {
        return $this->where(['id' => $id, 'user' => $user])->first();
    }
}
