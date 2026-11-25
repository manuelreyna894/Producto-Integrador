<?php
namespace App\Controllers;

use App\Models\AlbumModel;
use App\Models\ImagenModel;

/*
 * CONTROLADOR de la galería de imágenes (lado del servidor)
 * Cada usuario solo puede ver y administrar SU galería: todas las
 * consultas filtran por el id del usuario guardado en la sesión.
 * Las imágenes se guardan en writable/uploads/imagenes/<id del usuario>,
 * fuera de la carpeta pública, y se entregan a través de este controlador.
 */
class Imagenes extends BaseController
{
    protected $helpers = ['archivos'];

    /* Galería: filtro por álbum y orden llegan por GET (?album=3&orden=nombre) */
    public function index()
    {
        $user  = session()->get('user');
        $album = $this->request->getGet('album') ?? 'todas';
        $orden = $this->request->getGet('orden') ?? 'recientes';

        if (! array_key_exists($orden, ImagenModel::ORDENES))
        {
            $orden = 'recientes';
        }

        $albumModel = new AlbumModel();

        $data['imagenes']  = (new ImagenModel())->listar($user, $album, $orden);
        $data['albumes']   = $albumModel->conConteo($user);
        $data['total']     = (new ImagenModel())->where('user', $user)->countAllResults();
        $data['sin_album'] = (new ImagenModel())->contarSinAlbum($user);
        $data['album']     = $album;
        $data['orden']     = $orden;
        $data['ordenes']   = ImagenModel::ORDENES;

        $data['titulo_seccion'] = 'Todas las imágenes';
        if ($album === 'sin')
        {
            $data['titulo_seccion'] = 'Sin álbum';
        }
        elseif (ctype_digit((string) $album) && ($a = $albumModel->buscar($album, $user)))
        {
            $data['titulo_seccion'] = $a['nombre'];
        }

        echo view('header', ['titulo' => 'Mi galería']);
        echo view('Imagenes/index', $data);
        echo view('footer', ['scripts' => ['galeria.js']]);   // JavaScript del cliente
    }

    /*
     * AGREGAR una o varias imágenes.
     * Si la petición viene del JavaScript (AJAX) se responde en JSON;
     * si viene de un formulario normal, se redirige con un mensaje.
     */
    public function subir()
    {
        $volver = base_url('imagenes') . '?album=' . urlencode($this->request->getPost('volver_album') ?? 'todas');

        $reglas = [
            'imagenes' => 'uploaded[imagenes]|is_image[imagenes]|mime_in[imagenes,image/jpg,image/jpeg,image/png,image/gif,image/webp]|max_size[imagenes,5120]',
        ];
        $mensajes = [
            'imagenes' => [
                'uploaded' => 'Selecciona al menos una imagen.',
                'is_image' => 'Solo se permiten imágenes.',
                'mime_in'  => 'Formatos permitidos: JPG, PNG, GIF y WEBP.',
                'max_size' => 'Cada imagen debe pesar menos de 5 MB.',
            ],
        ];

        if (! $this->validate($reglas, $mensajes))
        {
            return $this->responder(false, $volver, null, $this->validator->getErrors());
        }

        $user  = session()->get('user');
        $album = $this->request->getPost('album');
        if (! ctype_digit((string) $album) || ! (new AlbumModel())->buscar($album, $user))
        {
            $album = null;
        }

        $model   = new ImagenModel();
        $carpeta = ImagenModel::carpeta($user);
        if (! is_dir($carpeta . 'miniaturas'))
        {
            mkdir($carpeta . 'miniaturas', 0755, true);
        }

        $subidas = 0;
        foreach ($this->request->getFileMultiple('imagenes') as $archivo)
        {
            if (! $archivo->isValid() || $archivo->hasMoved() || $subidas >= 20)
            {
                continue;
            }

            $tipo   = $archivo->getMimeType();
            $tamano = $archivo->getSize();
            $titulo = pathinfo($archivo->getClientName(), PATHINFO_FILENAME);
            $nombre = $archivo->getRandomName();

            $archivo->move($carpeta, $nombre);
            [$ancho, $alto] = getimagesize($carpeta . $nombre);

            // Miniatura de 400 x 300 con la librería de imágenes de CodeIgniter (GD)
            try
            {
                \Config\Services::image()
                    ->withFile($carpeta . $nombre)
                    ->fit(400, 300, 'center')
                    ->save($carpeta . 'miniaturas/' . $nombre, 80);
            }
            catch (\Throwable $e)
            {
                // Si falla, se mostrará la imagen original
            }

            $model->insert([
                'user'    => $user,
                'album'   => $album,
                'titulo'  => mb_substr($titulo, 0, 150),
                'archivo' => $nombre,
                'tipo'    => $tipo,
                'tamano'  => $tamano,
                'ancho'   => $ancho,
                'alto'    => $alto,
            ]);
            $subidas++;
        }

        $texto = $subidas === 1 ? 'Se agregó 1 imagen.' : "Se agregaron $subidas imágenes.";
        return $this->responder(true, $volver, $texto);
    }

