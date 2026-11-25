<?php

namespace App\Libraries;

/*
 * LIBRERÍA para crear documentos en distintos formatos.
 * La usan el controlador Documentos (documentos del usuario) y el
 * controlador Archivos (reporte de archivos), así el código no se repite.
 *
 * Recibe un arreglo con la información del documento:
 *   [
 *     'titulo'    => 'Texto del título',
 *     'subtitulo' => 'Línea de datos (tipo, fecha, autor...)',
 *     'parrafos'  => ['renglón 1', 'renglón 2', ...],
 *     'tabla'     => [['Encabezado 1', 'Encabezado 2'], ['dato', 'dato'], ...]   (opcional)
 *   ]
 * Y regresa ['contenido' => datos del archivo, 'nombre' => nombre.ext]
 *
 * Word, Excel y PDF necesitan las bibliotecas instaladas con Composer
 * (actividad 3.3). Imagen usa la librería GD (actividad 3.2) y JSON
 * usa json_encode (actividad 3.1).
 */
class GeneradorDocumentos
{
    // Formatos disponibles: clave => texto para mostrar
    public const FORMATOS = [
        'word'   => 'Word (.docx)',
        'excel'  => 'Excel (.xlsx)',
        'pdf'    => 'PDF (.pdf)',
        'imagen' => 'Imagen (.png)',
        'json'   => 'JSON (.json)',
    ];

    public function generar(string $formato, array $doc): array
    {
        $doc += ['subtitulo' => '', 'parrafos' => [], 'tabla' => []];
        $base = $this->nombreArchivo($doc['titulo']);

        switch ($formato)
        {
            case 'word':   return ['contenido' => $this->word($doc),   'nombre' => $base . '.docx'];
            case 'excel':  return ['contenido' => $this->excel($doc),  'nombre' => $base . '.xlsx'];
            case 'pdf':    return ['contenido' => $this->pdf($doc),    'nombre' => $base . '.pdf'];
            case 'imagen': return ['contenido' => $this->imagen($doc), 'nombre' => $base . '.png'];
            case 'json':   return ['contenido' => $this->json($doc),   'nombre' => $base . '.json'];
        }

        throw new \InvalidArgumentException('Formato no válido.');
    }

    /* ---------------- WORD (PHPWord) ---------------- */
    private function word(array $doc): string
    {
        $this->requiere('PhpOffice\PhpWord\PhpWord', 'phpoffice/phpword');

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $section->addText($doc['titulo'], ['bold' => true, 'size' => 18, 'color' => '1F4E79']);
        $section->addText($doc['subtitulo'], ['color' => '808080', 'size' => 9]);
        $section->addTextBreak();

        foreach ($doc['parrafos'] as $parrafo)
        {
            $section->addText($parrafo);
        }

        if (! empty($doc['tabla']))
        {
            $section->addTextBreak();
            $tabla = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);
            foreach ($doc['tabla'] as $i => $fila)
            {
                $tabla->addRow();
                foreach ($fila as $celda)
                {
                    $tabla->addCell(1800)->addText((string) $celda, ['bold' => $i === 0, 'size' => 9]);
                }
            }
        }

        // Se guarda en un archivo temporal y se regresa su contenido
        $temporal = tempnam(WRITEPATH . 'cache', 'doc');
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($temporal);
        $contenido = file_get_contents($temporal);
        unlink($temporal);

