<!DOCTYPE html>
<html lang='en'>
<head>
    <title>Bookings - <?php echo esc_attr(get_bloginfo('name')); ?></title>
    <meta charset='utf-8'>

    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0,viewport-fit=cover"/>
    <meta content='yes' name='apple-mobile-web-app-capable'>
    <meta name="description" content="<?php echo esc_attr(get_bloginfo('description')); ?>">
    <meta name="robots" content="noindex">

    <link rel="icon" type="image/x-icon" href="<?php echo get_site_icon_url(); ?>"/>

    <?php wp_head(); ?>

    <style>
        html, body {
            margin-top: 0 !important;
        }

        .fbs_full_screen_btn {
            display: none !important;
        }

        body {
            background: #f0f0f1;
            color: #3c434a;
            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
            font-size: 13px;
            line-height: 1.4em;
        }

        body * {
            box-sizing: border-box;
            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
        }

        .fbs_front {
            display: block;
            width: 100%;
        }

        .fbs_front .fbs_regular_view {
            max-width: 1120px;
            margin: 0 auto !important;
        }

        .fframe_main-menu-items {
            margin-left: 0;
        }

        footer#footer {
            display: block;
            text-align: center;
            margin: 0;
        }

        .fbs_front .fframe_body {
            margin-left: 0 !important;
        }

        ul.fbs_board_setting_list_container {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .fbs_front .fluentboards_databox {
            height: calc(100vh - 60px);
        }

        body.fluentboards_page_fluent_booking #wpbody #fbs_tasks_wrapper, .toplevel_page_fluent_booking #wpbody #fbs_tasks_wrapper {
            height: calc(100vh - 145px);
        }

        .fbs_right_sidebar .fbs_notification_header, .fbs_right_sidebar .fbs_right_sidebar_header, .fbs_filters_sidebar .fbs_filters_hearder, .fbs-add_new_board .head {
            padding: 0 0 24px !important;
            margin: 0 !important;
        }

        .fl_app {
            margin: 0 20px;
        }
    </style>

    <?php if (!get_current_user_id()): ?>
        <style>
            .fbs_login_form {
                margin: 100px auto;
                max-width: 500px;
                background: white;
                border-radius: 5px;
            }
            .fbs_login_form {
                .fbs_login_form_heading {
                    padding: 20px;
                    background: #fbfbfb;
                    border-bottom: 1px solid #e5e7eb;
                    font-size: 20px;
                    font-weight: 500;
                    border-top-left-radius: 5px;
                    border-top-right-radius: 5px;
                    text-align: center;
                }
            }

            .fbs_login_wrap {
                padding: 20px;
                border-bottom-left-radius: 5px;
                border-bottom-right-radius: 5px;
            }
            #loginform p {
                margin-bottom: 10px;
                display: block;
            }
            #loginform * {
                box-sizing: border-box;
            }
            #loginform p > label {
                font-size: 14px;
                font-weight: 500;
                display: block;
            }
            input.input {
                width: 100%;
                padding: 10px;
                border: 1px solid #e5e7eb;
                border-radius: 5px;
                margin-top: 5px;
            }
            .button-primary {
                background: #3b82f6;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                margin-top: 10px;
            }
        </style>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                // get the current full url
                var currentUrl = window.location.href;

                // set to input[name=redirect_to] value as current url
                document.querySelector('input[name=redirect_to]').value = currentUrl;
            });
        </script>
    <?php endif; ?>

    <?php do_action('fluent_booking/front_head'); ?>

</head>
<body class="fluentboards_page_fluent_booking">

<div id="wpbody" class="fluent_booking_front">
    <?php echo $content; ?>
</div>

<?php wp_footer(); ?>

<?php do_action('fluent_booking/front_footer'); ?>

<?php if(is_user_logged_in()): ?>

<?php $user = get_user_by('ID', get_current_user_id()); ?>

<div style="margin-top: 50px; text-align: center;" class="fbs_footer">
    <p>You are logged in as <?php echo $user->display_name; ?>. <a href="<?php echo admin_url(); ?>">Go to Admin Panel</a> | <a href="<?php echo wp_logout_url(site_url()); ?>">Logout</a> </p>
</div>

<?php endif; ?>
</body>
</html>
