<div class="row justify-content-center">
  <div class="col-lg-8">

    <h1 class="h3 mb-3"><i class="bi bi-chat-square-text text-marca"></i> Publicaciones</h1>

    <?php if (session()->has('user')): ?>
      <!-- Formulario para publicar (solo con sesión) -->
      <div class="card mb-4">
        <div class="card-body">
          <form action="<?= base_url('publication/add') ?>" method="post">
            <?= csrf_field() ?>
            <label for="content" class="sr-only">Nueva publicación</label>
            <textarea class="form-control mb-2" id="content" name="content" rows="3" maxlength="2000"
                      placeholder="¿Qué quieres compartir, <?= esc(session()->get('name')) ?>?" required></textarea>
            <div class="text-right">
              <button type="submit" class="btn btn-marca"><i class="bi bi-send"></i> Publicar</button>
            </div>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-light border"><i class="bi bi-info-circle"></i> <a href="<?= base_url() ?>">Inicia sesión</a> para publicar.</div>
    <?php endif; ?>

    <?php if (count($posts) === 0): ?>
      <div class="card"><div class="vacio"><i class="bi bi-chat-square"></i>Todavía no hay publicaciones.</div></div>
    <?php endif; ?>

    <?php foreach ($posts as $item): ?>
      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <strong><i class="bi bi-person-circle text-muted"></i> <?= esc($item['name'] ?? 'Usuario eliminado') ?></strong>
              <small class="text-muted ml-2"><?= date('d/m/Y H:i', strtotime($item['posted_time'])) ?></small>
            </div>

            <?php // El autor puede editar y borrar; el administrador puede borrar cualquiera
            $esAutor = session()->has('user') && (int) session()->get('user') === (int) $item['user'];
            if ($esAutor || session()->get('rol') === 'admin'): ?>
              <div class="text-nowrap">
                <?php if ($esAutor): ?>
                  <a class="btn btn-outline-primary btn-sm" href="<?= base_url('publication/edit/' . $item['id']) ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
                <form action="<?= base_url('publication/delete/' . $item['id']) ?>" method="post" class="d-inline"
                      onsubmit="return confirm('¿Borrar esta publicación?');">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-outline-danger btn-sm" title="Borrar"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            <?php endif; ?>
          </div>
          <p class="card-text mt-2 mb-0" style="white-space: pre-line;"><?= esc($item['content']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>
