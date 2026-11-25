<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/*
 * FILTRO de administrador
 * Protege las rutas de /admin: solo deja pasar a usuarios con rol "admin".
 * Se aplica después del filtro Auth (que revisa que haya sesión).
 */
class Admin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('rol') !== 'admin')
        {
            session()->setFlashdata('error', 'Esa sección es solo para el administrador.');
            return redirect()->to(base_url('panel'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
