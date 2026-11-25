/* =====================================================================
   Mi Galería - JavaScript del LADO DEL CLIENTE (se ejecuta en el navegador)

   Funciones:
     1. Zona para arrastrar y soltar imágenes, con vista previa.
     2. Validación de tipo y tamaño ANTES de enviar al servidor.
     3. Subida con barra de progreso (XMLHttpRequest + FormData / AJAX).
     4. Búsqueda instantánea por título, sin recargar la página.
     5. Visor de imágenes con botones y flechas del teclado.
     6. Llenado de la ventana para editar una imagen.

   El servidor (PHP) vuelve a validar todo: la validación del navegador
   solo mejora la experiencia del usuario, no reemplaza la del servidor.
   ===================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var TIPOS_PERMITIDOS = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  var TAMANO_MAXIMO = 5 * 1024 * 1024;   // 5 MB, igual que en el servidor
  var MAXIMO_ARCHIVOS = 20;

  // Convierte bytes a un texto fácil de leer
  function formatoTamano(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
    return bytes + ' bytes';
  }

  // Quita acentos y pasa a minúsculas para comparar textos ("Montaña" = "montana")
  function normalizar(texto) {
    return String(texto).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  }

  /* ===================================================================
     1, 2 y 3. ZONA DE SUBIDA
     =================================================================== */
  var form = document.getElementById('formSubir');

  if (form) {
    var zona = document.getElementById('zonaSubida');
    var input = document.getElementById('imagenes');
    var previas = document.getElementById('previas');
    var cajaErrores = document.getElementById('erroresSubida');
    var boton = document.getElementById('botonSubir');
    var progreso = document.getElementById('progreso');
    var barra = progreso.querySelector('.progress-bar');

    var archivos = [];   // Lista de archivos elegidos: { file, valido, error }

    // Con JavaScript activo, la lista se maneja aquí y no con el "required" del navegador
    input.removeAttribute('required');

    function mostrarErrores(lista) {
      if (!lista.length) {
        cajaErrores.classList.add('d-none');
        cajaErrores.innerHTML = '';
        return;
      }
      cajaErrores.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> ' + lista.map(function (e) {
        return '<div>' + e.replace(/</g, '&lt;') + '</div>';
      }).join('');
      cajaErrores.classList.remove('d-none');
    }

    function validar(file) {
      if (TIPOS_PERMITIDOS.indexOf(file.type) === -1) return 'Formato no permitido (solo JPG, PNG, GIF o WEBP).';
      if (file.size > TAMANO_MAXIMO) return 'Pesa ' + formatoTamano(file.size) + '; el máximo es 5 MB.';
      return null;
    }

    function agregar(lista) {
      Array.prototype.forEach.call(lista, function (file) {
        if (archivos.length >= MAXIMO_ARCHIVOS) return;
        var error = validar(file);
        archivos.push({ file: file, valido: !error, error: error });
      });
      dibujar();
    }

    // Dibuja las vistas previas y actualiza el botón
    function dibujar() {
      previas.innerHTML = '';

      archivos.forEach(function (item, i) {
        var div = document.createElement('div');
        div.className = 'previa' + (item.valido ? '' : ' invalida');
        div.title = item.file.name + (item.error ? ' - ' + item.error : '');

        var img;
        if (item.valido) {
          img = document.createElement('img');
          img.alt = item.file.name;
          img.src = URL.createObjectURL(item.file);   // Vista previa local, sin enviar nada
          img.onload = function () { URL.revokeObjectURL(img.src); };
        } else {
          img = document.createElement('div');        // Archivo rechazado: ícono de error
          img.className = 'previa-error';
          img.innerHTML = '<i class="bi bi-x-octagon"></i>';
        }

        var quitar = document.createElement('button');
        quitar.type = 'button';
        quitar.className = 'quitar';
        quitar.innerHTML = '&times;';
        quitar.setAttribute('aria-label', 'Quitar ' + item.file.name);
        quitar.addEventListener('click', function () {
          archivos.splice(i, 1);
          dibujar();
        });

        var nombre = document.createElement('div');
        nombre.className = 'text-truncate';
        nombre.textContent = item.file.name;

        div.appendChild(img);
        div.appendChild(quitar);
        div.appendChild(nombre);
        previas.appendChild(div);
      });

      var validos = archivos.filter(function (a) { return a.valido; });
      var errores = archivos.filter(function (a) { return !a.valido; })
                            .map(function (a) { return a.file.name + ': ' + a.error; });
      if (archivos.length >= MAXIMO_ARCHIVOS) errores.push('Solo se pueden subir ' + MAXIMO_ARCHIVOS + ' imágenes a la vez.');
      mostrarErrores(errores);

      boton.querySelector('span').textContent = validos.length
        ? 'Subir ' + validos.length + (validos.length === 1 ? ' imagen' : ' imágenes')
        : 'Subir imágenes';
    }

    // Elegir archivos con el botón del sistema
    input.addEventListener('change', function () {
      agregar(input.files);
      input.value = '';   // Permite volver a elegir el mismo archivo
    });

    // Arrastrar y soltar
    ['dragenter', 'dragover'].forEach(function (evento) {
      zona.addEventListener(evento, function (e) {
        e.preventDefault();
        zona.classList.add('arrastrando');
      });
    });
    ['dragleave', 'drop'].forEach(function (evento) {
      zona.addEventListener(evento, function (e) {
        e.preventDefault();
        zona.classList.remove('arrastrando');
      });
    });
    zona.addEventListener('drop', function (e) {
      agregar(e.dataTransfer.files);
    });

    // Enviar al servidor con AJAX y mostrar el progreso
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      var validos = archivos.filter(function (a) { return a.valido; });
      if (!validos.length) {
        mostrarErrores(['Elige al menos una imagen válida para subir.']);
        return;
      }

      var datos = new FormData(form);   // Incluye el token CSRF, el álbum, etc.
      datos.delete('imagenes[]');
      validos.forEach(function (a) { datos.append('imagenes[]', a.file); });

      var xhr = new XMLHttpRequest();
      xhr.open('POST', form.action);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');   // El servidor responde en JSON

      xhr.upload.addEventListener('progress', function (ev) {
        if (!ev.lengthComputable) return;
        var porcentaje = Math.round(ev.loaded / ev.total * 100);
        barra.style.width = porcentaje + '%';
        barra.textContent = porcentaje + '%';
        barra.setAttribute('aria-valuenow', porcentaje);
      });

      xhr.addEventListener('load', function () {
        var respuesta = null;
        try { respuesta = JSON.parse(xhr.responseText); } catch (err) { /* respuesta no válida */ }

        if (respuesta && respuesta.ok) {
          barra.textContent = '¡Listo!';
          window.location.href = respuesta.url;   // Recarga la galería con el mensaje de éxito
          return;
        }

        terminar();
        mostrarErrores(respuesta && respuesta.errores && respuesta.errores.length
          ? respuesta.errores
          : ['No se pudieron subir las imágenes. Inténtalo de nuevo.']);
      });

      xhr.addEventListener('error', function () {
        terminar();
        mostrarErrores(['Se perdió la conexión con el servidor. Revisa tu internet e inténtalo de nuevo.']);
      });

      // Mientras se sube: botón desactivado y barra visible
      boton.disabled = true;
      progreso.classList.remove('d-none');
      barra.style.width = '0%';
      barra.textContent = '0%';
      xhr.send(datos);
    });

    function terminar() {
      boton.disabled = false;
      progreso.classList.add('d-none');
    }
  }

  /* ===================================================================
     4. BÚSQUEDA INSTANTÁNEA
     =================================================================== */
  var buscar = document.getElementById('buscar');

  if (buscar) {
    var conteo = document.getElementById('conteoVisible');
    var sinResultados = document.getElementById('sinResultados');

    buscar.addEventListener('input', function () {
      var texto = normalizar(buscar.value.trim());
      var visibles = 0;

      document.querySelectorAll('.item-galeria').forEach(function (item) {
        var coincide = normalizar(item.dataset.titulo).indexOf(texto) !== -1;
        item.classList.toggle('d-none', !coincide);
        if (coincide) visibles++;
      });

      if (conteo) conteo.textContent = visibles;
      if (sinResultados) sinResultados.classList.toggle('d-none', visibles > 0);
    });
  }

  /* ===================================================================
     5. VISOR DE IMÁGENES
     =================================================================== */
  var modalVer = document.getElementById('modalVer');

  if (modalVer) {
    var imagenVer = document.getElementById('imagenVer');
    var tituloVer = document.getElementById('tituloVer');
    var posicionVer = document.getElementById('posicionVer');
    var lista = [];
    var actual = 0;

    // Solo se recorren las imágenes visibles (respeta la búsqueda)
    function imagenesVisibles() {
      return Array.prototype.filter.call(document.querySelectorAll('.miniatura[data-src]'), function (img) {
        return !img.closest('.item-galeria') || !img.closest('.item-galeria').classList.contains('d-none');
      });
    }

    function mostrar(indice) {
      if (!lista.length) return;
      actual = (indice + lista.length) % lista.length;   // Da la vuelta al llegar al final
      var img = lista[actual];
      imagenVer.src = img.dataset.src;
      imagenVer.alt = img.dataset.titulo;
      tituloVer.textContent = img.dataset.titulo;
      posicionVer.textContent = (actual + 1) + ' de ' + lista.length;
    }

    function abrir(img) {
      lista = imagenesVisibles();
      mostrar(lista.indexOf(img));
      $(modalVer).modal('show');
    }

    document.querySelectorAll('.miniatura[data-src]').forEach(function (img) {
      img.addEventListener('click', function () { abrir(img); });
      img.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); abrir(img); }
      });
    });

    document.getElementById('verAnterior').addEventListener('click', function () { mostrar(actual - 1); });
    document.getElementById('verSiguiente').addEventListener('click', function () { mostrar(actual + 1); });

    document.addEventListener('keydown', function (e) {
      if (!modalVer.classList.contains('show')) return;
      if (e.key === 'ArrowLeft') mostrar(actual - 1);
      if (e.key === 'ArrowRight') mostrar(actual + 1);
    });
  }

  /* ===================================================================
     6. VENTANA PARA EDITAR (título y álbum)
     =================================================================== */
  if (document.getElementById('modalEditar')) {
    $('#modalEditar').on('show.bs.modal', function (e) {
      var origen = $(e.relatedTarget);
      $('#formEditar').attr('action', origen.data('accion'));
      $('#editar_titulo').val(origen.attr('data-titulo'));
      $('#editar_album').val(String(origen.data('album') || ''));
    });
    $('#modalEditar').on('shown.bs.modal', function () {
      $('#editar_titulo').trigger('focus').select();
    });
  }
});
