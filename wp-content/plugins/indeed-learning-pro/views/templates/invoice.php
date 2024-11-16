<span class="ulp-js-init-print-this" data-load_css="<?php echo ULP_URL . 'assets/css/public.css';?>"></span>


<?php if($data['ulp_invoices_custom_css'] !== ''){
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes($data['ulp_invoices_custom_css']) );
} ?>




<div class="ulp-invoice-bttn-wrapp">

	<div class="ulp-popup-print-bttn" onClick="ulpInitPrinthis('<?php echo esc_attr('#' . $data['wrapp_id']);?>');"><?php esc_html_e('Print Invoice', 'ulp');?></div>

</div>





<div class="ulp-invoice-wrapp <?php echo esc_attr($data['ulp_invoices_template']);?>" id="<?php echo esc_attr($data['wrapp_id']);?>" >

	<div class="ulp-invoice-logo"><img src="<?php echo esc_url($data['ulp_invoices_logo']);?>" /></div>

	<div class="ulp-invoice-title"><?php echo esc_ulp_content($data['ulp_invoices_title']);?></div>

	<div class="ulp-clear"></div>

	<div class="ulp-invoice-company-field"><?php echo esc_ulp_content($data['ulp_invoices_company_field']);?></div>

	<div class="ulp-invoice-invoice-code">

		<?php if (!empty($data['order_details']['code'])):?>

			<div><b><?php esc_html_e('Invoice code:', 'ulp');?></b> <?php echo esc_ulp_content($data['order_details']['code']);?></div>

		<?php endif;?>

		<?php if (!empty($data['order_details']['txn_id'])):?>

			<div><b><?php esc_html_e('Transaction ID:', 'ulp');?></b> <?php echo esc_html($data['order_details']['txn_id']);?></div>

		<?php endif;?>

		<?php if (!empty($data['order_details']['create_date'])):?>

			<div><b><?php esc_html_e('Date:', 'ulp');?></b> <?php echo esc_html($data['order_details']['create_date']);?></div>

		<?php endif;?>

	</div>

	<div class="ulp-clear"></div>

	<div class="ulp-invoice-client-details"><?php echo esc_ulp_content($data['ulp_invoices_bill_to']);?></div>

	<div class="ulp-clear"></div>



	<div class="ulp-invoice-list-details">

		<table>

			<thead>

				<tr>

					<td width="5%">#</td>

					<td width="75%"><?php esc_html_e('Description', 'ulp');?></td>

					<td width="20%"><?php esc_html_e('Amount', 'ulp');?></td>

				</tr>

			</thead>

			<tbody>

				<?php $i = 1; ?>

				<?php if (!empty($data['course_price']) && !empty($data['course_label'])):?>

					<tr <?php echo ($i%2==0) ? 'class="alternate"' : ''; ?> >

						<td><?php echo esc_html($i);$i++;?></td>

						<td><?php echo esc_html($data['course_label']);?></td>

						<td><?php echo esc_html($data['course_price']);?></td>

					</tr>

				<?php endif;?>

				<?php if (!empty($data['total_discount'])):?>

					<tr <?php echo ($i%2==0) ? 'class="alternate"' : ''; ?> >

						<td><?php echo esc_html($i);$i++;?></td>

						<td><?php esc_html_e('Total Discount:', 'ulp');?></td>

						<td><?php echo esc_html($data['total_discount']);?></td>

					</tr>

				<?php endif;?>

				<?php if (!empty($data['total_taxes'])):?>

					<tr <?php echo ($i%2==0) ? 'class="alternate"' : ''; ?> >

						<td><?php echo esc_html($i);$i++;?></td>

						<td><?php esc_html_e('Total Taxes:', 'ulp');?></td>

						<td><?php echo esc_html($data['total_taxes']);?></td>

					</tr>

				<?php endif;?>

				<?php if (!empty($data['total_amount'])):?>

				<?php if($i < 3){

						do{ ?>

						<tr <?php echo ($i%2==0) ? 'class="alternate"' : ''; ?> >

							<td>&nbsp;</td>

							<td>&nbsp;</td>

							<td>&nbsp;</td>

						</tr>

						<?php

							$i++;

						}while($i < 3);

					}

				?>

					<tr class="ulp-invoice-total">

						<td></td>

						<td align="right";><?php esc_html_e('Total Amount:', 'ulp');?></td>

						<td><?php echo esc_html($data['total_amount']);?></td>

					</tr>

				<?php endif;?>

			</tbody>

		</table>

	</div>



	<?php if (!empty($data['ulp_invoices_footer'])):?>

		<div class="ulp-invoice-footer"><?php echo esc_ulp_content($data['ulp_invoices_footer']);?></div>

	<?php endif;?>



</div>
