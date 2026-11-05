<?php
// Ícono del sprite image/iconos.svg. $raiz = '' en el sitio, '../' dentro de /admin.
function svgIcono(string $nombre, string $clases = '', string $raiz = ''): string
{
    $clase = trim('icono ' . $clases);
    return '<svg class="' . htmlspecialchars($clase, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true" focusable="false">'
        . '<use href="' . $raiz . 'image/iconos.svg#' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '"/></svg>';
}
