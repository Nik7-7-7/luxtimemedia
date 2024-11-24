<?php

// Add links in plugins list
function flying_pages_add_action_links($links) {
    $plugin_shortcuts = array(
        '<a href="'.admin_url('options-general.php?page=flying-pages').'">Settings</a>',
    );

    
    if (!defined('FLYING_PRESS_VERSION')) {
        $plugin_shortcuts[] =
      '<a href="https://flyingpress.com?ref=flying-pages" target="_blank" style="color:#3db634;">Get FlyingPress</a>';
    }

    return array_merge($links, $plugin_shortcuts);
}