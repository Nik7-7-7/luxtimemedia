<!DOCTYPE html>
<?php
$ssa = ssa();
$ssa_settings = $ssa->settings->get();
$ssa_settings = $ssa->settings->remove_unauthorized_settings_for_current_user( $ssa_settings, true, true );
$ssa_appointment_types = $ssa->appointment_type_model->query( array (
    'status' => 'publish', 
    'fetch' => array(
      'has_sms' => true,
    ),
  )
);

// Clean instruction fields
foreach ($ssa_appointment_types as $appointment_type_key => $appointment_type) {
  if ( empty( $appointment_type['custom_customer_information'] ) ) {
    continue;
  }

  if ( ! is_array( $appointment_type['custom_customer_information'] ) ) {
    continue;
  }

  foreach ($appointment_type['custom_customer_information'] as $field_key => $field) {
    if ( empty( $ssa_appointment_types[$appointment_type_key]['custom_customer_information'][$field_key]['instructions'] ) ) {
      continue;
    }

    $ssa_appointment_types[$appointment_type_key]['custom_customer_information'][$field_key]['instructions'] = strip_tags( $ssa_appointment_types[$appointment_type_key]['custom_customer_information'][$field_key]['instructions'], '<a><strong><em>' );
  }
}

// Override availability window
foreach ($ssa_appointment_types as $appointment_type_key => $appointment_type) {
  if ( ! empty( $_GET['availability_start_date'] ) ) {
    $ssa_appointment_types[$appointment_type_key]['availability_start_date'] = esc_attr( $_GET['availability_start_date'] );
  }
  if ( ! empty( $_GET['availability_end_date'] ) ) {
    $ssa_appointment_types[$appointment_type_key]['availability_end_date'] = esc_attr( $_GET['availability_end_date'] );
  }

  if ( ! empty( $_GET['suggest_first_available_within_minutes'] ) && $ssa->settings_installed->is_installed( 'booking_flows' ) ) {
    $ssa_appointment_types[$appointment_type_key]['booking_flow_settings']['suggest_first_available_within_minutes'] = (int) esc_attr( $_GET['suggest_first_available_within_minutes'] );
  }

  if ( ! empty( $_GET['flow'] ) && $ssa->settings_installed->is_installed( 'booking_flows' ) ) {
    $ssa_appointment_types[$appointment_type_key]['booking_flow_settings']['booking_flow'] = esc_attr( $_GET['flow'] );
    if ($_GET['flow'] != 'first_available' ) {
      $ssa_appointment_types[$appointment_type_key]['booking_flow_settings']['suggest_first_available'] = false;
    }else{
      $ssa_appointment_types[$appointment_type_key]['booking_flow_settings']['suggest_first_available'] = true;
    }
  }

  if ( ! empty( $_GET['fallback_flow'] ) && $ssa->settings_installed->is_installed( 'booking_flows' )  ) {
    $ssa_appointment_types[$appointment_type_key]['booking_flow_settings']['fallback_booking_flow'] = esc_attr( $_GET['fallback_flow'] );
  }

  if ( ! empty( $_GET['date_view'] ) ) {
    $date_view = esc_attr( $_GET['date_view']);
    if( $ssa->settings_installed->is_installed( 'booking_flows' ) ) {
      $ssa_appointment_types[$appointment_type_key]['booking_layout'] = $date_view;
    } elseif ( $date_view === "month" || $date_view === "week" ){
        $ssa_appointment_types[$appointment_type_key]['booking_layout'] = $date_view;
    }
  }
  
  if ( ! empty( $_GET['time_view'] ) && $ssa->settings_installed->is_installed( 'booking_flows' )  ) {
    $ssa_appointment_types[$appointment_type_key]['booking_flow_settings']['time_view'] = esc_attr( $_GET['time_view'] );
  }
}

