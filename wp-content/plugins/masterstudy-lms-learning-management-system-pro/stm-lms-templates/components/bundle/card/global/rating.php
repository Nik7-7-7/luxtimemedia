<?php
/**
 * @var $bundle
 */

$stars   = range( 1, 5 );
$average = round( $bundle['rating']['average'] / $bundle['rating']['count'], 2 );
?>

<div class="masterstudy-bundle-card__rating">
	<?php foreach ( $stars as $star ) { ?>
		<span class="masterstudy-bundle-card__rating-star <?php echo esc_attr( $star <= floor( $average ) ? 'masterstudy-bundle-card__rating-star_filled ' : '' ); ?>"></span>
	<?php } ?>
	<div class="masterstudy-bundle-card__rating-count">
		<?php echo number_format( $average, 1, '.', '' ); ?>
	</div>
</div>