        return $contenido;
    }

    /* ---------------- EXCEL (PhpSpreadsheet) ---------------- */
    private function excel(array $doc): string
    {
        $this->requiere('PhpOffice\PhpSpreadsheet\Spreadsheet', 'phpoffice/phpspreadsheet');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        // Arreglo con las filas de la hoja
        $filas = [[$doc['titulo']], [$doc['subtitulo']], []];
        foreach ($doc['parrafos'] as $parrafo)
        {
            $filas[] = [$parrafo];
        }

        $filaTabla = null;
        if (! empty($doc['tabla']))
        {
            $filas[]   = [];
            $filaTabla = count($filas) + 1;   // Número de fila donde empieza la tabla
            foreach ($doc['tabla'] as $fila)
            {
                $filas[] = $fila;
            }
        }

        $hoja->fromArray($filas);
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        if ($filaTabla)
        {
            $hoja->getStyle("A{$filaTabla}:F{$filaTabla}")->getFont()->setBold(true);
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $columna)
            {
                $hoja->getColumnDimension($columna)->setAutoSize(true);
            }
        }
        else
        {
            $hoja->getColumnDimension('A')->setWidth(100);
        }

        $temporal = tempnam(WRITEPATH . 'cache', 'xls');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($temporal);
        $contenido = file_get_contents($temporal);
        unlink($temporal);

        return $contenido;
    }

    /* ---------------- PDF (TCPDF) ---------------- */
    private function pdf(array $doc): string
    {
        $this->requiere('TCPDF', 'tecnickcom/tcpdf:^6');

        $pdf = new \TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle($doc['titulo']);
        $pdf->AddPage();

        // "dejavusans" es una fuente de TCPDF que muestra acentos y ñ
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->Write(0, $doc['titulo'], '', false, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetTextColor(110, 110, 110);
        $pdf->Write(0, $doc['subtitulo']);
        $pdf->Ln(10);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('dejavusans', '', 11);
        if (! empty($doc['parrafos']))
        {
            $pdf->MultiCell(0, 0, implode("\n", $doc['parrafos']));
            $pdf->Ln(4);
        }

        if (! empty($doc['tabla']))
        {
            $pdf->SetFont('dejavusans', '', 9);
            $html = '<table border="1" cellpadding="4">';
            foreach ($doc['tabla'] as $i => $fila)
            {
                $html .= '<tr>';
                foreach ($fila as $celda)
                {
                    $etiqueta = $i === 0 ? 'th' : 'td';
                    $html .= "<$etiqueta>" . htmlspecialchars((string) $celda) . "</$etiqueta>";
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
            $pdf->writeHTML($html);
        }

        return $pdf->Output('', 'S');   // 'S' regresa el PDF como texto
    }

    /* ---------------- IMAGEN PNG (librería GD) ---------------- */
    private function imagen(array $doc): string
    {
        // Renglones del contenido, cortados para que quepan en la imagen
        $renglones = [];
        foreach ($doc['parrafos'] as $parrafo)
        {
            $cortado   = wordwrap($this->quitarAcentos($parrafo), 90, "\n", true);
            $renglones = array_merge($renglones, explode("\n", $cortado));
        }
        if (! empty($doc['tabla']))
        {
            $renglones[] = '';
            foreach ($doc['tabla'] as $fila)
            {
                $celdas = array_map(fn ($c) => str_pad(substr($this->quitarAcentos((string) $c), 0, 22), 22), $fila);
                $renglones[] = rtrim(implode(' ', $celdas));
            }
        }
        $renglones = array_slice($renglones, 0, 60);

        $ancho  = 700;
        $alto   = 130 + count($renglones) * 16;
        $imagen = imagecreatetruecolor($ancho, $alto);

        $azul       = imagecolorallocate($imagen, 31, 78, 121);
        $blanco     = imagecolorallocate($imagen, 255, 255, 255);
        $gris       = imagecolorallocate($imagen, 50, 50, 50);
        $gris_claro = imagecolorallocate($imagen, 130, 130, 130);

        imagefill($imagen, 0, 0, $blanco);
        imagefilledrectangle($imagen, 0, 0, $ancho, 50, $azul);
        imagerectangle($imagen, 0, 0, $ancho - 1, $alto - 1, $azul);

        $titulo = substr($this->quitarAcentos($doc['titulo']), 0, 75);
        imagestring($imagen, 5, (int) (($ancho - imagefontwidth(5) * strlen($titulo)) / 2), 17, $titulo, $blanco);
        imagestring($imagen, 2, 20, 62, substr($this->quitarAcentos($doc['subtitulo']), 0, 110), $gris_claro);
        imageline($imagen, 20, 84, $ancho - 20, 84, $azul);

        $y = 96;
        foreach ($renglones as $renglon)
        {
            imagestring($imagen, 3, 20, $y, $renglon, $gris);
            $y += 16;
        }

        ob_start();
        imagepng($imagen);
        imagedestroy($imagen);

        return ob_get_clean();
    }

    /* ---------------- JSON ---------------- */
    private function json(array $doc): string
    {
        return json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /* ---------------- Utilidades ---------------- */

    // Si falta una biblioteca de Composer, lanza un error con la instrucción para instalarla
    private function requiere(string $clase, string $paquete): void
    {
        if (! class_exists($clase))
        {
            throw new \RuntimeException("Para este formato falta instalar una biblioteca. En la carpeta del proyecto ejecuta: composer require \"$paquete\"");
        }
    }

    // Las fuentes de imagestring() no muestran acentos ni ñ
    private function quitarAcentos(string $texto): string
    {
        return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
                              'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
    }

    // Nombre de archivo seguro a partir del título
    private function nombreArchivo(string $titulo): string
    {
        $nombre = trim(preg_replace('/[^A-Za-z0-9]+/', '_', $this->quitarAcentos($titulo)), '_');
        return $nombre !== '' ? $nombre : 'documento';
    }
}
