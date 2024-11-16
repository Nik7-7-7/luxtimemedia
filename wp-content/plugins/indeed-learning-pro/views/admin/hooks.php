<div class="ulp-wrapper">

	<div class="col-right">

			<div class="ulp-page-title"><?php esc_html_e('Ultimate Learning Pro - Filters & Hooks', 'ulp');?></div>

		        <?php if ( $data ):?>
		            <table class="wp-list-table widefat fixed tags ulp-admin-tables" >
										<thead>
				                <tr>
				                    <th class="manage-column"><?php esc_html_e('Name', 'ulp');?></th>
						                <th class="manage-column ulp-hooks-small-col"><?php esc_html_e('Type', 'ulp');?></th>
				                    <th class="manage-column"><?php esc_html_e('Description', 'ulp');?></th>
				                    <th class="manage-column"><?php esc_html_e('File', 'ulp');?></th>
				                </tr>
										</thead>
										<tbody>
				            <?php foreach ( $data as $hookName => $hookData ):?>
				                <tr>
				                    <td class="manage-column"><?php echo esc_html($hookName);?></td>
						                <td class="manage-column"><?php echo esc_html($hookData['type']);?></td>
				                    <td class="manage-column"><?php echo esc_hhtml($hookData['description']);?></td>
				                    <td class="manage-column ulp-hooks-filename">
																<?php if ( $hookData['file'] && is_array( $hookData['file'] ) ):?>
																		<?php foreach ( $hookData['file'] as $file ):?>
																				<div><?php echo esc_html($file);?></div>
																		<?php endforeach;?>
																<?php endif;?>
														</td>
				                </tr>
				            <?php endforeach;?>
										</tbody>
										<tfoot>
												<tr>
														<th class="manage-column"><?php esc_html_e('Name', 'ulp');?></th>
														<th class="manage-column"><?php esc_html_e('Type', 'ulp');?></th>
														<th class="manage-column"><?php esc_html_e('Description', 'ulp');?></th>
														<th class="manage-column"><?php esc_html_e('File', 'ulp');?></th>
												</tr>
										</tfoot>
								</table>
		        <?php endif;?>

	</div>

</div>
