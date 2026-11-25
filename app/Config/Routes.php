<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

/*
 * RUTAS de la aplicación: dirección => Controlador::método
 * (:num) recibe un número (id) y (:alpha) una palabra (formato).
 */

// ---------- Rutas públicas ----------
$routes->get('/', 'Home::index');                  // Inicio de sesión
$routes->post('user/login', 'User::login');
$routes->get('user/logout', 'User::logout');
$routes->get('publication', 'Publication::index');

// ---------- Rutas para cualquier usuario con sesión (filtro Auth) ----------
$routes->group('', ['filter' => \App\Filters\Auth::class], static function ($routes) {

    $routes->get('panel', 'Panel::index');

    // Mi cuenta (cada usuario edita sus propios datos)
    $routes->match(['GET', 'POST'], 'cuenta', 'User::cuenta');

    // Publicaciones
    $routes->post('publication/add', 'Publication::add');
    $routes->match(['GET', 'POST'], 'publication/edit/(:num)', 'Publication::edit/$1');
    $routes->post('publication/delete/(:num)', 'Publication::delete/$1');

    // Galería de imágenes (cada usuario solo ve la suya)
    $routes->get('imagenes', 'Imagenes::index');
    $routes->post('imagenes/subir', 'Imagenes::subir');
    $routes->get('imagenes/ver/(:num)', 'Imagenes::ver/$1');
    $routes->get('imagenes/miniatura/(:num)', 'Imagenes::miniatura/$1');
    $routes->post('imagenes/editar/(:num)', 'Imagenes::editar/$1');
    $routes->post('imagenes/borrar/(:num)', 'Imagenes::borrar/$1');
    $routes->post('imagenes/album/crear', 'Imagenes::crearAlbum');
    $routes->post('imagenes/album/borrar/(:num)', 'Imagenes::borrarAlbum/$1');

    // Archivos
    $routes->get('archivos', 'Archivos::index');
    $routes->post('archivos/subir', 'Archivos::subir');
    $routes->get('archivos/ver/(:num)', 'Archivos::ver/$1');
    $routes->get('archivos/descargar/(:num)', 'Archivos::descargar/$1');
    $routes->post('archivos/borrar/(:num)', 'Archivos::borrar/$1');
    $routes->get('archivos/reporte/(:alpha)', 'Archivos::reporte/$1');

    // Documentos
    $routes->get('documentos', 'Documentos::index');
    $routes->post('documentos/crear', 'Documentos::crear');
    $routes->get('documentos/descargar/(:num)/(:alpha)', 'Documentos::descargar/$1/$2');
    $routes->post('documentos/borrar/(:num)', 'Documentos::borrar/$1');

    // ---------- Administración (además, filtro Admin: solo rol "admin") ----------
    $routes->group('admin', ['filter' => \App\Filters\Admin::class], static function ($routes) {
        $routes->get('usuarios', 'Admin::usuarios');
        $routes->post('usuarios/crear', 'Admin::crear');
        $routes->match(['GET', 'POST'], 'usuarios/editar/(:num)', 'Admin::editar/$1');
        $routes->post('usuarios/borrar/(:num)', 'Admin::borrar/$1');
        $routes->get('usuarios/(:num)/galeria', 'Admin::galeria/$1');
        $routes->post('imagenes/borrar/(:num)', 'Admin::borrarImagen/$1');
    });
});
