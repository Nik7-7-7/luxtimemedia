<?php
/**
 * @var int $quantity
 */
?>
<div class="masterstudy-doughnut-chart__info">
	<?php for ( $i = 0; $i < $quantity; $i++ ) { ?>
		<div class="masterstudy-doughnut-chart__info-block">
			<div class="masterstudy-doughnut-chart__info-title__wrapper">
				<div class="masterstudy-doughnut-chart__info-icon"></div>
				<div class="masterstudy-doughnut-chart__info-title"></div>
			</div>
			<div class="masterstudy-doughnut-chart__info-percent"></div>
			<div class="masterstudy-doughnut-chart__info-value"></div>
		</div>
	<?php } ?>
</div>
