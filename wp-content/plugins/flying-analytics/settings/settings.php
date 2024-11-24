<?php
function flying_analytics_settings() {
    
    // Validate nonce
    if(isset($_POST['submit']) && !wp_verify_nonce($_POST['flying_analytics_settings_form'], 'flying_analytics')) {
        echo '<div class="notice notice-error"><p>Nonce verification failed</p></div>';
        exit;
    }

    // Update config in database after form submission
    if (isset($_POST['submit'])) {
        update_option('flying_analytics_id', sanitize_text_field($_POST['id']));
        update_option('flying_analytics_method', sanitize_text_field($_POST['method']));
        update_option('flying_analytics_disable_on_login', sanitize_text_field($_POST['disable_on_login']));

        echo '<div class="notice notice-success is-dismissible"><p>Settings have been saved! Please clear cache if you\'re using a cache plugin</p></div>';
    }

    $id = esc_attr(get_option('flying_analytics_id'));
    $method = esc_attr(get_option('flying_analytics_method'));
    $disable_on_login = get_option('flying_analytics_disable_on_login');
?>
<form method="POST">
    <?php wp_nonce_field( 'flying_analytics', 'flying_analytics_settings_form' ); ?>
    <table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row"><label>Google Analytics Tracking ID</label></th>
            <td>
                <input name="id" type="text" value="<?php echo $id; ?>" placeholder="G-XXXXXXXXXX"/>
                <p class="description"><a href="https://support.google.com/analytics/answer/9539598?hl=en" target="_blank">Where to find tracking ID?</a></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label>Tracking script</label></th>
            <td>
                <label>
                    <input type="radio" name="method" value="gtag.js" <?php if ($method == "gtag.js") echo 'checked'; ?>>
                    Gtag.js (90KB)
                </label><br>
                <label>
                    <input type="radio" name="method" value="minimal-analytics" <?php if ($method == "minimal-analytics") echo 'checked'; ?>>
                    Minimal Analytics.js (1.4KB)
                </label>
                <p class="description">Confused? Read the <a href="?page=flying-analytics&tab=faq">FAQ</a></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label>Disable for logged in admins</label></th>
            <td>
                <input name="disable_on_login" type="checkbox" value="1" <?php if($disable_on_login) echo "checked"; ?>>
            </td>
        </tr>
    </tbody>
    </table>

    <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
    </p>
</form>
<?php
}