// Check for $_GET['label'] if set, and convert it to $_GET['types']
if( ! empty( $_GET['label'] ) ) {
    $label = sanitize_text_field( esc_attr( $_GET['label'] ) );
    $ids = $ssa->shortcodes->convert_label_to_appt_types_ids( $label );
    if ( empty( $ids ) ) {
        $error_message = '<h3>' . __('Sorry, no appointment types available for this label, please check back later.', 'simply-schedule-appointments') . '</h3>';
        if( current_user_can( 'ssa_manage_site_settings' ) ) {
        $error_message .= '<code>' . sprintf( __('The specified appointment type label \'%1$s\' can\'t be found, or has no appointment types available %2$s (this message only viewable to site administrators)', 'simply-schedule-appointments'),
                          $label,
                          '</code>' );
        }
        return $error_message;
    } else {
      $_GET['types'] = $ids;
  
    }
}

if ( ! empty( $_GET['types'] ) ) {
  $params_types = esc_attr( $_GET['types'] );
} else if ( ! empty( $params['types'] ) ) {
  $params_types = $params['types'];
}

if ( ! empty( $params_types ) ) {
  $restricted_types = explode( ',', $params_types );
  $ssa_appointment_types = array_filter( $ssa_appointment_types, function( $appointment_type ) use ( $restricted_types ) {
    if ( empty( $appointment_type['id'] ) || empty( $appointment_type['slug'] ) ) {
      return false;
    }

    if ( in_array( $appointment_type['id'], $restricted_types ) ) {
      return true;
    }

    if ( in_array( $appointment_type['slug'], $restricted_types ) ) {
      return true;
    }

    return false;
  });
  $ssa_appointment_types = array_values( $ssa_appointment_types );
}

$ssa_appointment_types = apply_filters( 'ssa_booking_appointment_types', $ssa_appointment_types );


// Setup booking URL parameters for global variable
$ssa_booking_url_settings = array(
  'booking_url'     => null,
  'booking_post_id' => null,
  'booking_title'   => null,
);

if( isset( $_GET['booking_url'] ) ) {
  $ssa_booking_url_settings['booking_url'] = esc_attr( $_GET['booking_url'] );
}
if( isset( $_GET['booking_post_id'] ) ) {
  $ssa_booking_url_settings['booking_post_id'] = esc_attr( $_GET['booking_post_id'] );
}
if( isset( $_GET['booking_title'] ) ) {
  $ssa_booking_url_settings['booking_title'] = html_entity_decode( urldecode( esc_attr( $_GET['booking_title'] ) ) );
}


