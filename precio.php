<?php
/*
  Plugin Name: Precio
  Plugin URI: http://gattaca.com.ar/wordpress-plugins/#precio
  Version: 2011-05-19
  Description: Muestra el precio grabado en un <em>custom field</em> llamado 'precio' de un post o p&aacute;gina. Para ello busca la cadena '<em>[precio]</em>' en el cuerpo del post. Si el valor del precio en el custom field es <em>-1</em>, muestra una leyenda de 'producto fuera de stock'. Permite efectuar una b&uacute;squeda por rango de precios. Si desea usar centavos, utilize el punto en los decimales (no la coma) al cargar los precios en los <em>customs fields.</em> Se usa {?php Mostrar_Precio(); ?} desde el template para mostrar el precio en una p&aacute;gina o post y {?php Mostrar_Form_Rango(); ?} para desplegar el formulario de b&uacute;squeda por precio para el usuario.

  Author: Carlos Augusto Jaacks
  Author URI: http://gattaca.com.ar/
 */

/* --- Licencia / licence ---

  DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
  Version 2, December 2004

  Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

  Everyone is permitted to copy and distribute verbatim or modified
  copies of this license document, and changing it is allowed as long
  as the name is changed.

  DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

  0. You just DO WHAT THE FUCK YOU WANT TO.

 */

/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details. */



define('URL_FROM_DE_CONTACTO', get_bloginfo('wpurl') . '/contactenos/');
define('ESCALA_BASE_DE_RANGO_DE_PRECIOS', 50);

/**
 * Arma el string de muesteo de un precio unico en pantalla,
 * en una pagina o post.
 * @global <type> $post
 * @return string
 */
function Armar_Cadena_Precio() {
    global $post;
    $importe = -1;
    if (get_post_meta($post->ID, 'precio', true))
        $importe = get_post_meta($post->ID, 'precio', true);

    $cadena = "\n<br />\n";
    if ($importe != -1) {
        $centavos = (number_format($importe, 2) != number_format($importe, 0)) ? 2 : 0;
        $cadena .= 'Precio: <strong>$' . number_format($importe, $centavos) . '.-</strong>';
        $cadena .= "&nbsp;&nbsp;";
        $cadena .= '<small>(En <em>pesos</em> de Argentina).</small>';
        $cadena .= "\n<br />";
        $cadena .= 'Para comprar o solicitar m&aacute;s informaci&oacute;n, <a href="' .
                URL_FROM_DE_CONTACTO . '" title="Escr&iacute;banos a trav&eacute;s de nuestro formulario de contacto.">cont&aacute;ctenos</a>.';
    } else {
        $cadena = '<em>Este producto se encuentra temporalmente <strong>fuera de stock</strong>.</em>';
    }
    $cadena .= "\n<br />\n";
    return $cadena;
}

/**
 * Muestra el precio llamando a la funcion Armar_Cadena_Precio
 * en donde encuentra el string [precio].
 * @param double $importe
 * @return string
 */
function Mostrar_Precio($importe) {
    $importe = preg_replace("/\[precio\]/ise", "Armar_Cadena_Precio()", $importe);
    return $importe;
}

/**
 * Pinta el form de busqueda de precios restringida desde/hasta.
 *
 * @global type $wpdb
 * @global type $post 
 */
