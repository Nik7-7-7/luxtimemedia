<div>
    <?php if (empty($data ['error'])):?>
        <?php if (empty($data ['charge_made'])):?>
            <div class="ulp-danger-message"><?php echo Ulp_Global_Settings::get('ulp_messages_stripe_not_completed');?></div>
        <?php else:?>
            <div class="ulp-success-message"><?php echo Ulp_Global_Settings::get('ulp_messages_stripe_completed');?></div>
        <?php endif;?>
    <?php else:?>
        <div class="ulp-danger-message"><?php echo esc_html($data ['error']);?></div>
    <?php endif;?>
</div>
