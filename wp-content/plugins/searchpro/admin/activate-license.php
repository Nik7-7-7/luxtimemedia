<?php
if (!defined('ABSPATH')) exit;

?>
<style>
    div#berqwp-license-activation {
        background: #fff;
        padding: 20px;
    }

    div#berqwp-license-activation h1 {
        margin-bottom: 15px;
    }

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
<div class="wrap">
    <div id="berqwp-license-activation">
        <h1>BerqWP License Activation</h1>
        <?php
        if (!empty($error)) {
            echo wp_kses_post('<p style="color:red">'.$error.'</p>');
        }
        ?>
        <form action="" method="post">
            <input type="password" placeholder="Enter your license key" name="berqwp_license_key">
            <input type="submit" value="Activate">
        </form>
    </div>
</div>