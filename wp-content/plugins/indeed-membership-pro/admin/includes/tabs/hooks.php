<?php do_action( 'ump_admin_after_top_menu_add_ons' );?>

<div class="iump-wrapper">

	<div class="col-right">

			<div class="iump-page-title"><?php esc_html_e('Ultimate Membership Pro - Filters & Hooks', 'ihc');?></div>

		        <?php if ( $data ):?>
		            <table class="wp-list-table widefat fixed tags ihc-admin-tables" >
										<thead>
				                <tr>
				                    <th class="manage-column"><?php esc_html_e('Name', 'ihc');?></th>
						                <th class="manage-column" width="50px;"><?php esc_html_e('Type', 'ihc');?></th>
				                    <th class="manage-column"><?php esc_html_e('Description', 'ihc');?></th>
				                    <th class="manage-column"><?php esc_html_e('File', 'ihc');?></th>
				                </tr>
										</thead>
										<tbody>
				            <?php foreach ( $data as $hookName => $hookData ):?>
				                <tr>
				                    <td class="manage-column"><?php echo esc_html($hookName);?></td>
						                <td class="manage-column"><?php echo esc_html($hookData['type']);?></td>
				                    <td class="manage-column"><?php echo esc_html($hookData['description']);?></td>
				                    <td class="manage-column">
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
														<th class="manage-column"><?php esc_html_e('Name', 'ihc');?></th>
														<th class="manage-column"><?php esc_html_e('Type', 'ihc');?></th>
														<th class="manage-column"><?php esc_html_e('Description', 'ihc');?></th>
														<th class="manage-column"><?php esc_html_e('File', 'ihc');?></th>
												</tr>
										</tfoot>
								</table>
		        <?php endif;?>

	</div>

</div>
