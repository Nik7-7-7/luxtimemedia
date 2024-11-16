<div class="ihc-stuffbox">
  <h3><?php esc_html_e('Order Details', 'ihc');?></h3>
  <div class="inside">
    <?php
    $orderId = isset( $_GET['order_id'] ) ? sanitize_text_field($_GET['order_id']) : 0;
    $orderObject = new \Indeed\Ihc\Db\Orders();
    $orderData = $orderObject->setId( $orderId )
                             ->fetch()
                             ->get();
    $orderMetaObject = new \Indeed\Ihc\Db\OrderMeta();
    $orderMeta = $orderMetaObject->getAllByOrderId( $orderId );

    if ( !$orderId || !$orderData ):?>
        <h5><?php esc_html_e( "No order details available!", 'ihc' );?></h5>
    <?php else :?>

        <div><?php echo esc_html__( 'Membership: ', 'ihc' ) , \Indeed\Ihc\Db\Memberships::getMembershipLabel( $orderData->lid );?></div>
        <h5><?php esc_html_e( 'Order Base Data:', 'ihc' );?></h5>
        <?php foreach ( $orderData as $key => $value ):?>
            <div>
                <?php echo esc_html($key) . ' - ' . esc_html($value);?>
            </div>
        <?php endforeach;?>
        <h5><?php esc_html_e( 'Order Meta:', 'ihc' );?></h5>
        <?php foreach ( $orderMeta as $metaKey => $metaValue ):?>
            <div>
                <?php echo esc_html($metaKey) . ' - ' . esc_html($metaValue);?>
            </div>
        <?php endforeach;?>
    <?php endif;?>
  </div>
</div>
