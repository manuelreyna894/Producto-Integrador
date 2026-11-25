</main>

<footer class="text-center text-muted small py-4">
  Mi Galería · Aplicación cliente/servidor con PHP (CodeIgniter 4), HTML y JavaScript · <?= date('Y') ?>
</footer>

<!-- JavaScript del lado del cliente -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (! empty($scripts)): // Scripts propios que necesita cada página ?>
  <?php foreach ($scripts as $script): ?>
    <script src="<?= base_url('js/' . $script) ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
