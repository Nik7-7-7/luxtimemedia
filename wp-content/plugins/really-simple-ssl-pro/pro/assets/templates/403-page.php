<?php if ( isset( $captcha ) && true === $captcha ) {
	wp_head();
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>403 Forbidden</title>
	<style type="text/css">
		body {
			font-family: arial, helvetica, sans-serif;
			margin: 2em;
			background-color: #fff;
		}

		.container {
			margin: 1% auto;
			width: 550px;
		}

		.logo {
			margin-bottom: 10px;
		}

		.logo img {
			width: 45%;
			max-width: 32px;
		}

		h1 {
			font-size: 24px;
			font-weight: 525;
			margin: 0;
		}

		.subtitle {
			font-size: 18px;
			margin: 8px 0 8px 0;
			font-weight: normal;
		}

		code {
			margin: 8px 0 0 0;
			font-size: 1em;
			color: rgba(128, 128, 128, 0.59);
		}
        .button {
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
        .button-primary {
            background-color: #0073aa;
        }
	</style>
</head>
<body>

<div class="container">
	<div class="logo">
		<?php

		use RSSSL\Pro\Security\WordPress\Captcha\Rsssl_Captcha;

		$url      = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]";
		$path     = str_replace( $_SERVER['DOCUMENT_ROOT'], '', __DIR__ );
		$icon_url = "$url$path/security.png";
		?>
		<img src="<?php echo $icon_url; ?>" alt="Logo">
	</div>
	<h1><?php echo $apology; ?></h1>
	<h2 class="subtitle"><?php echo $message; ?></h2>
	<code><?php echo $error_code; ?></code>
	<?php
	if ( isset( $captcha ) && true === $captcha ) {
		$model->set_captcha( $ip_address );
		?>
		<p>
		<form method="post" id="rsssl-captcha-form">
			<?php
			Rsssl_Captcha::render( true, 'rsssl-captcha-form' );
            if (!isset($auto_submit) || !$auto_submit) {
                ?>
	            <input type="submit" class="button button-primary" value="Submit"/>
        <?php
            }
			?>
		</form>
		</p>
		<?php

	}
	?>
</div>

<?php
if ( isset( $captcha ) && true === $captcha ) {
	wp_footer();
}
?>
</body>
</html>
