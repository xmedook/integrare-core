<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_CPT {

    public function __construct() {
        add_action( 'init', array( __CLASS__, 'register_post_type' ) );
        add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
    }

    /**
     * Register the farmacia_product CPT.
     */
    public static function register_post_type() {
        $labels = array(
            'name'               => 'Productos Farmacia',
            'singular_name'      => 'Producto',
            'menu_name'          => 'Productos Farmacia',
            'add_new'            => 'Agregar Producto',
            'add_new_item'       => 'Agregar Nuevo Producto',
            'edit_item'          => 'Editar Producto',
            'new_item'           => 'Nuevo Producto',
            'view_item'          => 'Ver Producto',
            'search_items'       => 'Buscar Productos',
            'not_found'          => 'No se encontraron productos',
            'not_found_in_trash' => 'No hay productos en la papelera',
            'all_items'          => 'Todos los Productos',
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => 'productos' ),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-cart',
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest'        => true,
        );

        register_post_type( 'farmacia_product', $args );
    }

    /**
     * Register the producto_categoria taxonomy.
     */
    public static function register_taxonomy() {
        $labels = array(
            'name'              => 'Categorías de Producto',
            'singular_name'     => 'Categoría',
            'search_items'      => 'Buscar Categorías',
            'all_items'         => 'Todas las Categorías',
            'parent_item'       => 'Categoría Padre',
            'parent_item_colon' => 'Categoría Padre:',
            'edit_item'         => 'Editar Categoría',
            'update_item'       => 'Actualizar Categoría',
            'add_new_item'      => 'Agregar Nueva Categoría',
            'new_item_name'     => 'Nombre de Nueva Categoría',
            'menu_name'         => 'Categorías',
        );

        register_taxonomy( 'producto_categoria', 'farmacia_product', array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => false,
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'categoria-producto' ),
            'show_in_rest'      => true,
        ) );
    }
}
