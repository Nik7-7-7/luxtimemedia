<?php

/**
 * @var string $id
 * @var string $title
 * @var string $text
 * @var array $settings
 * @var string $submit_button_text
 * @var string $submit_button_style
 */

$submit_button_style = $submit_button_style ?? '';
$settings            = $settings ?? array();
?>

<div data-id="masterstudy-settings-modal-<?php echo esc_attr( $id ); ?>" class="masterstudy-settings-modal" style="display:none">
	<div class="masterstudy-settings-modal__wrapper">
		<div class="masterstudy-settings-modal__header">
			<span class="masterstudy-settings-modal__header-title">
				<?php echo esc_html( $title ); ?>
			</span>
			<span class="masterstudy-settings-modal__header-close"></span>
		</div>
		<div class="masterstudy-settings-modal__text">
			<?php echo esc_html( $text ); ?>
		</div>
		<?php if ( ! empty( $settings ) ) { ?>
			<div class="masterstudy-settings-modal__items">
				<?php foreach ( $settings as $item ) { ?>
					<div id="<?php echo esc_attr( $item['id'] ); ?>" class="masterstudy-settings-modal__item">
						<div class="masterstudy-settings-modal__item-wrapper masterstudy-settings-modal__item-wrapper_fill">
							<div class="masterstudy-settings-modal__switch">
								<span class="masterstudy-settings-modal__switch-slider"></span>
							</div>
							<?php echo esc_html( $item['title'] ); ?>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>
