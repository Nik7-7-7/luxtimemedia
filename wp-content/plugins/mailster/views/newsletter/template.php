<?php

$editable = ! in_array( $post->post_status, array( 'active', 'finished' ) );
if ( isset( $_GET['showstats'] ) && $_GET['showstats'] ) {
	$editable = false;
}

$custom_list = $this->templateobjcustom->get_modules_list();
$module_list = $this->templateobj->get_modules_list();

$classes = array( 'load' );
if ( $editable ) {
		$classes[] = 'is-editable';
	if ( mailster_get_user_setting( 'showmodules', true ) && ! empty( $module_list ) ) {
		$classes[] = 'show-modules';
	}
	if ( ! empty( $module_list ) ) {
		$classes[] = 'has-modules';
	}
}

?>
<div id="template-wrap" class="<?php echo implode( ' ', $classes ); ?>">

<?php if ( $editable ) : ?>

	<?php include 'optionbar.php'; ?>
	<?php include 'editbar.php'; ?>

<?php else : ?>

	<?php $stats['total'] = $this->get_clicks( $post->ID, true ); ?>
	<?php $stats['clicks'] = $this->get_clicked_links( $post->ID ); ?>

	<div id="mailster_click_stats" data-stats='<?php echo json_encode( $stats ); ?>'></div>
	<div id="clickmap-stats">
		<div class="piechart" data-percent="0" data-size="60" data-line-width="8" data-animate="500"><span>0</span>%</div>
		<p><strong class="link"></strong></p>
		<p><?php esc_html_e( 'Clicks', 'mailster' ); ?>: <strong class="clicks">0</strong><br><?php esc_html_e( 'Total', 'mailster' ); ?>: <strong class="total">0</strong></p>
	</div>
	<textarea id="content" name="content" class="hidden" autocomplete="off"><?php echo esc_textarea( $post->post_content ); ?></textarea>
	<textarea id="excerpt" name="excerpt" class="hidden" autocomplete="off"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>

<?php endif; ?>

<?php if ( ! $this->templateobj->exists ) : ?>

	<div class="notice notice-warning inline">
		<p>
			<strong><?php esc_html_e( 'The template file for this campaign is missing. Make sure you have installed the correct template.', 'mailster' ); ?></strong>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=newsletter&page=mailster_templates&search=' . esc_attr( $this->templateobj->slug ) . '&type=slug' ) ); ?>"><?php esc_html_e( 'Find the missing template file.', 'mailster' ); ?></a>
		</p>
	</div>

<?php endif; ?>

	<div id="plain-text-wrap">
		<?php $autoplaintext = ! isset( $this->post_data['autoplaintext'] ) || $this->post_data['autoplaintext']; ?>
		<p>
			<label><input type="checkbox" id="plaintext" name="mailster_data[autoplaintext]" value="1" <?php checked( $autoplaintext ); ?>> <?php esc_html_e( 'Create the plain text version based on the HTML version of the campaign', 'mailster' ); ?></label> <a class="alignright button button-primary getplaintext"><?php esc_html_e( 'get text from HTML version', 'mailster' ); ?></a>
		</p>

		<textarea id="excerpt" name="excerpt" class="<?php echo $autoplaintext ? ' disabled' : ''; ?>" autocomplete="off" <?php disabled( $autoplaintext ); ?>><?php echo $post->post_excerpt; ?></textarea>
	</div>

	<div id="html-wrap">
		<?php
		if ( $editable && ! empty( $module_list ) ) :
			$screenshot_modules_folder     = MAILSTER_UPLOAD_DIR . '/screenshots/';
			$screenshot_modules_folder_uri = MAILSTER_UPLOAD_URI . '/screenshots/';
			?>
			<div id="module-selector">
				<a class="toggle-modules mailster-btn mailster-icon" title="<?php esc_attr_e( 'Modules', 'mailster' ); ?>"></a>
				<div id="module-search-wrap">
					<input type="text" class="widefat" id="module-search" placeholder="<?php esc_attr_e( 'Search Modules...', 'mailster' ); ?>">
					<a id="module-search-remove" href="#" title="<?php esc_attr_e( 'clear search', 'mailster' ); ?>">&#10005;</a>
				</div>
				<div class="inner">
					<?php if ( ! empty( $custom_list ) ) : ?>
					<ul class="custom-modules">
						<?php foreach ( $custom_list as $i => $module ) : ?>
							<?php echo $module['module']; ?>
						<?php endforeach; ?>
					</ul>
					<?php else : ?>
					<ul class="custom-modules"></ul>
					<?php endif; ?>
					<ul class="default-modules">
					<?php foreach ( $module_list as $i => $module ) : ?>
						<?php echo $module['module']; ?>
					<?php endforeach; ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>

		<input type="hidden" id="editor-height" name="mailster_data[editor_height]" value="<?php echo esc_attr( $this->post_data['editor_height'] ); ?>">
		<div id="iframe-wrap">

			<?php
			$url = add_query_arg(
				array(
					'action'       => 'mailster_get_template',
					'id'           => $post->ID,
					'template'     => $this->get_template(),
					'templatefile' => $this->get_file(),
					'editorstyle'  => $editable,
					'_wpnonce'     => wp_create_nonce( 'mailster_nonce' ),
					'nocache'      => time(),
				),
				admin_url( 'admin-ajax.php' )
			)
			?>
			<iframe id="mailster_iframe" class="loading" data-src="<?php echo esc_url( $url ); ?>" width="100%" height="<?php echo esc_attr( $this->post_data['editor_height'] ); ?>" scrolling="no" frameborder="0" data-no-lazy="">
			</iframe>
		</div>
		<div id="module-save-dialog" style="display:none;">
		<div class="mailster_template_save">
			<div class="inner">
				<div class="alignright">
					<?php echo mailster()->beacon( '6555c7f616990139f308b93f' ); ?>				
				</div>
				<p>
					<label><?php esc_html_e( 'Module Name', 'mailster' ); ?><br><input type="text" class="widefat" id="new_module_name" placeholder="<?php esc_attr_e( 'Module name', 'mailster' ); ?>" value=""></label>
				</p>
				<p class="description">
					<?php esc_html_e( 'Save this module to use it later. Custom modules will appear at the top of the module selector list.', 'mailster' ); ?>					
				</p>
				<input type="hidden" id="new_module_type">
				<textarea id="new_module_content" class="hidden"></textarea>				

			</div>
			<div class="foot">			
				<button class="button button-primary save-module"><?php esc_html_e( 'Save', 'mailster' ); ?></button>
				<button class="button save-module-cancel"><?php esc_html_e( 'Cancel', 'mailster' ); ?></button>
				<span class="spinner" id="new_module-ajax-loading"></span>
			</div>
	</div>
		</div>
	</div>

</div>

<textarea id="content" autocomplete="off" name="content"><?php echo esc_textarea( $post->post_content ); ?></textarea>
<textarea id="head" name="mailster_data[head]" autocomplete="off"><?php echo esc_textarea( isset( $this->post_data['head'] ) ? $this->post_data['head'] : $this->templateobj->get_head() ); ?></textarea>
