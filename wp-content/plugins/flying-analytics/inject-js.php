<?php

function flying_analytics_inject_js() {

  $id = esc_html(get_option('flying_analytics_id'));
  $method = esc_html(get_option('flying_analytics_method'));
  $disable_on_login = get_option('flying_analytics_disable_on_login');

  if ($disable_on_login && current_user_can('manage_options')) return;

  if ($method =="minimal-analytics") {
    $local_js = plugins_url( 'js/minimal-analytics.js', __FILE__ );
    echo "<script>window.GA_ID='{$id}'</script>";
    echo "<script src='{$local_js}' defer></script>"; 
  }

  else {
    $local_js = plugins_url( 'js/gtag.js', __FILE__ );
    echo "<script>window.GA_ID='{$id}'</script>";
    echo "<script src='{$local_js}' defer></script>"; 
    echo "<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', '{$id}');</script>";
  }
  
}

add_action( 'wp_print_footer_scripts', 'flying_analytics_inject_js');
