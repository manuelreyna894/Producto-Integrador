<?php
// Sección actual (primer segmento de la url) para resaltar el menú
$seccion = service('uri')->getSegment(1);
$activo  = fn ($s) => $seccion === $s ? ' active' : '';
$esAdmin = session()->get('rol') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Mi Galería: administra tus imágenes desde cualquier dispositivo.">
  <title><?= esc($titulo ?? 'Inicio') ?> · Mi Galería</title>

  <!-- Bootstrap 4, íconos y estilos propios -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body>

<!-- ===== Menú de navegación (el mismo en todas las páginas) ===== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-marca shadow-sm">
  <div class="container">
    <a class="navbar-brand font-weight-bold" href="<?= base_url(session()->has('user') ? 'panel' : '') ?>">
      <i class="bi bi-images"></i> Mi Galería
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Abrir menú">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav mr-auto">
        <?php if (session()->has('user')): ?>
          <li class="nav-item<?= $activo('panel') ?>"><a class="nav-link" href="<?= base_url('panel') ?>"><i class="bi bi-house"></i> Inicio</a></li>
          <li class="nav-item<?= $activo('imagenes') ?>"><a class="nav-link" href="<?= base_url('imagenes') ?>"><i class="bi bi-images"></i> Mi galería</a></li>
          <li class="nav-item<?= $activo('archivos') ?>"><a class="nav-link" href="<?= base_url('archivos') ?>"><i class="bi bi-folder2-open"></i> Archivos</a></li>
          <li class="nav-item<?= $activo('documentos') ?>"><a class="nav-link" href="<?= base_url('documentos') ?>"><i class="bi bi-file-earmark-text"></i> Documentos</a></li>
        <?php endif; ?>
        <li class="nav-item<?= $activo('publication') ?>"><a class="nav-link" href="<?= base_url('publication') ?>"><i class="bi bi-chat-square-text"></i> Publicaciones</a></li>
        <?php if ($esAdmin): ?>
          <li class="nav-item<?= $activo('admin') ?>"><a class="nav-link" href="<?= base_url('admin/usuarios') ?>"><i class="bi bi-shield-lock"></i> Administración</a></li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav">
        <?php if (session()->has('user')): ?>
          <!-- Datos del usuario guardados en la SESIÓN -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" id="menuUsuario" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bi bi-person-circle"></i> <?= esc(session()->get('name')) ?>
              <?php if ($esAdmin): ?><span class="badge badge-warning ml-1">Admin</span><?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="menuUsuario">
              <a class="dropdown-item" href="<?= base_url('cuenta') ?>"><i class="bi bi-gear"></i> Mi cuenta</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" href="<?= base_url('user/logout') ?>"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
            </div>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-light btn-sm text-marca" href="<?= base_url() ?>"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  <?= view('partials/alertas') ?>
