<div class="row justify-content-center">
  <div class="col-lg-6">
    <nav aria-label="Ruta"><ol class="breadcrumb bg-transparent px-0">
      <li class="breadcrumb-item"><a href="<?= base_url('admin/usuarios') ?>">Administración</a></li>
      <li class="breadcrumb-item active" aria-current="page">Modificar usuario</li>
    </ol></nav>

    <div class="card">
      <div class="card-body">
        <h1 class="h4 mb-3"><i class="bi bi-pencil-square"></i> Modificar a <?= esc($user['name']) ?></h1>

        <?php if (! empty($errores)): ?>
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/usuarios/editar/' . $user['id']) ?>" method="post">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= esc($user['name']) ?>" maxlength="256" required>
          </div>
          <div class="form-group">
            <label for="login">Usuario (login)</label>
            <input type="text" class="form-control" id="login" name="login" value="<?= esc($user['login']) ?>" maxlength="128" pattern="[A-Za-z0-9_\-]+" required>
          </div>
          <div class="form-group">
            <label for="rol">Tipo de usuario</label>
            <select class="form-control" id="rol" name="rol" <?= $esYo ? 'disabled' : '' ?>>
              <?php foreach ($roles as $clave => $texto): ?>
                <option value="<?= $clave ?>" <?= $user['rol'] === $clave ? 'selected' : '' ?>><?= $texto ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($esYo): ?>
              <small class="form-text text-muted">No puedes quitarte el rol de administrador a ti mismo.</small>
            <?php endif; ?>
          </div>
          <div class="form-group">
            <label for="password">Nueva contraseña</label>
            <input type="password" class="form-control" id="password" name="password" minlength="6" autocomplete="new-password">
            <small class="form-text text-muted">Déjala vacía para conservar la actual.</small>
          </div>
          <button type="submit" class="btn btn-marca"><i class="bi bi-check-lg"></i> Guardar cambios</button>
          <a href="<?= base_url('admin/usuarios') ?>" class="btn btn-light">Cancelar</a>
        </form>
      </div>
    </div>
  </div>
</div>