function ssa_get_language_attributes( $doctype = 'html' ) {
  $attributes = array();

  $is_rtl = SSA_Translation::is_rtl();
  $lang = SSA_Translation::get_locale();
  $lang = str_replace( '_', '-', $lang );

  if ( $is_rtl ) {
    $attributes[] = 'dir="rtl"';
  }

  $attributes[] = 'lang="' . esc_attr( $lang ) . '"';

  $output = implode( ' ', $attributes );

  return $output;
}
?>
<html <?php echo ssa_get_language_attributes(); ?>>
  <head>
    <meta charset="utf-8">
    <title><?php the_title(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow" />
    <link rel='stylesheet' id='ssa-unsupported-style'  href='<?php echo $ssa->url( 'assets/css/unsupported.css?ver='.$ssa::VERSION ); ?>' type='text/css' media='all' />
    <link rel='stylesheet' id='ssa-booking-material-icons-css'  href='<?php echo $ssa->url( 'assets/css/material-icons.css?ver='.$ssa::VERSION ); ?>' type='text/css' media='all' />
    <link rel='stylesheet' id='ssa-booking-roboto-font-css'  href='<?php echo $ssa->url( 'assets/css/roboto-font.css?ver='.$ssa::VERSION ); ?>' type='text/css' media='all' />
    <link rel='stylesheet' id='ssa-booking-style-css'  href='<?php echo $ssa->url( 'booking-app-new/dist/static/css/app.css?ver='.$ssa::VERSION ); ?>' type='text/css' media='all' />
    <link rel="stylesheet" href='<?php echo $ssa->url( 'assets/css/iframe-inner.css?ver='.$ssa::VERSION ); ?>'>
    <link rel='https://api.w.org/' href='<?php echo home_url( 'wp-json/' ); ?>' />
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php echo home_url( 'xmlrpc.php?rsd' ); ?>" />
    <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="<?php echo home_url( 'wp-includes/wlwmanifest.xml' ); ?>" />
    <link rel="alternate" type="application/json+oembed" href="<?php echo home_url( 'wp-json/oembed/1.0/embed?url=http%3A%2F%2Fssa.dev%2Fbooking-test%2F' ); ?>" />
    <link rel="alternate" type="text/xml+oembed" href="<?php echo home_url( 'wp-json/oembed/1.0/embed?url=http%3A%2F%2Fssa.dev%2Fbooking-test%2F&#038;format=xml' ); ?>" />

    <?php $booking_css_url = $ssa->templates->locate_template_url( 'booking-app/custom.css' ); ?>
    <?php /* Apply styles from settings to view */ ?>
    <?php
      $ssa_styles = $ssa->styles_settings->get();

      // if we have style settings on the GET parameters, merge them with the styles settings
      $styles_params = array();

      if( isset( $_GET['accent_color'] ) && ! empty( $_GET['accent_color'] ) ) {
        $accent_color = $ssa->styles->hex_to_rgba( '#'. $_GET['accent_color'] );
        if( $accent_color ) {
          $styles_params['accent_color'] = $accent_color;
        }
      }

      if( isset( $_GET['background'] ) && ! empty( $_GET['background'] ) ) {
        $background = $ssa->styles->hex_to_rgba( '#'. $_GET['background'] );
        if( $background ) {
          $styles_params['background'] = $background;
        }
      }

      if( isset( $_GET['font'] ) && ! empty( $_GET['font'] ) ) {
        $styles_params['font'] = esc_attr( $_GET['font'] );
      }

      if( isset( $_GET['padding'] ) && ! empty( $_GET['padding'] ) ) {
        $styles_params['padding'] = esc_attr( $_GET['padding'] );
      }

      $ssa_styles = wp_parse_args( $styles_params, $ssa_styles );

      /* Use luminosity contrast of iframe background color to determine if the headings text color should be black or white */
      $iframe_bg_contrast_ratio = $ssa->styles->get_contrast_ratio( $ssa_styles['background'] );
      $iframe_bg_transparency = $ssa->styles->get_transparency( $ssa_styles['background'] );

      /* Use luminosity contrast of accent color to determine if the accent color - book-day & time-select elements - should have black or white text */
      $contrast_ratio = $ssa->styles->get_contrast_ratio( $ssa_styles['accent_color'] );

      // Set accent contrast based on luminosity of the color
      if ($contrast_ratio > 6 ) {
        $ssa_styles['accent_contrast'] = 'black';
      } else {
        $ssa_styles['accent_contrast'] = 'white';
      }

      $is_dark = $ssa->styles->is_dark_background( $ssa_styles['background'] );

      // Separate padding value into integer and units
      $padding_atts = $ssa->styles->get_style_atts_from_string( $ssa_styles['padding'] );

      // Attach Google stylesheet if necessary
      $system_fonts = array(
        'Arial' => 'Arial, Helvetica Neue, Helvetica, sans-serif',
        'Arial Black' => 'Arial Black, Arial Bold, Gadget, sans-serif',
        'Courier New' => 'Courier New, Courier, Lucida Sans Typewriter, Lucida Typewriter, monospace',
        'Georgia' => 'Georgia, Times, Times New Roman, serif',
        'Helvetica' => 'Helvetica Neue, Helvetica, Arial, sans-serif',
        'Tahoma' => 'Tahoma, Verdana, Segoe, sans-serif',
        'Times New Roman' => 'TimesNewRoman, Times New Roman, Times, Baskerville, Georgia,serif',
        'Trebuchet MS' => 'Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif',
        'Verdana' => 'Verdana, Geneva, sans-serif',
        'Roboto' => 'Roboto'
      );

      $is_system_font = in_array(trim($ssa_styles['font']), $system_fonts);

      if ( !$is_system_font ) : ?>
        <link rel='dns-prefetch' href='//fonts.googleapis.com' />
        <link href="https://fonts.googleapis.com/css?family=<?php echo $ssa_styles['font']; ?>" rel="stylesheet">
      <?php endif; ?>
    <style>
      :root {
        /* Colors */
        --mdc-theme-primary: <?php echo $ssa_styles['accent_color']; ?>;
        --mdc-theme-on-primary: <?php echo $ssa_styles['accent_contrast']; ?>;
        --mdc-theme-secondary: <?php echo $ssa_styles['accent_color']; ?>;
        --mdc-theme-on-secondary: <?php echo $ssa_styles['accent_contrast']; ?>;
        --mdc-ripple-color: <?php echo $ssa_styles['accent_color']; ?>;
        /* Typography */
        --mdc-typography-font-family: <?php echo $ssa_styles['font']; ?>;
        --mdc-typography-headline6-font-family: <?php echo $ssa_styles['font']; ?>;
      }

      /* Background color */
      html body,
      html body.md-theme-default {
        background: <?php echo $ssa_styles['background']; ?>;
        padding: <?php echo $padding_atts['value'] . $padding_atts['unit']; ?>
      }

      html .mdc-card {
        color: black;
      }

      /* Accent color and accent contrast */
      html .md-theme-default.md-button:not([disabled]).md-primary.md-icon-button:not(.md-raised),
      html .select2-results__options .select2-results__option[aria-selected=true],
      html .md-theme-default.md-button:not([disabled]).md-primary:not(.md-icon-button),
      html .md-theme-default.md-input-container.md-input-focused label,
      html .md-theme-default.md-input-container.md-input-focused .md-icon:not(.md-icon-delete),
      html .md-theme-default.time-select.md-button:not([disabled]).md-raised:not(.md-icon-button),
      html .appointment-actions .md-button,
      html .md-theme-default.md-checkbox.md-primary .md-ink-ripple,
      html .md-theme-default.md-radio.md-primary .md-ink-ripple,
      html .md-theme-default.md-radio.md-primary.md-checked .md-ink-ripple,
      html .mdc-theme-name--default.mdc-icon-button:not(:disabled) {
        color: <?php echo $ssa_styles['accent_color']; ?>;
      }
      html legend.md-subheading .md-icon.md-theme-default {
        color: rgba(0,0,0,0.54);
      }
      html .md-card.selectable.light-green:hover,
      html .md-card.selectable.light-green:focus,
      html .md-card.selectable.mdc-theme-name--default:hover,
      html .md-card.selectable.mdc-theme-name--default:focus,
      html .md-theme-default.md-button:not([disabled]).md-primary.md-raised,
      html .md-theme-default.md-button:not([disabled]).md-primary.md-fab,
      html .md-theme-default.md-button:not([disabled]).md-primary.md-raised:hover,
      html .md-theme-default.md-button:not([disabled]).md-primary.md-raised:focus,
      html .md-theme-default.md-button:not([disabled]).md-primary.md-fab:hover,
      html .md-theme-default.md-button:not([disabled]).md-primary.md-fab:focus,
      html .book-day button.md-whiteframe.selectable:focus,
      html .book-day button.md-whiteframe.selectable:hover,
      html .book-day button.md-whiteframe.selectable:focus,
      html .md-theme-default.md-input-container.md-input-focused:after,
      html .md-theme-default.time-select.md-button:not([disabled]).md-raised:not(.md-icon-button):hover,
      html .md-theme-default.time-select.md-button:not([disabled]).md-raised:not(.md-icon-button):focus {
        background-color: <?php echo $ssa_styles['accent_color']; ?>;
        color: <?php echo $ssa_styles['accent_contrast']; ?>;
      }
      html .md-card.selectable.light-green,
      html .md-card.selectable.mdc-theme-name--default {
        border-left-color: <?php echo $ssa_styles['accent_color']; ?>;
      }
      html .select2-search--dropdown .select2-search__field:focus {
        border-bottom-color: <?php echo $ssa_styles['accent_color']; ?>;
      }
      html .md-theme-default.md-spinner .md-spinner-path {
        stroke: <?php echo $ssa_styles['accent_color']; ?>;
      }

      /* Checkboxes and Radios */
      html .md-theme-default.md-checkbox.md-primary.md-checked .md-checkbox-container {
        background-color: <?php echo $ssa_styles['accent_color']; ?>;
        border-color: <?php echo $ssa_styles['accent_color']; ?>;
      }
      html .md-theme-default.md-checkbox.md-primary.md-checked .md-checkbox-container:after {
        border-color: <?php echo $ssa_styles['accent_contrast']; ?>;
      }
      html .md-theme-default.md-radio.md-primary .md-radio-container:after {
        background-color: <?php echo $ssa_styles['accent_color']; ?>;
      }
      html .md-theme-default.md-radio.md-primary.md-checked .md-radio-container {
        border-color: <?php echo $ssa_styles['accent_color']; ?>;
      }

      /* New booking app initial loading spinner */
      html .ssa_booking_initial_spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 600px;
        margin: 0;
      }

      html .ssa_booking_initial_spinner-container .ssa_booking_initial_spinner {
        border: 4px solid var(--mdc-theme-on-primary);
        border-top-color: var(--mdc-theme-primary);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: ssa_spin .8s cubic-bezier(1, 0.73, 0.39, 0.65)  infinite;
      }

      @keyframes ssa_spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      /* End new booking app initial loading spinner */


      /* Contrast Mode */
      /* show text in white if: background has enough contrast, or admin is using a somewhat transparent background color and has turned on contrast mode */
      <?php if (( $iframe_bg_contrast_ratio < 10 && $iframe_bg_transparency >= 0.8 ) || ( $ssa_styles['contrast'] && $iframe_bg_transparency < 0.8 ) ) : ?>
        html body,
        html body.md-theme-default {
          --mdc-theme-text-primary-on-background: white;
          --mdc-theme-text-secondary-on-light: rgba(255,255,255,0.54);
          color: white;
        }
        html .time-listing-icon {
          fill: white;
        }
      <?php endif; ?>

      /* Font family */
      html body,
      html body.md-theme-default,
      html .book-day button.md-whiteframe.selectable,
      html .book-day button.md-whiteframe.disabled,
      html .book-day button.md-whiteframe.selectable,
      html .book-day button.md-whiteframe.disabled {
        font-family: <?php echo $ssa_styles['font']; ?>;
      }

      <?php if ($is_dark) : ?>
        html .book-day button.md-whiteframe.disabled  {
          color: #bdbdbd;  /* foxy-color('grey', 400)*/
          background-color: #616161; /* foxy-color('grey', 700)*/
        }
        html .book-day button.md-whiteframe.passive  {
          background-color: transparent;
        }
      <?php endif; ?>
    </style>

    <style>
      <?php echo strip_tags( $ssa_styles['css'] ); ?>
    </style>

    <link rel='stylesheet' id='ssa-booking-custom-css'  href='<?php echo $booking_css_url; ?>' type='text/css' media='all' />
    <?php

    // BEGIN: Deprecated
    if ( wp_style_is( 'ssa-custom' ) ){
      $wp_styles = wp_styles();
      foreach ($wp_styles->queue as $handle_key => $handle) {
        if ( $handle === 'ssa-custom' ) {
          continue;
        }

        wp_dequeue_style( $handle );
      }

      wp_print_styles();
    }
    // END: Deprecated
    ?>
    <?php do_action( 'ssa_booking_head' ); ?>
  </head>
  <body <?php body_class(); ?>>
    <?php echo '<div id="ssa-booking-app">
        <div class="ssa_booking_initial_spinner-container">
          <div class="ssa_booking_initial_spinner"></div>
        </div>
        <noscript>
          <div class="unsupported">
            <div class="unsupported-container">
              <h1 class="unsupported-label">' . __('Simply Schedule Appointments requires JavaScript', 'simply-schedule-appointments') . '</h1>
              <p class="unsupported-description">' . __('To book an appointment, please make sure you enable JavaScript in your browser.', 'simply-schedule-appointments') . '</p>
            </div>
          </div>
        </noscript>
      </div>
      <div id="ssa-unsupported" style="display:none;">
          <div class="unsupported">
            <div class="unsupported-container">
              <h1 class="unsupported-label">' . __('Unsupported Browser', 'simply-schedule-appointments') . '</h1>
              <p class="unsupported-description">' . __('To book an appointment, please update your browser to something more modern. We recommend Firefox or Chrome.', 'simply-schedule-appointments') . '</p>
            </div>
          </div>
      </div>'; ?>
  <script type="text/javascript">
    var ssa = <?php echo json_encode( $ssa->bootstrap->get_api_vars() ); ?>;
    var ssa_settings = <?php echo json_encode( $ssa_settings ); ?>;
    var ssa_appointment_types = <?php echo json_encode( $ssa_appointment_types ); ?>;
    var ssa_translations = <?php echo json_encode( $ssa->shortcodes->get_translations() ); ?>;
    var ssa_customer_information_defaults = <?php echo json_encode( $ssa->customer_information->get_defaults() ); ?>;
    var ssa_booking_url_settings = <?php echo json_encode( $ssa_booking_url_settings ) ?>;
    var ssa_token = '<?php echo isset( $_GET['token'] ) ? esc_attr( $_GET['token'] ) : '' ?>'
    var ssa_embed_settings = <?php echo json_encode(array("redirect_post_id" => isset($_GET['redirect_post_id'])? esc_attr($_GET['redirect_post_id']): '', "redirect_url" => isset($_GET['redirect_post_id'])? get_permalink(esc_attr($_GET['redirect_post_id'])): '')); ?>;
    var ssa_availability_edge_cache_timestamp = <?php echo json_encode(date("Y-m-d H:i:s")) ?>;
    var ssa_availability_edge_cache = <?php echo json_encode( ssa_cache_get( 'booking_app_availability_edge_cache' ) ); ?>;
    var ssa_availability_query_args = <?php echo json_encode( SSA_Availability_Query::get_default_args() ); ?>;
  </script>

  <?php if ( $ssa->settings_installed->is_activated( 'stripe' ) ): ?>
    <script src="https://js.stripe.com/v3/"></script>
  <?php endif ?>

  <script type='text/javascript' src='<?php echo $ssa->url( 'assets/js/unsupported-min.js?ver='.$ssa::VERSION ); ?>'></script>
  <script type='text/javascript' src='<?php echo $ssa->url( 'booking-app-new/dist/static/js/manifest.js?ver='.$ssa::VERSION ); ?>'></script>
  <script type='text/javascript' src='<?php echo $ssa->url( 'booking-app-new/dist/static/js/chunk-vendors.js?ver='.$ssa::VERSION ); ?>'></script>
  <script type='text/javascript' src='<?php echo $ssa->url( 'booking-app-new/dist/static/js/app.js?ver='.$ssa::VERSION ); ?>'></script>
  <script type='text/javascript' data-cfasync='false' src='<?php echo $ssa->url( 'assets/js/iframe-inner.js?ver='.$ssa::VERSION ); ?>'></script>
  <?php do_action( 'ssa_booking_footer' ); ?>
  </body>
</html>
