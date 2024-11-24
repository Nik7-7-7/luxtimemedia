<?php
// Register settings menu
function flying_analytics_register_settings_menu() {
    add_options_page('Flying Analytics', 'Flying Analytics', 'manage_options', 'flying-analytics', 'flying_analytics_settings_view');
}
add_action('admin_menu', 'flying_analytics_register_settings_menu');

// Settings page
function flying_analytics_settings_view() {
    include('settings.php');
    include('faq.php');
    include('optimize-more.php');

    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : "settings";
?>

<h2>Flying Analytics</h2>
<h2 class="nav-tab-wrapper">
    <a href="?page=flying-analytics&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
    <a href="?page=flying-analytics&tab=faq" class="nav-tab <?php echo $active_tab == 'faq' ? 'nav-tab-active' : ''; ?>">FAQs</a>
    <a href="?page=flying-analytics&tab=optimize-more" class="nav-tab <?php echo $active_tab == 'optimize-more' ? 'nav-tab-active' : ''; ?>">Optimize More!</a>
</h2>

<?php
    switch ($active_tab) {
        case 'settings':
            flying_analytics_settings();
            break;
        case 'faq':
            flying_analytics_faq();
            break;
        case 'optimize-more':
            flying_analytics_optimize_more();
            break;
        default:
            flying_analytics_settings();
    }
}
?>