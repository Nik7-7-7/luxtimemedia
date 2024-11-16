<div class="ulp-reviews-rating-awesome-box-wrapp">
    <diu class="ulp-reviews-rating-awesome-box-content">
        <div class="ulp-review-summary" >
            <div class="ulp-review-summary-average"><?php echo esc_html($data['averageRating']);?></div>
            <div class="ulp-review-summary-stars"><?php echo ulp_generate_stars($data['averageRating']);?></div>
            <div><?php esc_html_e('Average Rating', 'ulp');?></div>
        </div>
        <div class="ulp-review-rates">
            <?php if (empty($data['ratingPercentages'])):?>
                <?php esc_html_e('No ratings', 'ulp');?>
            <?php else :?>
                <?php for ($i=5; $i>0; $i--):?>
                    <div class="ulp-review-rates-row">
                        <div class="ulp-review-rates-row-line">
                            <div class="ulp-review-rates-row-line-inside">
                                <div class="ulp-review-rates-row-line-inside-filled" style= " width: <?php echo esc_attr($data['ratingPercentages'][$i]);?>; "></div>
                            </div>
                        </div>
                        <div class="ulp-review-rates-row-rates"><?php echo ulp_generate_stars($i);?></div>
						<div class="ulp-review-rates-row-percent"><?php echo esc_attr($data['ratingPercentages'][$i]);?></div>
                    </div>
                <?php endfor;?>
            <?php endif;?>
        </div>
    </div>
