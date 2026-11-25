<?php
namespace App\Controllers;
use App\Models\PublicationModel;

/*
 * CONTROLADOR de la entidad "publication"
 * Leer es público; publicar, editar y borrar requieren sesión (filtro Auth)
 * y solo el autor puede editar o borrar sus publicaciones.
 */
class Publication extends BaseController
{
    public function index()
    {
        $data['posts'] = (new PublicationModel())->show();

        echo view('header', ['titulo' => 'Publicaciones']);
        echo view('Publication/all', $data);
        echo view('footer');
    }

    public function add()
    {
        if ($this->validate(['content' => 'required|max_length[2000]']))
        {
            (new PublicationModel())->save([
                'content' => $this->request->getPost('content'),
                'user'    => session()->get('user'),
            ]);
            return redirect()->to(base_url('publication'))->with('mensaje', 'Publicación creada.');
        }

        return redirect()->to(base_url('publication'))->with('error', 'Escribe algo antes de publicar.');
    }

    public function edit($id)
    {
        $model = new PublicationModel();
        $post  = $model->get($id);

        if ($post === null || ! $this->esAutor($post))
        {
            return redirect()->to(base_url('publication'))->with('error', 'No tienes permiso para editar esa publicación.');
        }

        if ($this->request->is('post') && $this->validate(['content' => 'required|max_length[2000]']))
        {
            $model->save(['id' => $id, 'content' => $this->request->getPost('content')]);
            return redirect()->to(base_url('publication'))->with('mensaje', 'Publicación actualizada.');
        }

        echo view('header', ['titulo' => 'Editar publicación']);
        echo view('Publication/edit', ['post' => $post]);
        echo view('footer');
    }

    public function delete($id)
    {
        $model = new PublicationModel();
        $post  = $model->get($id);

        // El autor o el administrador pueden borrar la publicación
        if ($post === null || (! $this->esAutor($post) && session()->get('rol') !== 'admin'))
        {
            return redirect()->to(base_url('publication'))->with('error', 'No tienes permiso para borrar esa publicación.');
        }

        $model->delete($id);
        return redirect()->to(base_url('publication'))->with('mensaje', 'Publicación eliminada.');
    }

    private function esAutor($post)
    {
        return (int) session()->get('user') === (int) $post['user'];
    }
}
