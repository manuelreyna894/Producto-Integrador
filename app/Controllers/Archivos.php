<?php
namespace App\Controllers;

use App\Libraries\GeneradorDocumentos;
use App\Models\ArchivoModel;

/*
 * CONTROLADOR de administración de archivos
 * (lo que hicimos en las actividades 2.3, Integradora 2 y 3.1, ahora en MVC)
 * Los archivos se guardan en writable/uploads/archivos/<id del usuario>,
 * una carpeta que NO es pública: solo se pueden ver a través de este
 * controlador, que revisa que el archivo sea del usuario en sesión.
 */
class Archivos extends BaseController
{
    protected $helpers = ['archivos'];

    // Extensiones permitidas (se bloquean archivos peligrosos como .php o .exe)
    private const EXTENSIONES = 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,zip';

    private function carpeta()
    {
        return WRITEPATH . 'uploads/archivos/' . session()->get('user') . '/';
    }

    public function index()
    {
        $model = new ArchivoModel();
        $user  = session()->get('user');

        $data['archivos'] = $model->delUsuario($user);
        $data['espacio']  = $model->espacioUsado($user);
        $data['formatos'] = GeneradorDocumentos::FORMATOS;

        echo view('header', ['titulo' => 'Mis archivos']);
        echo view('Archivos/index', $data);
        echo view('footer');
    }

    /* Agregar un archivo */
    public function subir()
    {
        $reglas = [
            'archivo' => 'uploaded[archivo]|max_size[archivo,10240]|ext_in[archivo,' . self::EXTENSIONES . ']',
        ];
        $mensajes = [
            'archivo' => [
                'uploaded' => 'Selecciona un archivo para subir.',
                'max_size' => 'El archivo no debe pesar más de 10 MB.',
                'ext_in'   => 'Ese tipo de archivo no está permitido.',
            ],
        ];

        if (! $this->validate($reglas, $mensajes))
        {
            return redirect()->to(base_url('archivos'))->with('errores', $this->validator->getErrors());
        }

        $archivo = $this->request->getFile('archivo');

        // Datos que se guardan en la base de datos (metadatos)
        $datos = [
            'user'            => session()->get('user'),
            'nombre_original' => $archivo->getClientName(),
            'nombre_guardado' => $archivo->getRandomName(),   // Nombre aleatorio para evitar choques
            'tipo'            => $archivo->getMimeType(),
            'tamano'          => $archivo->getSize(),
        ];

        $archivo->move($this->carpeta(), $datos['nombre_guardado']);
        (new ArchivoModel())->save($datos);

        return redirect()->to(base_url('archivos'))->with('mensaje', '"' . $datos['nombre_original'] . '" se subió correctamente.');
    }

    /* Ver un archivo en el navegador (imágenes, PDF y texto) */
    public function ver($id)
    {
        $archivo = (new ArchivoModel())->buscar($id, session()->get('user'));
        $ruta    = $archivo ? $this->carpeta() . $archivo['nombre_guardado'] : null;

        if (! $archivo || ! is_file($ruta))
        {
            return redirect()->to(base_url('archivos'))->with('error', 'El archivo no existe.');
        }

        $visible = strpos($archivo['tipo'], 'image/') === 0 || in_array($archivo['tipo'], ['application/pdf', 'text/plain']);
        if (! $visible)
        {
            return $this->descargar($id);   // Los demás tipos se descargan
        }

        $nombre = str_replace('"', '', $archivo['nombre_original']);

        return $this->response
            ->setHeader('Content-Type', $archivo['tipo'])
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombre . '"')
            ->setBody(file_get_contents($ruta));
    }

    /* Descargar un archivo con su nombre original */
    public function descargar($id)
    {
        $archivo = (new ArchivoModel())->buscar($id, session()->get('user'));
        $ruta    = $archivo ? $this->carpeta() . $archivo['nombre_guardado'] : null;

        if (! $archivo || ! is_file($ruta))
        {
            return redirect()->to(base_url('archivos'))->with('error', 'El archivo no existe.');
        }

        return $this->response->download($ruta, null)->setFileName($archivo['nombre_original']);
    }

    /* Borrar un archivo (del disco y de la base de datos) */
    public function borrar($id)
    {
        $model   = new ArchivoModel();
        $archivo = $model->buscar($id, session()->get('user'));

        if (! $archivo)
        {
            return redirect()->to(base_url('archivos'))->with('error', 'El archivo no existe.');
        }

        $ruta = $this->carpeta() . $archivo['nombre_guardado'];
        if (is_file($ruta))
        {
            unlink($ruta);
        }
        $model->delete($id);

        return redirect()->to(base_url('archivos'))->with('mensaje', '"' . $archivo['nombre_original'] . '" se eliminó.');
    }

    /* Reporte de los archivos del usuario en Word, Excel, PDF, imagen o JSON */
    public function reporte($formato)
    {
        if (! array_key_exists($formato, GeneradorDocumentos::FORMATOS))
        {
            return redirect()->to(base_url('archivos'))->with('error', 'Formato no válido.');
        }

        $model    = new ArchivoModel();
        $user     = session()->get('user');
        $archivos = $model->delUsuario($user);

        $tabla = [['No.', 'Archivo', 'Tipo', 'Tamaño', 'Fecha de subida']];
        foreach ($archivos as $i => $a)
        {
            $tabla[] = [$i + 1, $a['nombre_original'], $a['tipo'], formato_tamano($a['tamano']), date('d/m/Y H:i', strtotime($a['fecha']))];
        }

        $doc = [
            'titulo'    => 'Reporte de mis archivos',
            'subtitulo' => 'Usuario: ' . session()->get('name') . '  |  Fecha del reporte: ' . date('d/m/Y H:i'),
            'parrafos'  => ['Total de archivos: ' . count($archivos), 'Espacio utilizado: ' . formato_tamano($model->espacioUsado($user))],
            'tabla'     => $tabla,
        ];

        try
        {
            $resultado = (new GeneradorDocumentos())->generar($formato, $doc);
        }
        catch (\RuntimeException $e)
        {
            return redirect()->to(base_url('archivos'))->with('error', $e->getMessage());
        }

        return $this->response->download($resultado['nombre'], $resultado['contenido']);
    }
}