    // Respuesta para AJAX (JSON) o para formulario normal (redirección con mensaje)
    private function responder($ok, $url, $mensaje = null, $errores = [])
    {
        if ($ok)
        {
            session()->setFlashdata('mensaje', $mensaje);
        }

        if ($this->request->isAJAX())
        {
            return $this->response->setJSON([
                'ok'      => $ok,
                'url'     => $url,
                'mensaje' => $mensaje,
                'errores' => array_values($errores),
            ])->setStatusCode($ok ? 200 : 422);
        }

        return $ok ? redirect()->to($url) : redirect()->to($url)->with('errores', $errores);
    }

    /* Entregar la imagen original o su miniatura */
    public function ver($id)
    {
        return $this->enviarImagen($id, false);
    }

    public function miniatura($id)
    {
        return $this->enviarImagen($id, true);
    }

    private function enviarImagen($id, $miniatura)
    {
        $model = new ImagenModel();

        // El dueño ve sus imágenes; el administrador puede ver las de cualquiera
        $imagen = session()->get('rol') === 'admin'
            ? $model->find($id)
            : $model->buscar($id, session()->get('user'));

        if (! $imagen)
        {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $carpeta = ImagenModel::carpeta($imagen['user']);
        $ruta    = $carpeta . $imagen['archivo'];
        if ($miniatura && is_file($carpeta . 'miniaturas/' . $imagen['archivo']))
        {
            $ruta = $carpeta . 'miniaturas/' . $imagen['archivo'];
        }

        if (! is_file($ruta))
        {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', $imagen['tipo'])
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($ruta));
    }

    /* ORGANIZAR: cambiar título y álbum */
    public function editar($id)
    {
        $user   = session()->get('user');
        $model  = new ImagenModel();
        $imagen = $model->buscar($id, $user);
        $volver = base_url('imagenes') . '?album=' . urlencode($this->request->getPost('volver_album') ?? 'todas');

        if (! $imagen)
        {
            return redirect()->to($volver)->with('error', 'La imagen no existe.');
        }

        if (! $this->validate(['titulo' => 'required|max_length[150]'], ['titulo' => ['required' => 'El título no puede quedar vacío.', 'max_length' => 'El título es demasiado largo.']]))
        {
            return redirect()->to($volver)->with('errores', $this->validator->getErrors());
        }

        $album = $this->request->getPost('album');
        if (! ctype_digit((string) $album) || ! (new AlbumModel())->buscar($album, $user))
        {
            $album = null;
        }

        $model->update($id, ['titulo' => $this->request->getPost('titulo'), 'album' => $album]);

        return redirect()->to($volver)->with('mensaje', 'Imagen actualizada.');
    }

    /* BORRAR una imagen propia */
    public function borrar($id)
    {
        $model  = new ImagenModel();
        $imagen = $model->buscar($id, session()->get('user'));
        $volver = base_url('imagenes') . '?album=' . urlencode($this->request->getPost('volver_album') ?? 'todas');

        if (! $imagen)
        {
            return redirect()->to($volver)->with('error', 'La imagen no existe.');
        }

        $model->borrarImagen($imagen);

        return redirect()->to($volver)->with('mensaje', '"' . $imagen['titulo'] . '" se eliminó.');
    }

    /* ORGANIZAR: crear un álbum */
    public function crearAlbum()
    {
        if (! $this->validate(['nombre' => 'required|max_length[100]'], ['nombre' => ['required' => 'Escribe el nombre del álbum.', 'max_length' => 'El nombre es demasiado largo.']]))
        {
            return redirect()->to(base_url('imagenes'))->with('errores', $this->validator->getErrors());
        }

        $id = (new AlbumModel())->insert([
            'user'   => session()->get('user'),
            'nombre' => $this->request->getPost('nombre'),
        ]);

        return redirect()->to(base_url('imagenes') . '?album=' . $id)->with('mensaje', 'Álbum creado.');
    }

    /* ORGANIZAR: borrar un álbum (sus imágenes quedan "sin álbum") */
    public function borrarAlbum($id)
    {
        $user  = session()->get('user');
        $model = new AlbumModel();
        $album = $model->buscar($id, $user);

        if (! $album)
        {
            return redirect()->to(base_url('imagenes'))->with('error', 'El álbum no existe.');
        }

        \Config\Database::connect()->table('imagen')
            ->where(['album' => $id, 'user' => $user])
            ->update(['album' => null]);

        $model->delete($id);

        return redirect()->to(base_url('imagenes'))->with('mensaje', 'Se eliminó el álbum "' . $album['nombre'] . '". Sus imágenes quedaron en "Sin álbum".');
    }
}
