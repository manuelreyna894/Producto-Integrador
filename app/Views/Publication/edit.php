<div class="row justify-content-center">
  <div class="col-lg-8">
    <nav aria-label="Ruta"><ol class="breadcrumb bg-transparent px-0">
      <li class="breadcrumb-item"><a href="<?= base_url('publication') ?>">Publicaciones</a></li>
      <li class="breadcrumb-item active" aria-current="page">Editar</li>
    </ol></nav>

    <div class="card">
      <div class="card-body">
        <h1 class="h4 mb-3">Editar publicación</h1>
        <form action="<?= base_url('publication/edit/' . $post['id']) ?>" method="post">
          <?= csrf_field() ?>
          <textarea class="form-control mb-3" name="content" rows="4" maxlength="2000" required aria-label="Contenido"><?= esc($post['content']) ?></textarea>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Guardar cambios</button>
          <a href="<?= base_url('publication') ?>" class="btn btn-light">Cancelar</a>
        </form>
      </div>
    </div>
  </div>
</div>
