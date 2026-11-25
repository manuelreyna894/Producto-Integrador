<?php
// Texto con singular o plural: 1 imagen, 2 imágenes
$cuenta  = fn ($n, $uno, $varios) => $n . ' ' . ($n == 1 ? $uno : $varios);
$esAdmin = session()->get('rol') === 'admin';

// Tarjetas de acceso a cada sección: [url, ícono, color, título, descripción, total]
$secciones = [
    ['imagenes',    'bi-images',            'marca',   'Mi galería',     'Agrega, organiza en álbumes y administra tus imágenes.',  $cuenta($total_imagenes, 'imagen', 'imágenes')],
    ['archivos',    'bi-folder2-open',      'primary', 'Mis archivos',   'Sube, consulta, descarga y borra tus archivos.',           $cuenta($total_archivos, 'archivo', 'archivos')],
    ['documentos',  'bi-file-earmark-text', 'success', 'Mis documentos', 'Crea documentos y descárgalos en Word, Excel, PDF y más.', $cuenta($total_documentos, 'documento', 'documentos')],
    ['publication', 'bi-chat-square-text',  'info',    'Publicaciones',  'Comparte publicaciones con los demás usuarios.',          $cuenta($total_publicaciones, 'publicación tuya', 'publicaciones tuyas')],
    ['cuenta',      'bi-gear',              'secondary', 'Mi cuenta',    'Cambia tu nombre o tu contraseña.',                       'Configurar'],
];
if ($esAdmin)
{
    $secciones[] = ['admin/usuarios', 'bi-shield-lock', 'warning', 'Administración', 'Modifica o borra los datos de cualquier usuario.', 'Solo administrador'];
}
?>

<!-- Saludo con datos de la SESIÓN y de la COOKIE de último acceso -->
<div class="mb-4">
  <h1 class="h3 mb-1">Hola, <?= esc(session()->get('name')) ?></h1>
  <p class="text-muted mb-0">
    <?php if (session()->get('acceso_anterior')): ?>
      Tu acceso anterior fue el <?= date('d/m/Y \a \l\a\s H:i', (int) session()->get('acceso_anterior')) ?>.
    <?php else: ?>
      Bienvenido. Este es tu primer acceso desde este equipo.
    <?php endif; ?>
    Espacio utilizado: <strong><?= formato_tamano($espacio) ?></strong>.
  </p>
</div>

<div class="row">
  <?php foreach ($secciones as [$url, $icono, $color, $nombre, $descripcion, $total]): ?>
    <div class="col-sm-6 col-lg-4 mb-4">
      <a href="<?= base_url($url) ?>" class="card h-100 tarjeta-enlace">
        <div class="card-body d-flex">
          <i class="bi <?= $icono ?> text-<?= $color ?> icono-grande mr-3" aria-hidden="true"></i>
          <div>
            <h2 class="h5 mb-1"><?= $nombre ?></h2>
            <p class="small text-muted mb-2"><?= $descripcion ?></p>
            <span class="badge badge-light border"><?= $total ?></span>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h5 mb-0">Imágenes recientes</h2>
      <a href="<?= base_url('imagenes') ?>" class="small">Ir a mi galería <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if (count($recientes) > 0): ?>
      <div class="row">
        <?php foreach ($recientes as $img): ?>
          <div class="col-4 col-md-2 mb-2">
            <a href="<?= base_url('imagenes') ?>"><img src="<?= base_url('imagenes/miniatura/' . $img['id']) ?>" alt="<?= esc($img['titulo']) ?>" title="<?= esc($img['titulo']) ?>" class="miniatura rounded" style="height: 90px;"></a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted mb-0">Aún no tienes imágenes. <a href="<?= base_url('imagenes') ?>">Agrega la primera</a>.</p>
    <?php endif; ?>
  </div>
</div>
