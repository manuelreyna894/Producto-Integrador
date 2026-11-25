<?php

/*
 * HELPER de archivos: funciones pequeñas que usan las vistas.
 * Se carga en los controladores con: protected $helpers = ['archivos'];
 */

if (! function_exists('formato_tamano'))
{
    // Convierte bytes a un texto fácil de leer: 2048 -> "2 KB"
    function formato_tamano($bytes)
    {
        $bytes = (int) $bytes;

        if ($bytes >= 1048576)
        {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024)
        {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' bytes';
    }
}

if (! function_exists('icono_archivo'))
{
    // Regresa el ícono de Bootstrap Icons que corresponde al tipo de archivo
    function icono_archivo($mime, $nombre = '')
    {
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        if (strpos((string) $mime, 'image/') === 0)          return 'bi-file-earmark-image text-success';
        if ($ext === 'pdf')                                  return 'bi-file-earmark-pdf text-danger';
        if (in_array($ext, ['doc', 'docx']))                 return 'bi-file-earmark-word text-primary';
        if (in_array($ext, ['xls', 'xlsx', 'csv']))          return 'bi-file-earmark-excel text-success';
        if (in_array($ext, ['ppt', 'pptx']))                 return 'bi-file-earmark-ppt text-warning';
        if ($ext === 'zip')                                  return 'bi-file-earmark-zip text-secondary';
        if ($ext === 'txt')                                  return 'bi-file-earmark-text text-secondary';

        return 'bi-file-earmark text-secondary';
    }
}
