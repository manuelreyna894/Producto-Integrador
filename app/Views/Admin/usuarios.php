<div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h3 mb-0"><i class="bi bi-shield-lock text-warning"></i> Administración de usuarios</h1>
    <p class="text-muted small mb-0">Como administrador puedes modificar o borrar los datos de cualquier usuario.</p>
  </div>
</div>

<div class="row">
  <!-- Crear usuario -->
  <div class="col-lg-4 mb-4">
    <div class="card">
      <div class="card-body">
        <h2 class="h5"><i class="bi bi-person-plus"></i> Nuevo usuario</h2>
        <form action="<?= base_url('admin/usuarios/crear') ?>" method="post">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= esc(old('name')) ?>" maxlength="256" required>
          </div>
          <div class="form-group">
            <label for="login">Usuario (login)</label>
            <input type="text" class="form-control" id="login" name="login" value="<?= esc(old('login')) ?>" maxlength="128" pattern="[A-Za-z0-9_\-]+" required>
            <small class="form-text text-muted">Solo letras, números, guion y guion bajo.</small>
          </div>
          <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" minlength="6" autocomplete="new-password" required>
            <small class="form-text text-muted">Mínimo 6 caracteres.</small>
          </div>
          <div class="form-group">
            <label for="rol">Tipo de usuario</label>
            <select class="form-control" id="rol" name="rol">
              <?php foreach ($roles as $clave => $texto): ?>
                <option value="<?= $clave ?>" <?= old('rol') === $clave ? 'selected' : '' ?>><?= $texto ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success btn-block"><i class="bi bi-plus-lg"></i> Crear usuario</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de usuarios -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <h2 class="h5">Usuarios registrados <span class="badge badge-secondary"><?= count($users) ?></span></h2>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="thead-light">
              <tr><th>Usuario</th><th class="d-none d-md-table-cell">Tipo</th><th>Imágenes</th><th class="text-right">Opciones</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): $esYo = (int) $u['id'] === (int) session()->get('user'); ?>
                <tr>
                  <td class="align-middle">
                    <strong><?= esc($u['name']) ?></strong> <?php if ($esYo): ?><span class="badge badge-primary">Tú</span><?php endif; ?>
                    <div class="small text-muted"><?= esc($u['login']) ?></div>
                  </td>
                  <td class="align-middle d-none d-md-table-cell">
                    <span class="badge <?= $u['rol'] === 'admin' ? 'badge-warning' : 'badge-light border' ?>"><?= $roles[$u['rol']] ?? $u['rol'] ?></span>
                  </td>
                  <td class="align-middle text-nowrap">
                    <?= $u['imagenes'] ?> <span class="small text-muted">(<?= formato_tamano($u['espacio']) ?>)</span>
                  </td>
                  <td class="align-middle text-right text-nowrap">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('admin/usuarios/' . $u['id'] . '/galeria') ?>" title="Ver galería" aria-label="Ver galería de <?= esc($u['name']) ?>"><i class="bi bi-images"></i></a>
                    <a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/usuarios/editar/' . $u['id']) ?>" title="Modificar" aria-label="Modificar a <?= esc($u['name']) ?>"><i class="bi bi-pencil"></i></a>
                    <?php if (! $esYo): ?>
                      <form action="<?= base_url('admin/usuarios/borrar/' . $u['id']) ?>" method="post" class="d-inline"
                            onsubmit="return confirm('¿Borrar a <?= esc($u['name'], 'js') ?>? También se borrarán sus <?= (int) $u['imagenes'] ?> imágenes, sus archivos y documentos. No se puede deshacer.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Borrar" aria-label="Borrar a <?= esc($u['name']) ?>"><i class="bi bi-trash"></i></button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
