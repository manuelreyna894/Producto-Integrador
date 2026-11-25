<h1 class="h3 mb-3"><i class="bi bi-file-earmark-text text-success"></i> Mis documentos</h1>

<div class="row">
  <!-- FORMULARIO DE CREACIÓN DE DOCUMENTOS -->
  <div class="col-lg-5 mb-4">
    <div class="card">
      <div class="card-body">
        <h2 class="h5"><i class="bi bi-plus-circle"></i> Nuevo documento</h2>
        <form action="<?= base_url('documentos/crear') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" class="form-control" id="titulo" name="titulo" maxlength="150" value="<?= esc(old('titulo')) ?>" required>
          </div>

          <div class="form-group">
            <label for="tipo">Tipo de documento</label>
            <select class="form-control" id="tipo" name="tipo">
              <?php foreach ($tipos as $t): ?>
                <option value="<?= $t ?>" <?= old('tipo') === $t ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="contenido">Contenido</label>
            <textarea class="form-control" id="contenido" name="contenido" rows="7" maxlength="5000" required><?= esc(old('contenido')) ?></textarea>
          </div>

          <!-- El usuario elige UN formato de descarga -->
          <div class="form-group">
            <label class="d-block">Formato de descarga</label>
            <?php foreach ($formatos as $clave => $texto): ?>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="f_<?= $clave ?>" name="formato" value="<?= $clave ?>" class="custom-control-input"
                       <?= (old('formato') ?? 'pdf') === $clave ? 'checked' : '' ?>>
                <label class="custom-control-label" for="f_<?= $clave ?>"><?= $texto ?></label>
              </div>
            <?php endforeach; ?>
          </div>

          <button type="submit" class="btn btn-success btn-block"><i class="bi bi-download"></i> Crear y descargar</button>
        </form>
      </div>
    </div>
  </div>

  <!-- DOCUMENTOS CREADOS: se pueden descargar en cualquier formato -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <h2 class="h5">Documentos creados <span class="badge badge-success"><?= count($documentos) ?></span></h2>

        <?php if (count($documentos) === 0): ?>
          <div class="vacio"><i class="bi bi-file-earmark"></i>Aún no has creado documentos. Llena el formulario para crear el primero.</div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($documentos as $d): ?>
              <li class="list-group-item px-0 d-flex flex-wrap justify-content-between align-items-center">
                <div class="mr-2 mb-2 mb-sm-0">
                  <strong><?= esc($d['titulo']) ?></strong>
                  <span class="badge badge-light border ml-1"><?= esc($d['tipo']) ?></span>
                  <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($d['fecha'])) ?></div>
                </div>
                <div class="text-nowrap">
                  <div class="btn-group">
                    <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="bi bi-download"></i> Descargar
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <?php foreach ($formatos as $clave => $texto): ?>
                        <a class="dropdown-item" href="<?= base_url("documentos/descargar/{$d['id']}/$clave") ?>"><?= $texto ?></a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <form action="<?= base_url('documentos/borrar/' . $d['id']) ?>" method="post" class="d-inline"
                        onsubmit="return confirm('¿Borrar el documento &quot;<?= esc($d['titulo'], 'js') ?>&quot;?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Borrar"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($descargar = session()->getFlashdata('descargar')): ?>
  <!-- Inicia la descarga del documento recién creado -->
  <script>
    setTimeout(function () { window.location.href = <?= json_encode($descargar) ?>; }, 800);
  </script>
<?php endif; ?>
