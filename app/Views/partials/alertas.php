<?php
/*
 * Alertas de toda la aplicación (mensajes "flash" de la sesión).
 * Se incluyen en header.php, así todas las páginas muestran los avisos igual.
 */
$exito   = session()->getFlashdata('mensaje');
$error   = session()->getFlashdata('error') ?? session()->getFlashdata('login_error');
$errores = session()->getFlashdata('errores');
?>

<?php if ($exito): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill"></i> <?= esc($exito) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i> <?= esc($error) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
  </div>
<?php endif; ?>

<?php if (! empty($errores)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i> Revisa lo siguiente:
    <ul class="mb-0 mt-1">
      <?php foreach ($errores as $e): ?>
        <li><?= esc($e) ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
  </div>
<?php endif; ?>
