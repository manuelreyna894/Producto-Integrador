<div class="row justify-content-center mt-md-4">
  <div class="col-md-6 col-lg-5">
    <div class="card">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <i class="bi bi-images text-marca icono-grande"></i>
          <h1 class="h4 mt-2 mb-1">Mi Galería</h1>
          <h2 class="h6 text-muted">Iniciar sesión</h2>
          <p class="text-muted small mb-0">Tu galería de imágenes, disponible en cualquier dispositivo.</p>
        </div>

        <form action="<?= base_url('user/login') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="login">Nombre de usuario</label>
            <!-- $login_recordado viene de la cookie recordar_login -->
            <input type="text" class="form-control" id="login" name="login" value="<?= esc($login_recordado) ?>"
                   autocomplete="username" required <?= $login_recordado === '' ? 'autofocus' : '' ?>>
          </div>

          <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="current-password"
                   required <?= $login_recordado !== '' ? 'autofocus' : '' ?>>
          </div>

          <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="recordar" name="recordar" value="1" <?= $login_recordado !== '' ? 'checked' : '' ?>>
            <label class="form-check-label" for="recordar">Recordar mi usuario en este equipo</label>
          </div>

          <button type="submit" class="btn btn-marca btn-block"><i class="bi bi-box-arrow-in-right"></i> Entrar</button>
        </form>
      </div>
    </div>
    <p class="text-center mt-3 small"><a href="<?= base_url('publication') ?>">Ver las publicaciones sin iniciar sesión</a></p>
  </div>
</div>
