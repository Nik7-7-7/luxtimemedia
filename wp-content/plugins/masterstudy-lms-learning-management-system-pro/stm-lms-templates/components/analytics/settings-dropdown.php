<?php
/**
 * @var string $id
 * @var array $menu_items
 */

$menu_items = ! empty( $menu_items ) ? $menu_items : array();
?>

<div data-id="masterstudy-settings-dropdown-<?php echo esc_attr( $id ); ?>" class="masterstudy-settings-dropdown">
	<div class="masterstudy-settings-dropdown__menu">
		<?php foreach ( $menu_items as $item ) { ?>
			<span data-id="<?php echo esc_attr( $item['id'] ); ?>" class="masterstudy-settings-dropdown__item">
				<?php echo esc_html( $item['title'] ); ?>
			</span>
		<?php } ?>
	</div>
</div>
