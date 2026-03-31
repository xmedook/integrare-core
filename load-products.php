<?php
/**
 * Cargar productos de prueba en Integrare.
 * Visitar: https://integrare.mx/wp-admin/admin-ajax.php?action=integrare_load_products&key=integrare2025
 * Borrar este archivo después de usarlo.
 */

add_action( 'wp_ajax_integrare_load_products', function() {
    if ( ! current_user_can( 'manage_options' ) || ( $_GET['key'] ?? '' ) !== 'integrare2025' ) {
        wp_die( 'No autorizado.' );
    }

    $productos = array(
        array( 'nombre' => 'Solución CS C/1000ML FLX SI',              'precio' => 27.00,  'unidades' => 1 ),
        array( 'nombre' => 'Solución CS PISA P/Irrigación C/1000ML',    'precio' => 34.57,  'unidades' => 1 ),
        array( 'nombre' => 'Solución HM C/Potasio 1:35.83 C/3.78L',    'precio' => 137.22, 'unidades' => 1 ),
        array( 'nombre' => 'Bipodial 1:35:83 C/851G SI',               'precio' => 71.04,  'unidades' => 1 ),
        array( 'nombre' => 'Inhepar 1000UI/ML C/10ML 1 FAMP SI',       'precio' => 93.45,  'unidades' => 1 ),
        array( 'nombre' => 'Flebotek S/Aguja',                         'precio' => 10.91,  'unidades' => 1 ),
        array( 'nombre' => 'Flebotek CAPD LV',                         'precio' => 198.94, 'unidades' => 1 ),
        array( 'nombre' => 'Hemoline MOD UNIV C/PROT TRAN',            'precio' => 169.47, 'unidades' => 1 ),
        array( 'nombre' => 'Kit C/D Fistula HD 4 Campos',              'precio' => 69.09,  'unidades' => 1 ),
        array( 'nombre' => 'Kit C/D Cateter HD',                       'precio' => 70.53,  'unidades' => 1 ),
    );

    $created = 0;

    foreach ( $productos as $prod ) {
        $post_id = wp_insert_post( array(
            'post_title'  => $prod['nombre'],
            'post_type'   => 'farmacia_product',
            'post_status' => 'publish',
        ) );

        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, '_integrare_sku', 'SKU-' . str_pad( $post_id, 5, '0', STR_PAD_LEFT ) );
            update_post_meta( $post_id, '_integrare_precio_unitario', $prod['precio'] );
            update_post_meta( $post_id, '_integrare_unidades_caja', $prod['unidades'] );
            update_post_meta( $post_id, '_integrare_stock', 100 );
            update_post_meta( $post_id, '_integrare_descuentos', array(
                array( 'min_qty' => 10,  'discount' => 5 ),
                array( 'min_qty' => 25,  'discount' => 10 ),
                array( 'min_qty' => 50,  'discount' => 15 ),
                array( 'min_qty' => 100, 'discount' => 20 ),
            ) );
            $created++;
        }
    }

    wp_die( "✅ Se crearon {$created} productos exitosamente. Ya puedes borrar load-products.php" );
});
