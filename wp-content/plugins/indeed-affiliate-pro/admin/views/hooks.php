<div class="uap-wrapper">

	<div class="col-right">

			<div class="uap-page-title"><?php esc_html_e('Ultimate Affiliate Pro - Filters & Hooks', 'uap');?></div>

		        <?php if ( $data ):?>
		            <table class="wp-list-table widefat fixed tags uap-admin-tables" >
										<thead>
				                <tr>
				                    <th class="manage-column"><?php esc_html_e('Name', 'uap');?></th>
						                <th class="manage-column" s width"10%"><?php esc_html_e('Type', 'uap');?></th>
				                    <th class="manage-column"><?php esc_html_e('Description', 'uap');?></th>
				                    <th class="manage-column"><?php esc_html_e('File', 'uap');?></th>
				                </tr>
										</thead>
										<tbody>
				            <?php foreach ( $data as $hookName => $hookData ):?>
				                <tr>
				                    <td class="manage-column"><?php echo esc_html($hookName);?></td>
						                <td class="manage-column"><?php echo esc_html($hookData['type']);?></td>
				                    <td class="manage-column"><?php echo esc_uap_content($hookData['description']);?></td>
				                    <td class="manage-column uap-hooks-files">
																<?php if ( $hookData['file'] && is_array( $hookData['file'] ) ):?>
																		<?php foreach ( $hookData['file'] as $file ):?>
																				<div><?php echo esc_uap_content($file);?></div>
																		<?php endforeach;?>
																<?php endif;?>
														</td>
				                </tr>
				            <?php endforeach;?>
										</tbody>
										<tfoot>
												<tr>
														<th class="manage-column"><?php esc_html_e('Name', 'uap');?></th>
														<th class="manage-column" width"10%"><?php esc_html_e('Type', 'uap');?></th>
														<th class="manage-column"><?php esc_html_e('Description', 'uap');?></th>
														<th class="manage-column"><?php esc_html_e('File', 'uap');?></th>
												</tr>
										</tfoot>
								</table>
		        <?php endif;?>

	</div>

</div>
