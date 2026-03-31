<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Metaboxes {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
        add_action( 'save_post_farmacia_product', array( $this, 'save_metabox_data' ), 10, 2 );
    }

    /**
     * Register the product data metabox.
     */
    public function register_metaboxes() {
        add_meta_box(
            'integrare_product_data',
            'Datos del Producto — Integrare',
            array( $this, 'render_metabox' ),
            'farmacia_product',
            'normal',
            'high'
        );
    }

    /**
     * Render the metabox HTML.
     */
    public function render_metabox( $post ) {
        wp_nonce_field( 'integrare_product_nonce', '_integrare_nonce' );

        $sku     = get_post_meta( $post->ID, '_integrare_sku', true );
        $precio  = get_post_meta( $post->ID, '_integrare_precio_unitario', true );
        $unidades = get_post_meta( $post->ID, '_integrare_unidades_caja', true );
        $stock   = get_post_meta( $post->ID, '_integrare_stock', true );
        $mostrar_precio = get_post_meta( $post->ID, '_integrare_mostrar_precio', true );
        $descuentos = get_post_meta( $post->ID, '_integrare_descuentos', true );

        if ( ! is_array( $descuentos ) || empty( $descuentos ) ) {
            $descuentos = array(
                array( 'min_cajas' => 1,  'descuento_pct' => 0 ),
                array( 'min_cajas' => 6,  'descuento_pct' => 10 ),
                array( 'min_cajas' => 12, 'descuento_pct' => 15 ),
            );
        }
        ?>
        <style>
            .integrare-metabox { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .integrare-metabox .full-width { grid-column: 1 / -1; }
            .integrare-metabox label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; }
            .integrare-metabox input[type="text"],
            .integrare-metabox input[type="number"] { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 6px; }
            .integrare-tier-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
            .integrare-tier-table th { text-align: left; padding: 8px; background: #f7f7f7; border: 1px solid #e0e0e0; font-size: 13px; }
            .integrare-tier-table td { padding: 6px 8px; border: 1px solid #e0e0e0; }
            .integrare-tier-table input { width: 80px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; }
            .integrare-section-title { font-size: 14px; font-weight: 700; margin: 0 0 8px; padding-top: 8px; border-top: 1px solid #eee; }
        </style>

        <div class="integrare-metabox">
            <div>
                <label for="integrare_sku">SKU / Código</label>
                <input type="text" id="integrare_sku" name="integrare_sku"
                       value="<?php echo esc_attr( $sku ); ?>" placeholder="Ej: IBU-400-12">
            </div>
            <div>
                <label for="integrare_precio_unitario">Precio por Unidad ($)</label>
                <input type="number" id="integrare_precio_unitario" name="integrare_precio_unitario"
                       value="<?php echo esc_attr( $precio ); ?>" step="0.01" min="0" placeholder="10.00">
            </div>
            <div>
                <label for="integrare_unidades_caja">Unidades por Caja</label>
                <input type="number" id="integrare_unidades_caja" name="integrare_unidades_caja"
                       value="<?php echo esc_attr( $unidades ); ?>" step="1" min="1" placeholder="12">
            </div>
            <div>
                <label for="integrare_stock">Stock Actual (en unidades)</label>
                <input type="number" id="integrare_stock" name="integrare_stock"
                       value="<?php echo esc_attr( $stock ); ?>" step="1" min="0" placeholder="1000">
            </div>

            <div class="full-width" style="padding-top:8px;">
                <label>
                    <input type="checkbox" name="integrare_mostrar_precio" value="1" <?php checked( $mostrar_precio, '1' ); ?>>
                    Mostrar precio a usuarios sin cuenta
                </label>
            </div>

            <div class="full-width">
                <p class="integrare-section-title">Descuentos por Volumen (Tiers)</p>
                <table class="integrare-tier-table">
                    <thead>
                        <tr>
                            <th>Tier</th>
                            <th>Mínimo de Cajas</th>
                            <th>Descuento (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $descuentos as $i => $tier ) : ?>
                        <tr>
                            <td><strong>Nivel <?php echo $i + 1; ?></strong></td>
                            <td>
                                <input type="number" name="integrare_desc_min[]"
                                       value="<?php echo esc_attr( $tier['min_cajas'] ); ?>" min="1" step="1">
                            </td>
                            <td>
                                <input type="number" name="integrare_desc_pct[]"
                                       value="<?php echo esc_attr( $tier['descuento_pct'] ); ?>" min="0" max="100" step="0.5">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="description" style="margin-top:6px;">
                    Ejemplo: Nivel 1 → 1 caja = 0%, Nivel 2 → 6 cajas = 10%, Nivel 3 → 12 cajas = 15%.
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Save metabox data with nonce + sanitization.
     */
    public function save_metabox_data( $post_id, $post ) {
        // Nonce check
        if ( ! isset( $_POST['_integrare_nonce'] ) || ! wp_verify_nonce( $_POST['_integrare_nonce'], 'integrare_product_nonce' ) ) {
            return;
        }
        // Auto-save check
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Permission check
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save simple fields
        if ( isset( $_POST['integrare_sku'] ) ) {
            update_post_meta( $post_id, '_integrare_sku', sanitize_text_field( $_POST['integrare_sku'] ) );
        }
        if ( isset( $_POST['integrare_precio_unitario'] ) ) {
            update_post_meta( $post_id, '_integrare_precio_unitario', floatval( $_POST['integrare_precio_unitario'] ) );
        }
        if ( isset( $_POST['integrare_unidades_caja'] ) ) {
            update_post_meta( $post_id, '_integrare_unidades_caja', absint( $_POST['integrare_unidades_caja'] ) );
        }
        if ( isset( $_POST['integrare_stock'] ) ) {
            update_post_meta( $post_id, '_integrare_stock', absint( $_POST['integrare_stock'] ) );
        }

        // Save discount tiers
        if ( isset( $_POST['integrare_desc_min'] ) && isset( $_POST['integrare_desc_pct'] ) ) {
            $mins = array_map( 'absint', $_POST['integrare_desc_min'] );
            $pcts = array_map( 'floatval', $_POST['integrare_desc_pct'] );
            $descuentos = array();
            for ( $i = 0; $i < count( $mins ); $i++ ) {
                $descuentos[] = array(
                    'min_cajas'     => $mins[ $i ],
                    'descuento_pct' => $pcts[ $i ],
                );
            }
            // Sort by min_cajas ascending
            usort( $descuentos, function( $a, $b ) {
                return $a['min_cajas'] - $b['min_cajas'];
            });
            update_post_meta( $post_id, '_integrare_descuentos', $descuentos );
        }

        // Show price toggle
        update_post_meta( $post_id, '_integrare_mostrar_precio', ! empty( $_POST['integrare_mostrar_precio'] ) ? '1' : '' );
    }
}
