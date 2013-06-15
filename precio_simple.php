<?php

/*
  Plugin Name: Precio Simple
  Plugin URI: http://gattaca.com.ar/wordpress-plugins/#precio
  Version: 2010-05-22
  Description: Muestra el precio grabado en un custom field llamado 'precio' de un post o p&aacute;gina. Para ello busca la cadena '<em>[precio]</em>' en el cuerpo del post. De no entontrar dicha palabra clave, muestra un mensaje indicando que el precio no ha sido definido.
  Author: Carlos Augusto Jaacks
  Author URI: http://gattaca.com.ar/
 */

function Armar_Cadena_Precio() {
    global $post;
    $importe = -1;
    if (get_post_meta($post->ID, 'precio', true))
        $importe = get_post_meta($post->ID, 'precio', true);

    $cadena = "\n<br />";
    if ($importe != -1) {
        $cadena .= 'Precio: <strong>$' . number_format($importe, 0) . '.-</strong>';  // En caso de querer usar decimales, cambiar el 0 por el numero de decimales deseados.
        //$cadena .= "\n<br />";
        $cadena .= "&nbsp;&nbsp;";
        $cadena .= '<small>(En <em>pesos</em> de Argentina).</small>';
    } else {
        $cadena = '<em>(No se carg&oacute; el <strong>precio</strong> para este producto).</em>';
    }
    $cadena .= "\n<br />";
    return $cadena;
}

function Mostrar_Precio($importe) {
    $importe = preg_replace("/\[precio\]/ise", "Armar_Cadena_Precio()", $importe);
    return $importe;
}

add_filter('the_content', 'Mostrar_Precio', '7');