function Mostrar_Form_Busqueda_Rango_Precios_Desde_Hasta() {
    global $wpdb;
    global $post;

    /*
      echo '<pre>';
      print_r($_POST);
      echo '</pre>';
     */

    $rangoPrecioDesdePorPost = (isset($_POST['rango_precio_desde'])) ? intval($_POST['rango_precio_desde']) : 0;
    $rangoPrecioHastaPorPost = (isset($_POST['rango_precio_hasta'])) ? intval($_POST['rango_precio_hasta']) : 0;

    if ($rangoPrecioDesdePorPost > $rangoPrecioHastaPorPost) {  // Swap de valores, si aplica.
        $tmp = $rangoPrecioDesdePorPost;
        $rangoPrecioDesdePorPost = $rangoPrecioHastaPorPost;
        $rangoPrecioHastaPorPost = $tmp;
    }

    $query_paginas_con_precios = "SELECT * FROM $wpdb->posts
				    WHERE post_status = 'publish'
				    LIMIT 0, 1000;";
    $pageposts = $wpdb->get_results($query_paginas_con_precios, OBJECT);
    if ($pageposts):
        $arrPaginasConPrecio = array();
        $valor_maximo = $valor_minimo = 0;
        foreach ($pageposts as $post):
            setup_postdata($post);
            $importe = -1;
            if (get_post_meta($post->ID, 'precio', true)) {
                $importe = get_post_meta($post->ID, 'precio', true);
                if (($importe >= $rangoPrecioDesdePorPost) and ($importe <= $rangoPrecioHastaPorPost)) {
                    $arrPaginasConPrecio[] = array(
                        'precio' => $importe,
                        'pID' => $post->ID,
                        'titulo' => $post->post_title,
                        'url' => get_permalink($post->ID)
                    );
                }
                if (($valor_maximo == $valor_minimo) and ($valor_maximo == 0)) {
                    $valor_maximo = $valor_minimo = $importe;
                } else {
                    if ($importe > $valor_maximo)
                        $valor_maximo = $importe;
                    if ($importe < $valor_minimo)
                        $valor_minimo = $importe;
                }
            }
        endforeach;
        $contadorElementosArray = count($arrPaginasConPrecio);

        asort($arrPaginasConPrecio);
        /*
          echo '<pre>';
          print_r($arrPaginasConPrecio);
          echo '</pre>';
          echo "<h3>VMax: $valor_maximo</h3>";
          echo "<h3>VMin: $valor_minimo</h3>";
         */
        $booleanoColorLinea = false;
        ?>
        <br />
        <table cellpadding="3" cellspacing="0" border="1">
        <?php
        foreach ($arrPaginasConPrecio as $unRegistroSolito) {
            $unRegistroSolito = (OBJECT) $unRegistroSolito;
            $booleanoColorLinea = (!$booleanoColorLinea);
            ?>
                <tr style="background-color: <?php echo ($booleanoColorLinea == true) ? '#E5E3E3' : '#F2F2F2'; ?>">
                    <td><a href="<?php echo $unRegistroSolito->url; ?>"><?php echo $unRegistroSolito->titulo; ?></a></td>
                    <td style="text-align: right;"><?php echo $unRegistroSolito->precio; ?></td>
                </tr>
            <?php
            echo "\n";
        }
        ?>
        </table>
        <br />
    <?php endif;  // if ($pageposts):  ?>
    <div id="identificador_rango_precios_dde_hta" style="background-color: #eaedea; padding: 15px 25px;">
        <span style="font-size: 15px; color: #8a988a">B&uacute;squeda de productos por precio</span><br />
        <p>Establezca el rango de precios deseados desde/hasta</p>
        <form id="formulario_rango_precios_dde_hta" method="post" action="">
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="text-align: right;">Precio m&iacute;nimo:</td>
                    <td><input type="text" name="rango_precio_desde" id="rango_precio_desde" maxlength="10" size="5" style="background-color: #d7dfd7; margin:0 7px 0 5px; width:50px;" value="<?php echo ($rangoPrecioDesdePorPost > 0) ? $rangoPrecioDesdePorPost : $valor_minimo; ?>" onmouseover="this.focus()" onfocus="this.select()" /></td>
                    <td style="text-align: right;">Precio m&aacute;ximo:</td>
                    <td><input type="text" name="rango_precio_hasta" id="rango_precio_hasta" maxlength="10" size="5" style="background-color: #d7dfd7; margin:0 7px 0 5px; width:50px;" value="<?php echo ($rangoPrecioHastaPorPost > 0) ? $rangoPrecioHastaPorPost : $valor_maximo; ?>" onmouseover="this.focus()" onfocus="this.select()" /></td>
                    <td><input type="submit" id="definir_rango_dh" value=" Establecer &raquo; " /></td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}

/**
 * Hace el reemplazo del meta-codigo [precio_rango_dde_hta] por el formulario
 * para que el usuario elija que rengo de precios desde/hasta desea ver.
 * @param integer $rango
 * @return integer
 */
function Mostrar_Form_Rango($rango) {
    $rango = preg_replace("/\[precio_rango_dde_hta\]/ise", "Mostrar_Form_Busqueda_Rango_Precios_Desde_Hasta()", $rango);
    return $rango;
}

/**
 * Enganche del plugin con WordPress.
 */
add_filter('the_content', 'Mostrar_Precio', 7);
add_filter('the_content', 'Mostrar_Form_Rango', 7);
