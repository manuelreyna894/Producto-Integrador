<?php
namespace App\Controllers;

use App\Models\ArchivoModel;
use App\Models\DocumentoModel;
use App\Models\ImagenModel;
use App\Models\PublicationModel;

/*
 * CONTROLADOR del panel principal (página después de iniciar sesión)
 * Muestra un resumen de la cuenta y accesos a cada sección.
 */
class Panel extends BaseController
{
    protected $helpers = ['archivos'];

    public function index()
    {
        $user = session()->get('user');

        // Cada modelo cuenta los registros del usuario
        $data['total_publicaciones'] = (new PublicationModel())->where('user', $user)->countAllResults();
        $data['total_archivos']      = (new ArchivoModel())->where('user', $user)->countAllResults();
        $data['total_documentos']    = (new DocumentoModel())->where('user', $user)->countAllResults();
        $data['total_imagenes']      = (new ImagenModel())->where('user', $user)->countAllResults();
        $data['espacio']             = (new ArchivoModel())->espacioUsado($user)
                                     + (int) ((new ImagenModel())->selectSum('tamano')->where('user', $user)->first()['tamano'] ?? 0);

        // Las 6 imágenes más recientes para mostrarlas en el panel
        $data['recientes'] = (new ImagenModel())->listar($user, 'todas', 'recientes');
        $data['recientes'] = array_slice($data['recientes'], 0, 6);

        echo view('header', ['titulo' => 'Panel']);
        echo view('Panel/index', $data);
        echo view('footer');
    }
}
