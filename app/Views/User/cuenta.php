<div class="row justify-content-center">
  <div class="col-lg-6">
    <h1 class="h3 mb-3"><i class="bi bi-gear text-marca"></i> Mi cuenta</h1>

    <?php if (! empty($errores)): ?>
      <div class="alert alert-danger" role="alert">
        <ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form action="<?= base_url('cuenta') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label>Usuario</label>
            <input type="text" class="form-control-plaintext" value="<?= esc($user['login']) ?>" readonly>
          </div>
          <div class="form-group">
            <label>Tipo de cuenta</label>
            <input type="text" class="form-control-plaintext" value="<?= esc($roles[$user['rol']] ?? 'Usuario') ?>" readonly>
          </div>
          <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= esc($user['name']) ?>" maxlength="256" required>
          </div>

          <hr>
          <h2 class="h6">Cambiar contraseña <small class="text-muted">(opcional)</small></h2>
          <div class="form-group">
            <label for="password_actual">Contraseña actual</label>
            <input type="password" class="form-control" id="password_actual" name="password_actual" autocomplete="current-password">
          </div>
          <div class="form-row">
            <div class="form-group col-sm-6">
              <label for="password">Nueva contraseña</label>
              <input type="password" class="form-control" id="password" name="password" minlength="6" autocomplete="new-password">
              <small class="form-text text-muted">Mínimo 6 caracteres.</small>
            </div>
            <div class="form-group col-sm-6">
              <label for="password_confirmar">Confirmar nueva contraseña</label>
              <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" autocomplete="new-password">
            </div>
          </div>

          <button type="submit" class="btn btn-marca"><i class="bi bi-check-lg"></i> Guardar cambios</button>
          <a href="<?= base_url('panel') ?>" class="btn btn-light">Cancelar</a>
        </form>
      </div>
    </div>
  </div>
</div>
