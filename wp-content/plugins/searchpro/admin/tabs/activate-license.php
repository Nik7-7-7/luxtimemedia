<?php
if (!defined('ABSPATH'))
    exit;
?>
<div id="activate-license" style="display:none">
    <h2 class="berq-tab-title"><?php esc_html_e('BerqWP License', 'searchpro'); ?></h2>
    <style>

        div#berqwp-license-activation input[type="password"] {
            border-radius: 0;
            padding: 5px 15px;
            width: 30%;
        }

        div#berqwp-license-activation input[type="submit"] {
            border: none;
            background: #1F71FF;
            padding: 11px 20px;
            color: #fff;
            cursor: pointer;
        }
    </style>
    <div id="berqwp-license-activation">
        <div class="berq-info-box">
            <!-- <h3 class="berq-box-title"><?php esc_html_e('BerqWP License Activation', 'searchpro'); ?></h3> -->
            <div class="berq-box-content">
                <?php if (!$this->is_key_verified) { ?>
                
                <input type="password" placeholder="<?php esc_html_e('Enter your license key', 'searchpro'); ?>" name="berqwp_license_key">
                <input type="submit" value="<?php esc_html_e('Activate', 'searchpro'); ?>">
                
                <?php } else { ?>
                    <input type="submit" name="berq_deactivate_key" value="<?php esc_html_e('Deactivate license key', 'searchpro'); ?>" style="background-color:red;">
                    <!-- <a href="" style="color:red;font-size:16px">Deactivate license key</a> -->
                <?php } ?>
            </div>
        </div>

        <?php if ($this->is_key_verified) { ?>
        <div class="berq-info-box">
            <h3 class="berq-box-title"><?php esc_html_e('My Account', 'searchpro'); ?></h3>
            <div class="berq-box-content">
                <?php if ($this->key_response->product_ref !== 'AppSumo Deal') { ?>
                <p>
                    <?php esc_html_e('License:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->product_ref ); ?>
                </p>
                <?php } ?>
                <p><?php esc_html_e('License status:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->status ); ?>
                </p>
                <p><?php esc_html_e('Expiration date:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->date_expiry ); ?>
                </p>
            </div>
        </div>
        <?php } ?>
       
    </div>
</div>