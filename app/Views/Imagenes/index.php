<?php
// Dirección de la galería conservando el álbum y el orden actuales
$url = fn ($album, $orden) => base_url('imagenes') . '?album=' . urlencode((string) $album) . '&orden=' . urlencode($orden);
$albumActual = ctype_digit((string) $album) ? (int) $album : null;
?>

<div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h3 mb-0"><i class="bi bi-images text-marca"></i> Mi galería</h1>
    <p class="text-muted small mb-0">Solo tú puedes ver estas imágenes.</p>
  </div>
</div>

<div class="row">
  <!-- ===================== ÁLBUMES ===================== -->
  <aside class="col-lg-3 mb-4 order-2 order-lg-1">
    <div class="card">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-muted">Álbumes</h2>
        <div class="list-group list-group-flush mb-3">
          <a href="<?= $url('todas', $orden) ?>" class="list-group-item list-group-item-action d-flex justify-content-between px-2<?= $album === 'todas' ? ' active' : '' ?>">
            <span><i class="bi bi-collection"></i> Todas</span><span class="badge badge-pill badge-light"><?= $total ?></span>
          </a>
          <a href="<?= $url('sin', $orden) ?>" class="list-group-item list-group-item-action d-flex justify-content-between px-2<?= $album === 'sin' ? ' active' : '' ?>">
            <span><i class="bi bi-inbox"></i> Sin álbum</span><span class="badge badge-pill badge-light"><?= $sin_album ?></span>
          </a>
          <?php foreach ($albumes as $a): ?>
            <a href="<?= $url($a['id'], $orden) ?>" class="list-group-item list-group-item-action d-flex justify-content-between px-2<?= $albumActual === (int) $a['id'] ? ' active' : '' ?>">
              <span class="text-truncate"><i class="bi bi-folder"></i> <?= esc($a['nombre']) ?></span><span class="badge badge-pill badge-light"><?= $a['total'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>

        <form action="<?= base_url('imagenes/album/crear') ?>" method="post">
          <?= csrf_field() ?>
          <label for="nuevo_album" class="small text-muted mb-1">Nuevo álbum</label>
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" id="nuevo_album" name="nombre" maxlength="100" placeholder="Ej. Vacaciones" required>
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit" title="Crear álbum" aria-label="Crear álbum"><i class="bi bi-plus-lg"></i></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </aside>

  <!-- ===================== GALERÍA ===================== -->
  <section class="col-lg-9 order-1 order-lg-2">

    <!-- AGREGAR imágenes: arrastrar y soltar o elegir (galeria.js) -->
    <div class="card mb-3">
      <div class="card-body">
        <form action="<?= base_url('imagenes/subir') ?>" method="post" enctype="multipart/form-data" id="formSubir">
          <?= csrf_field() ?>
          <input type="hidden" name="volver_album" value="<?= esc($album) ?>">

          <label class="zona-subida d-block mb-3 position-relative" id="zonaSubida" for="imagenes">
            <i class="bi bi-cloud-arrow-up"></i>
            <span class="d-none d-md-block font-weight-bold">Arrastra tus imágenes aquí o haz clic para elegirlas</span>
            <span class="d-block d-md-none font-weight-bold">Toca aquí para elegir tus imágenes</span>
            <span class="d-block small text-muted">JPG, PNG, GIF o WEBP · Máximo 5 MB por imagen · Hasta 20 a la vez</span>
            <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple required>
          </label>

          <!-- Vistas previas y errores generados en el navegador -->
          <div class="previas mb-3" id="previas" aria-live="polite"></div>
          <div class="alert alert-danger d-none" id="erroresSubida" role="alert"></div>

          <div class="form-row align-items-center">
            <div class="col-sm-6 mb-2 mb-sm-0">
              <label for="album_subir" class="sr-only">Guardar en</label>
              <select class="form-control" id="album_subir" name="album">
                <option value="">Guardar sin álbum</option>
                <?php foreach ($albumes as $a): ?>
                  <option value="<?= $a['id'] ?>" <?= $albumActual === (int) $a['id'] ? 'selected' : '' ?>>Guardar en: <?= esc($a['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-6 text-sm-right">
              <button type="submit" class="btn btn-marca px-4" id="botonSubir"><i class="bi bi-upload"></i> <span>Subir imágenes</span></button>
            </div>
          </div>

          <!-- Barra de progreso de la subida -->
          <div class="progress mt-3 d-none" id="progreso" style="height: 1.2rem;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100">0%</div>
          </div>
        </form>
      </div>
    </div>

    <!-- Barra de la galería: título, búsqueda, orden y borrar álbum -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="h5 mb-2 mr-3"><?= esc($titulo_seccion) ?> <small class="text-muted">(<span id="conteoVisible"><?= count($imagenes) ?></span>)</small></h2>

      <div class="d-flex flex-wrap align-items-center">
        <!-- Búsqueda instantánea en el navegador (galeria.js) -->
        <div class="input-group input-group-sm mr-2 mb-2" style="width: 200px;">
          <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span></div>
          <input type="search" class="form-control" id="buscar" placeholder="Buscar por título" aria-label="Buscar por título">
        </div>

        <form method="get" action="<?= base_url('imagenes') ?>" class="form-inline mr-2 mb-2">
          <input type="hidden" name="album" value="<?= esc($album) ?>">
          <label for="orden" class="sr-only">Ordenar por</label>
          <select class="form-control form-control-sm" id="orden" name="orden" onchange="this.form.submit()">
            <?php foreach ($ordenes as $clave => $o): ?>
              <option value="<?= $clave ?>" <?= $orden === $clave ? 'selected' : '' ?>><?= $o[2] ?></option>
            <?php endforeach; ?>
          </select>
          <noscript><button class="btn btn-sm btn-light ml-1">Aplicar</button></noscript>
        </form>

        <?php if ($albumActual): ?>
          <form action="<?= base_url('imagenes/album/borrar/' . $albumActual) ?>" method="post" class="mb-2"
                onsubmit="return confirm('¿Borrar este álbum? Las imágenes NO se borran, pasarán a &quot;Sin álbum&quot;.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-folder-x"></i> Borrar álbum</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <?php if (count($imagenes) === 0): ?>
      <div class="card"><div class="vacio"><i class="bi bi-image"></i>No hay imágenes aquí todavía.<br>Arrástralas a la zona de arriba para agregarlas.</div></div>
    <?php else: ?>
      <div class="card d-none" id="sinResultados"><div class="vacio"><i class="bi bi-search"></i>Ninguna imagen coincide con la búsqueda.</div></div>

      <div class="row" id="galeria">
        <?php foreach ($imagenes as $img): ?>
          <div class="col-6 col-md-4 col-xl-3 mb-4 item-galeria" data-titulo="<?= esc($img['titulo']) ?>">
            <div class="card h-100 tarjeta-imagen">
              <img src="<?= base_url('imagenes/miniatura/' . $img['id']) ?>" alt="<?= esc($img['titulo']) ?>" class="miniatura" loading="lazy"
                   tabindex="0" role="button" data-src="<?= base_url('imagenes/ver/' . $img['id']) ?>" data-titulo="<?= esc($img['titulo']) ?>">
              <div class="card-body p-2">
                <div class="font-weight-bold text-truncate small" title="<?= esc($img['titulo']) ?>"><?= esc($img['titulo']) ?></div>
                <div class="text-muted" style="font-size: .75rem;"><?= $img['ancho'] ?>×<?= $img['alto'] ?> · <?= formato_tamano($img['tamano']) ?></div>
                <span class="badge badge-light border mt-1 text-truncate mw-100"><i class="bi bi-folder"></i> <?= esc($img['album_nombre'] ?? 'Sin álbum') ?></span>
              </div>
              <div class="card-footer border-0 p-2 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-primary btn-sm" title="Editar y mover" aria-label="Editar"
                        data-toggle="modal" data-target="#modalEditar"
                        data-accion="<?= base_url('imagenes/editar/' . $img['id']) ?>"
                        data-titulo="<?= esc($img['titulo']) ?>" data-album="<?= $img['album'] ?>">
                  <i class="bi bi-pencil"></i>
                </button>
                <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('imagenes/ver/' . $img['id']) ?>" download="<?= esc($img['titulo']) ?>" title="Descargar" aria-label="Descargar"><i class="bi bi-download"></i></a>
                <form action="<?= base_url('imagenes/borrar/' . $img['id']) ?>" method="post"
                      onsubmit="return confirm('¿Borrar la imagen &quot;<?= esc($img['titulo'], 'js') ?>&quot;? No se puede deshacer.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="volver_album" value="<?= esc($album) ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm" title="Borrar" aria-label="Borrar"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<?= view('partials/visor') ?>

<!-- Ventana para organizar: cambiar título y mover a otro álbum -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="tituloEditar" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" id="formEditar">
      <?= csrf_field() ?>
      <input type="hidden" name="volver_album" value="<?= esc($album) ?>">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloEditar"><i class="bi bi-pencil"></i> Editar imagen</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="editar_titulo">Título</label>
          <input type="text" class="form-control" id="editar_titulo" name="titulo" maxlength="150" required>
        </div>
        <div class="form-group mb-0">
          <label for="editar_album">Álbum</label>
          <select class="form-control" id="editar_album" name="album">
            <option value="">Sin álbum</option>
            <?php foreach ($albumes as $a): ?>
              <option value="<?= $a['id'] ?>"><?= esc($a['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-marca"><i class="bi bi-check-lg"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
