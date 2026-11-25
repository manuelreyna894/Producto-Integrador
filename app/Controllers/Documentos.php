<?php
namespace App\Controllers;

use App\Libraries\GeneradorDocumentos;
use App\Models\DocumentoModel;

/*
 * CONTROLADOR del panel de creación de documentos
 * (lo que hicimos en la Integradora 3, ahora en MVC)
 * El usuario escribe un documento, se guarda en la base de datos
 * y se puede descargar en cualquier formato.
 */
class Documentos extends BaseController
{
    public function index()
    {
        $data['documentos'] = (new DocumentoModel())->delUsuario(session()->get('user'));
        $data['tipos']      = DocumentoModel::TIPOS;
        $data['formatos']   = GeneradorDocumentos::FORMATOS;

        echo view('header', ['titulo' => 'Mis documentos']);
        echo view('Documentos/index', $data);
        echo view('footer');
    }

    /* Crear un documento y preparar su descarga */
    public function crear()
    {
        $reglas = [
            'titulo'    => 'required|max_length[150]',
            'tipo'      => 'required|in_list[' . implode(',', DocumentoModel::TIPOS) . ']',
            'contenido' => 'required|max_length[5000]',
            'formato'   => 'required|in_list[' . implode(',', array_keys(GeneradorDocumentos::FORMATOS)) . ']',
        ];
        $mensajes = [
            'titulo'    => ['required' => 'Escribe un título.', 'max_length' => 'El título es demasiado largo.'],
            'tipo'      => ['required' => 'Elige el tipo de documento.', 'in_list' => 'Elige un tipo de documento válido.'],
            'contenido' => ['required' => 'Escribe el contenido del documento.', 'max_length' => 'El contenido es demasiado largo (máximo 5000 caracteres).'],
            'formato'   => ['required' => 'Elige un formato.', 'in_list' => 'Elige un formato válido.'],
        ];

        if (! $this->validate($reglas, $mensajes))
        {
            return redirect()->to(base_url('documentos'))->withInput()->with('errores', $this->validator->getErrors());
        }

        $model = new DocumentoModel();
        $id    = $model->insert([
            'user'      => session()->get('user'),
            'titulo'    => $this->request->getPost('titulo'),
            'tipo'      => $this->request->getPost('tipo'),
            'contenido' => $this->request->getPost('contenido'),
        ]);

        $formato = $this->request->getPost('formato');

        // La vista inicia la descarga automáticamente con esta dirección
        return redirect()->to(base_url('documentos'))
            ->with('mensaje', 'Documento creado. La descarga en ' . GeneradorDocumentos::FORMATOS[$formato] . ' empezará en un momento.')
            ->with('descargar', base_url("documentos/descargar/$id/$formato"));
    }

    /* Descargar un documento en el formato pedido */
    public function descargar($id, $formato)
    {
        $documento = (new DocumentoModel())->buscar($id, session()->get('user'));

        if (! $documento || ! array_key_exists($formato, GeneradorDocumentos::FORMATOS))
        {
            return redirect()->to(base_url('documentos'))->with('error', 'El documento o el formato no existen.');
        }

        $doc = [
            'titulo'    => $documento['titulo'],
            'subtitulo' => 'Tipo: ' . $documento['tipo'] . '  |  Fecha: ' . date('d/m/Y H:i', strtotime($documento['fecha']))
                         . '  |  Autor: ' . session()->get('name'),
            'parrafos'  => explode("\n", str_replace("\r", '', $documento['contenido'])),
        ];

        try
        {
            $resultado = (new GeneradorDocumentos())->generar($formato, $doc);
        }
        catch (\RuntimeException $e)
        {
            return redirect()->to(base_url('documentos'))->with('error', $e->getMessage());
        }

        return $this->response->download($resultado['nombre'], $resultado['contenido']);
    }

    public function borrar($id)
    {
        $model     = new DocumentoModel();
        $documento = $model->buscar($id, session()->get('user'));

        if (! $documento)
        {
            return redirect()->to(base_url('documentos'))->with('error', 'El documento no existe.');
        }

        $model->delete($id);
        return redirect()->to(base_url('documentos'))->with('mensaje', '"' . $documento['titulo'] . '" se eliminó.');
    }
}
