<?php namespace App\Models;

use CodeIgniter\Model;

/*
 * MODELO de la entidad "imagen"
 */
class ImagenModel extends Model
{
    protected $table         = 'imagen';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user', 'album', 'titulo', 'archivo', 'tipo', 'tamano', 'ancho', 'alto'];
    protected $returnType    = 'array';

    // Opciones para ordenar la galería: clave => [columna, dirección, texto]
    public const ORDENES = [
        'recientes' => ['imagen.id', 'DESC', 'Más recientes'],
        'antiguas'  => ['imagen.id', 'ASC', 'Más antiguas'],
        'nombre'    => ['imagen.titulo', 'ASC', 'Nombre (A-Z)'],
        'tamano'    => ['imagen.tamano', 'DESC', 'Tamaño (mayor primero)'],
    ];

    // Carpeta (fuera de la parte pública) donde se guardan las imágenes de un usuario
    public static function carpeta($user)
    {
        return WRITEPATH . 'uploads/imagenes/' . (int) $user . '/';
    }

    /*
     * Imágenes del usuario con el nombre de su álbum.
     * $album: 'todas', 'sin' (sin álbum) o el id de un álbum
     */
    public function listar($user, $album = 'todas', $orden = 'recientes')
    {
        $this->select('imagen.*, album.nombre AS album_nombre')
             ->join('album', 'album.id = imagen.album', 'left')
             ->where('imagen.user', $user);

        if ($album === 'sin')
        {
            $this->where('imagen.album', null);
        }
        elseif (ctype_digit((string) $album))
        {
            $this->where('imagen.album', (int) $album);
        }

        [$columna, $direccion] = self::ORDENES[$orden] ?? self::ORDENES['recientes'];

        return $this->orderBy($columna, $direccion)->findAll();
    }

    // Una imagen, solo si pertenece al usuario
    public function buscar($id, $user)
    {
        return $this->where(['id' => $id, 'user' => $user])->first();
    }

    public function contarSinAlbum($user)
    {
        return $this->where('user', $user)->where('album', null)->countAllResults();
    }

    // Borra una imagen: el archivo original, su miniatura y el registro
    public function borrarImagen(array $imagen)
    {
        $carpeta = self::carpeta($imagen['user']);

        foreach ([$carpeta . $imagen['archivo'], $carpeta . 'miniaturas/' . $imagen['archivo']] as $ruta)
        {
            if (is_file($ruta))
            {
                unlink($ruta);
            }
        }

        return $this->delete($imagen['id']);
    }
}
