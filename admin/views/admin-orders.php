<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$status_filter = sanitize_text_field( $_GET['status'] ?? '' );
$paged  = max( 1, absint( $_GET['paged'] ?? 1 ) );
$limit  = 20;
$offset = ( $paged - 1 ) * $limit;

$orders = Integrare_Orders::get_all_orders( $status_filter, $limit, $offset );
$total  = Integrare_Orders::count_orders( $status_filter );
$pages  = ceil( $total / $limit );

$counts = array(
    'all'       => Integrare_Orders::count_orders(),
    'pending'   => Integrare_Orders::count_orders( 'pending' ),
    'paid'      => Integrare_Orders::count_orders( 'paid' ),
    'completed' => Integrare_Orders::count_orders( 'completed' ),
    'cancelled' => Integrare_Orders::count_orders( 'cancelled' ),
);

$status_labels = array(
    'pending'   => array( 'label' => 'Pendiente', 'color' => '#f59e0b' ),
    'paid'      => array( 'label' => 'Pagado',    'color' => '#3b82f6' ),
    'completed' => array( 'label' => 'Completado', 'color' => '#22c55e' ),
    'cancelled' => array( 'label' => 'Cancelado',  'color' => '#ef4444' ),
);
?>
<div class="wrap">
    <h1>Pedidos — Integrare</h1>

    <?php if ( isset( $_GET['updated'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Estado actualizado.</p></div>
    <?php endif; ?>

    <!-- Status Tabs -->
    <ul class="subsubsub">
        <li>
            <a href="<?php echo admin_url( 'admin.php?page=integrare-orders' ); ?>"
               class="<?php echo ! $status_filter ? 'current' : ''; ?>">
                Todos <span class="count">(<?php echo $counts['all']; ?>)</span>
            </a> |
        </li>
        <?php foreach ( $status_labels as $key => $s ) : ?>
        <li>
            <a href="<?php echo admin_url( 'admin.php?page=integrare-orders&status=' . $key ); ?>"
               class="<?php echo $status_filter === $key ? 'current' : ''; ?>">
                <?php echo $s['label']; ?> <span class="count">(<?php echo $counts[ $key ]; ?>)</span>
            </a> <?php echo $key !== 'cancelled' ? '|' : ''; ?>
        </li>
        <?php endforeach; ?>
    </ul>

    <table class="wp-list-table widefat fixed striped" style="margin-top:12px;">
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Cliente</th>
                <th>Email</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th style="width:180px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $orders ) ) : ?>
                <tr><td colspan="7">No hay pedidos.</td></tr>
            <?php else : ?>
                <?php foreach ( $orders as $order ) :
                    $sl = $status_labels[ $order['status'] ] ?? array( 'label' => $order['status'], 'color' => '#888' );
                ?>
                <tr>
                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                    <td><?php echo esc_html( $order['display_name'] ?: 'N/A' ); ?></td>
                    <td><?php echo esc_html( $order['user_email'] ?: '—' ); ?></td>
                    <td><strong>$<?php echo number_format( $order['total'], 2 ); ?></strong></td>
                    <td>
                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;background:<?php echo $sl['color']; ?>22;color:<?php echo $sl['color']; ?>;">
                            <?php echo $sl['label']; ?>
                        </span>
                    </td>
                    <td><?php echo date_i18n( 'd/m/Y H:i', strtotime( $order['created_at'] ) ); ?></td>
                    <td>
                        <?php if ( $order['status'] === 'pending' || $order['status'] === 'paid' ) : ?>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=integrare-orders&integrare_action=update_status&order_id=' . $order['id'] . '&status=completed' ), 'integrare_status_update' ); ?>"
                               class="button button-small button-primary">Completar</a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=integrare-orders&integrare_action=update_status&order_id=' . $order['id'] . '&status=cancelled' ), 'integrare_status_update' ); ?>"
                               class="button button-small" onclick="return confirm('¿Cancelar este pedido?');">Cancelar</a>
                        <?php else : ?>
                            <span style="color:#aaa;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ( $pages > 1 ) : ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php for ( $i = 1; $i <= $pages; $i++ ) : ?>
                <?php if ( $i === $paged ) : ?>
                    <span class="tablenav-pages-navspan button disabled"><?php echo $i; ?></span>
                <?php else : ?>
                    <a href="<?php echo admin_url( 'admin.php?page=integrare-orders&paged=' . $i . ( $status_filter ? '&status=' . $status_filter : '' ) ); ?>"
                       class="button"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
