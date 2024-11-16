<?php
if (!defined('ABSPATH'))
  exit;

$uri = $_SERVER['REQUEST_URI'];
if (get_option('berqwp_enable_sandbox') == 1 && isset($_GET['berqwp'])) {
  $uri = explode('?berqwp', $_SERVER['REQUEST_URI'])[0];
}

if (isset($_GET['creating_cache'])) {
  $uri = explode('?creating_cache', $_SERVER['REQUEST_URI'])[0];
}

$optifer_critical_css = optifer_cache . '/critical-css/' . '/desktop_' . md5($uri) . '.css';

if (file_exists($optifer_critical_css)) {
  $criticalCSS = file_get_contents($optifer_critical_css);
  $no_fonts_criticalCSS = preg_replace('/@font-face\s*{[^}]+}/i', '', $criticalCSS);

  if (get_option('berqwp_enable_preload_mostly_used_font') == 1) {
    $mostly_used_font_face = berqwp_extract_most_used_font_face($criticalCSS);
    $criticalCSS = $mostly_used_font_face . $no_fonts_criticalCSS;
  } else {
    $criticalCSS = $no_fonts_criticalCSS;
  }

  echo '<style data-berqwp id="berqwp-critical-css">' . $criticalCSS . '</style>';
}
?>

<script data-berqwp>
  var preloadLinks = document.querySelectorAll('link[rel="preload"]');
  preloadLinks.forEach(function (link) {
    // Only remove the link if it doesn't have the "data-berqwp" attribute
    if (!link.hasAttribute('data-berqwp')) {
      link.remove();
    }
  });
</script>