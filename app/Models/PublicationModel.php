<?php namespace App\Models;

use CodeIgniter\Model;

/*
 * MODELO de la entidad "publication"
 */
class PublicationModel extends Model
{
    protected $table         = 'publication';        // Corresponde a la tabla en la base de datos
    protected $primaryKey    = 'id';
    protected $allowedFields = ['content', 'user'];   // Los campos que podemos modificar
    protected $returnType    = 'array';

    /*
     * Regresa todas las publicaciones, o solo una si se envía su id
     */
    public function get($id = false)
    {
        if ($id === false)
        {
            return $this->findAll();
        }

        return $this->asArray()->where(['id' => $id])->first();
    }

    /*
     * Regresa las publicaciones junto con el nombre de quien las escribió.
     * Crea la consulta:
     * SELECT publication.*, user.name FROM publication
     * LEFT JOIN user ON user.id = publication.user ORDER BY publication.id DESC
     */
    public function show()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('publication');
        $builder->select('publication.*, user.name');
        $builder->join('user', 'user.id = publication.user', 'left');
        $builder->orderBy('publication.id', 'DESC');

        return $builder->get()->getResultArray();
    }
}
