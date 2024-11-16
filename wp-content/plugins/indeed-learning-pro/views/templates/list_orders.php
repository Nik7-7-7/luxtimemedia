<div>
    <?php if (empty($data ['orders'])):?>
        <div class="ulp-additional-message"><?php esc_html_e('You have no Orders yet!', 'ulp');?></div>
    <?php else : ?>
        <table class="ulp-table-general ulp-list-orders">
            <thead>
                <tr>
                    <th><?php esc_html_e('Course', 'ulp');?></th>
                    <th><?php esc_html_e('Amount', 'ulp');?></th>
                    <th><?php esc_html_e('Date', 'ulp');?></th>
                    <?php if ($data ['show_invoices']):?>
                        <th><?php esc_html_e('Invoices', 'ulp');?></th>
                    <?php endif;?>
                    <th><?php esc_html_e('Status', 'ulp');?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data ['orders'] as $object):?>
                    <tr>
                        <td class="ulp-special-column"><a href="<?php echo Ulp_Permalinks::getForCourse($object->metas['course_id']);?>" target="_blank"><?php echo esc_ulp_content($object->metas['course_label']);?></a></td>
                        <td><?php echo ulp_format_price($object->metas['amount']);?></td>
                        <td><?php echo ulp_print_date_like_wp($object->post_date);?></td>
                        <?php if ($data ['show_invoices'] ):?>
                            <td>
                              <?php if (!empty($data['ulp_invoices_only_completed_payments']) && isset( $object->post_status ) && $object->post_status !== 'ulp_complete' ):?>
                                -
                              <?php else:?>
                                    <i class="fa-ulp fa-invoice-preview-ulp ulp-pointer" onclick="ulpOpenInvoice(<?php echo esc_attr($object->ID);?>);"></i>
                              <?php endif;?>
                            </td>
                        <?php endif;?>
                        <td><strong><?php echo ulp_convert_order_status_to_readable_str($object->post_status);?></strong></td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endif;?>
</div>
