<nav aria-label="Ruta"><ol class="breadcrumb bg-transparent px-0">
  <li class="breadcrumb-item"><a href="<?= base_url('admin/usuarios') ?>">Administración</a></li>
  <li class="breadcrumb-item active" aria-current="page">Galería de <?= esc($usuario['name']) ?></li>
</ol></nav>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><i class="bi bi-images text-marca"></i> Galería de <?= esc($usuario['name']) ?>
    <small class="text-muted">(<span id="conteoVisible"><?= count($imagenes) ?></span>)</small></h1>
  <div class="input-group input-group-sm mt-2 mt-md-0" style="width: 220px;">
    <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span></div>
    <input type="search" class="form-control" id="buscar" placeholder="Buscar por título" aria-label="Buscar por título">
  </div>
</div>

<div class="alert alert-warning small"><i class="bi bi-shield-lock"></i> Estás viendo la galería de otro usuario como administrador. Puedes borrar imágenes que no cumplan las reglas del sitio.</div>

<?php if (count($imagenes) === 0): ?>
  <div class="card"><div class="vacio"><i class="bi bi-image"></i>Este usuario todavía no tiene imágenes.</div></div>
<?php else: ?>
  <div class="card d-none" id="sinResultados"><div class="vacio"><i class="bi bi-search"></i>Ninguna imagen coincide con la búsqueda.</div></div>
  <div class="row" id="galeria">
    <?php foreach ($imagenes as $img): ?>
      <div class="col-6 col-md-4 col-xl-3 mb-4 item-galeria" data-titulo="<?= esc($img['titulo']) ?>">
        <div class="card h-100 tarjeta-imagen">
          <img src="<?= base_url('imagenes/miniatura/' . $img['id']) ?>" alt="<?= esc($img['titulo']) ?>" class="miniatura" loading="lazy"
               tabindex="0" role="button" data-src="<?= base_url('imagenes/ver/' . $img['id']) ?>" data-titulo="<?= esc($img['titulo']) ?>">
          <div class="card-body p-2">
            <div class="font-weight-bold text-truncate small"><?= esc($img['titulo']) ?></div>
            <div class="text-muted" style="font-size: .75rem;"><?= formato_tamano($img['tamano']) ?> · <?= date('d/m/Y', strtotime($img['fecha'])) ?></div>
          </div>
          <div class="card-footer border-0 p-2 text-right">
            <form action="<?= base_url('admin/imagenes/borrar/' . $img['id']) ?>" method="post"
                  onsubmit="return confirm('¿Borrar la imagen &quot;<?= esc($img['titulo'], 'js') ?>&quot; de <?= esc($usuario['name'], 'js') ?>?');">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Borrar</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?= view('partials/visor') ?>
