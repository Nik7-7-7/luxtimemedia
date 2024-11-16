<?php if(!defined( 'ABSPATH' ))  exit; // if accessed directly exit ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        .fbs_invite_board_form_wrap {
            max-width: 658px;
            width: 100%;
            margin: 150px auto 50px auto;
            font-family: Sans-serif;
        }
        .fbs_invite_board_form_wrap .fbs_invite_board_invite {
            text-align: center;
            border-radius: 7px 7px 0 0;
            background: radial-gradient(82.33% 119.28% at 50% -19.28%, #6268F1 0%, #000348 100%);
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }
        .fbs_invite_board_form_wrap .fbs_invite_board_invite .bg-shape {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        .fbs_invite_board_form_wrap .fbs_invite_board_invite h3 {
            color: #FFF;
            font-size: 36px;
            font-weight: 600;
            line-height: 46px;
            margin: 0 0 5px;
        }
        .fbs_invite_board_form_wrap .fbs_invite_board_invite p {
            color: #F5F6F7;
            text-overflow: ellipsis;
            font-size: 13px;
            font-weight: 400;
            line-height: 20px;
            margin: 0;
        }
        .fbs_invite_board_form_wrap .fbs_invite_reg_form {
            border-radius: 0 0 8px 8px;
            border: 1px solid #D6DAE1;
            background: #FFF;
            padding: 30px;
        }
        .fbs_invite_board_form_wrap .fbs_invite_reg_form .form-field {
            margin-bottom: 20px;
        }
        .fbs_invite_board_form_wrap .fbs_invite_reg_form .form-field:last-child {
            margin-bottom: 0;
        }
        .fbs_invite_board_form_wrap .fbs_invite_reg_form .form-field label {
            display: block;
            color: #2F3448;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            margin: 0 0 8px;
        }
        .fbs_invite_board_form_wrap .fbs_invite_reg_form .form-field input {
            border-radius: 8px;
            border: 1px solid #D6DAE1;
            background: #FFF;
            width: 100%;
            display: block;
            padding: 11px 12px;
            line-height: 1.2em;
        }
        .fbs_invite_board_form_wrap .fbs_invite_reg_form .form-field input:focus {
            outline: none;
            box-shadow: none;
            border-color: #6268F1;
        }
        .fbs_invite_board_form_wrap .fbs_invite_reg_form .form-field button {
            border-radius: 8px;
            background: #6268F1;
            color: #FFF;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            display: block;
            border: none;
            width: 100%;
            padding: 10px 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="fbs_invite_board_form_wrap">
    <div class="fbs_invite_board_invite">
        <img src="<?php echo FLUENT_BOARDS_PLUGIN_URL . '/assets/images/bg-shape.png' ?>" alt="Shape" class="bg-shape">
        <h3>Fluent Boards</h3>
        <p><?php esc_html_e('WordPress Registration Form', 'fluent-boards'); ?></p>
    </div>
    <form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ) ?>" method="post" class="fbs_invite_reg_form">
        <input type="hidden" name="action" value="myform" />
        <input type="hidden" name="board_id" value="<?php echo esc_attr($boardId); ?>" />

        <div class="form-field">
            <label for="email-input"><?php esc_html_e('Email', 'fluent-boards'); ?></label>
            <input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>" readonly required />
        </div>

        <div class="form-field">
            <label for="firstname"><?php esc_html_e('First Name', 'fluent-boards'); ?></label>
            <input type="text" name="firstname" id="firstname" required />
        </div>

        <div class="form-field">
            <label for="lastname"><?php esc_html_e('Last Name', 'fluent-boards'); ?></label>
            <input type="text" name="lastname" id="lastname" required />
        </div>

        <div class="form-field">
            <label for="password-input"><?php esc_html_e('Password', 'fluent-boards'); ?></label>
            <input type="password" name="password" id="password" required />
        </div>
        <div class="form-field">
            <button type="submit"><?php esc_html_e('Submit', 'fluent-boards'); ?></button>
        </div>
    </form>
</div>
</body>
</html>

