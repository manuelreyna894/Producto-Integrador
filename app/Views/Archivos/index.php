<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-0"><i class="bi bi-folder2-open text-primary"></i> Mis archivos</h1>
    <p class="text-muted small mb-0"><?= count($archivos) ?> archivos · <?= formato_tamano($espacio) ?> utilizados</p>
  </div>

  <!-- Reporte de archivos en distintos formatos -->
  <?php if (count($archivos) > 0): ?>
    <div class="dropdown mt-2 mt-md-0">
      <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="menuReporte" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="bi bi-file-earmark-arrow-down"></i> Descargar reporte
      </button>
      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="menuReporte">
        <?php foreach ($formatos as $clave => $texto): ?>
          <a class="dropdown-item" href="<?= base_url('archivos/reporte/' . $clave) ?>"><?= $texto ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Formulario para AGREGAR un archivo -->
<div class="card mb-4">
  <div class="card-body">
    <form action="<?= base_url('archivos/subir') ?>" method="post" enctype="multipart/form-data" class="form-row align-items-center">
      <?= csrf_field() ?>
      <div class="col-md-8 mb-2 mb-md-0">
        <label for="archivo" class="sr-only">Archivo</label>
        <input type="file" class="form-control-file" id="archivo" name="archivo" required
               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.zip">
        <small class="form-text text-muted">PDF, Office, texto, imágenes o ZIP. Máximo 10 MB.</small>
      </div>
      <div class="col-md-4 text-md-right">
        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up"></i> Subir archivo</button>
      </div>
    </form>
  </div>
</div>

<!-- Lista de archivos con opciones para ver, descargar y borrar -->
<div class="card">
  <?php if (count($archivos) === 0): ?>
    <div class="vacio"><i class="bi bi-folder2"></i>Todavía no has subido archivos. Usa el formulario de arriba para agregar el primero.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr><th>Archivo</th><th class="d-none d-md-table-cell">Tipo</th><th>Tamaño</th><th class="d-none d-sm-table-cell">Fecha</th><th class="text-right">Opciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($archivos as $a): ?>
            <tr>
              <td class="align-middle">
                <i class="bi <?= icono_archivo($a['tipo'], $a['nombre_original']) ?> mr-1"></i>
                <a href="<?= base_url('archivos/ver/' . $a['id']) ?>" target="_blank" rel="noopener"><?= esc($a['nombre_original']) ?></a>
              </td>
              <td class="align-middle small text-muted d-none d-md-table-cell"><?= esc($a['tipo']) ?></td>
              <td class="align-middle text-nowrap"><?= formato_tamano($a['tamano']) ?></td>
              <td class="align-middle text-nowrap d-none d-sm-table-cell"><?= date('d/m/Y H:i', strtotime($a['fecha'])) ?></td>
              <td class="align-middle text-right text-nowrap">
                <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('archivos/ver/' . $a['id']) ?>" target="_blank" rel="noopener" title="Ver"><i class="bi bi-eye"></i></a>
                <a class="btn btn-outline-primary btn-sm" href="<?= base_url('archivos/descargar/' . $a['id']) ?>" title="Descargar"><i class="bi bi-download"></i></a>
                <form action="<?= base_url('archivos/borrar/' . $a['id']) ?>" method="post" class="d-inline"
                      onsubmit="return confirm('¿Borrar &quot;<?= esc($a['nombre_original'], 'js') ?>&quot;? Esta acción no se puede deshacer.');">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-outline-danger btn-sm" title="Borrar"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
