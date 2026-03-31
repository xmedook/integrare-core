<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Pricing {

    /**
     * Calculate full pricing breakdown for a product + quantity of boxes.
     *
     * @param int $product_id  Post ID of farmacia_product.
     * @param int $cajas       Number of boxes.
     * @return array|WP_Error  Pricing data or error.
     */
    public static function calculate( $product_id, $cajas ) {
        $product_id = absint( $product_id );
        $cajas      = max( 1, absint( $cajas ) );

        $precio_unitario = (float) get_post_meta( $product_id, '_integrare_precio_unitario', true );
        $unidades_caja   = (int) get_post_meta( $product_id, '_integrare_unidades_caja', true );
        $stock           = (int) get_post_meta( $product_id, '_integrare_stock', true );
        $descuentos      = get_post_meta( $product_id, '_integrare_descuentos', true );

        if ( ! $precio_unitario || ! $unidades_caja ) {
            return new WP_Error( 'missing_data', 'Producto sin datos de precio o unidades.' );
        }

        if ( ! is_array( $descuentos ) || empty( $descuentos ) ) {
            $descuentos = array( array( 'min_cajas' => 1, 'descuento_pct' => 0 ) );
        }

        // Find applicable tier (highest min_cajas that $cajas qualifies for)
        $applied_tier = $descuentos[0];
        $tier_index   = 0;
        // Sort descending by min_cajas to find the best tier
        $sorted = $descuentos;
        usort( $sorted, function( $a, $b ) {
            return $b['min_cajas'] - $a['min_cajas'];
        });

        foreach ( $sorted as $idx => $tier ) {
            if ( $cajas >= $tier['min_cajas'] ) {
                $applied_tier = $tier;
                break;
            }
        }

        // Find tier index in original array
        foreach ( $descuentos as $idx => $tier ) {
            if ( $tier['min_cajas'] == $applied_tier['min_cajas'] ) {
                $tier_index = $idx;
                break;
            }
        }

        $discount_pct    = (float) $applied_tier['descuento_pct'];
        $total_units     = $cajas * $unidades_caja;
        $discounted_price = round( $precio_unitario * ( 1 - $discount_pct / 100 ), 2 );
        $line_total      = round( $discounted_price * $total_units, 2 );
        $savings         = round( ( $precio_unitario * $total_units ) - $line_total, 2 );

        return array(
            'product_id'       => $product_id,
            'unit_price'       => $precio_unitario,
            'units_per_box'    => $unidades_caja,
            'quantity_boxes'   => $cajas,
            'total_units'      => $total_units,
            'discount_pct'     => $discount_pct,
            'discounted_price' => $discounted_price,
            'line_total'       => $line_total,
            'savings'          => $savings,
            'tier_index'       => $tier_index,
            'tier_label'       => 'Nivel ' . ( $tier_index + 1 ),
            'stock'            => $stock,
            'stock_sufficient' => $stock >= $total_units,
            'tiers'            => $descuentos,
        );
    }

    /**
     * Get the discount table data for display.
     *
     * @param int $product_id  Post ID.
     * @return array           Rows for the discount table.
     */
    public static function get_discount_table( $product_id ) {
        $precio   = (float) get_post_meta( $product_id, '_integrare_precio_unitario', true );
        $upb      = (int) get_post_meta( $product_id, '_integrare_unidades_caja', true );
        $tiers    = get_post_meta( $product_id, '_integrare_descuentos', true );

        if ( ! is_array( $tiers ) ) {
            $tiers = array( array( 'min_cajas' => 1, 'descuento_pct' => 0 ) );
        }

        $table = array();
        foreach ( $tiers as $tier ) {
            $disc  = (float) $tier['descuento_pct'];
            $dprice = round( $precio * ( 1 - $disc / 100 ), 2 );
            $units  = $tier['min_cajas'] * $upb;
            $table[] = array(
                'min_cajas'     => $tier['min_cajas'],
                'discount_pct'  => $disc,
                'price_per_unit' => $dprice,
                'total_units'   => $units,
                'savings_per_unit' => round( $precio - $dprice, 2 ),
            );
        }

        return $table;
    }
}
