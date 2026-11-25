<!-- Visor de imágenes (lo controla public/js/galeria.js).
     Se navega con los botones o con las flechas del teclado. -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-labelledby="tituloVer" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-truncate" id="tituloVer"></h5>
        <small class="text-muted ml-3 mt-1" id="posicionVer"></small>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body text-center bg-dark">
        <button type="button" class="navegar anterior" id="verAnterior" aria-label="Imagen anterior"><i class="bi bi-chevron-left"></i></button>
        <img src="" alt="" id="imagenVer" class="img-fluid" style="max-height: 75vh;">
        <button type="button" class="navegar siguiente" id="verSiguiente" aria-label="Imagen siguiente"><i class="bi bi-chevron-right"></i></button>
      </div>
      <div class="modal-footer py-2 small text-muted justify-content-center">
        Usa las flechas <kbd>←</kbd> <kbd>→</kbd> del teclado para cambiar de imagen y <kbd>Esc</kbd> para cerrar.
      </div>
    </div>
  </div>
</div>
