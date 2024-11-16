<div class="mailster-setup-step-body">

<form class="mailster-setup-step-form">

<p><?php esc_html_e( 'Choose how Mailster should send your campaigns. It\'s recommend to go with a dedicate ESP to prevent rejections and server blocking.', 'mailster' ); ?></p>

<?php $method = mailster_option( 'deliverymethod', 'simple' ); ?>

<?php

$methods = array(

	'simple'     => array(
		'name'    => 'Simple',
		'signup'  => false,
		'desc'    => __( 'Sending via your host is not recommended. Please consider using a dedicate Email Service Provider instead.', 'mailster' ),
		'article' => false,
		'plugin'  => 'mailster-gmail/mailster-gmail.php',
	),
	'smtp'       => array(
		'name'    => 'SMTP',
		'signup'  => false,
		'desc'    => __( 'Send your campaigns via SMTP. You need to provide a SMTP Host, Username and Password.', 'mailster' ),
		'article' => false,
		'plugin'  => 'mailster-gmail/mailster-gmail.php',
	),
	'amazonses'  => array(
		'name'    => 'AmazonSES',
		'signup'  => 'https://portal.aws.amazon.com/billing/signup#/start/email',
		'desc'    => __( 'Send your campaigns via AmazonSES. You need to create an IAM User with the right permissions.', 'mailster' ),
		'article' => '611bb67ab37d837a3d0e4790',
		'plugin'  => 'mailster-amazonses/mailster-amazonses.php',
	),
	'sparkpost'  => array(
		'name'    => 'SparkPost',
		'signup'  => 'https://sparkpost.com/pricing/',
		'desc'    => __( 'Send your campaigns via SparkPost. You need to create an API Key in your SparkPost Account.', 'mailster' ),
		'article' => '611bb258b37d837a3d0e475a',
		'plugin'  => 'mailster-sparkpost/mailster-sparkpost.php',
	),
	'mailgun'    => array(
		'name'    => 'Mailgun',
		'signup'  => 'https://get.sinchemail.com/mailster-mailgun',
		'desc'    => __( 'Send your campaigns via Mailgun. You need to create an API Key in your Mailgun Account.', 'mailster' ),
		'article' => '611bb21db55c2b04bf6df0ca',
		'plugin'  => 'mailster-mailgun/mailster-mailgun.php',
	),
	'sendgrid'   => array(
		'name'    => 'SendGrid',
		'signup'  => 'https://sendgrid.com/partner/mailster',
		'desc'    => __( 'Send your campaigns via SendGrid. You need to create an API Key in your SendGrid Account.', 'mailster' ),
		'article' => '611bb078b55c2b04bf6df0b4',
		'plugin'  => 'mailster-sendgrid/mailster-sendgrid.php',
	),
	'mailersend' => array(
		'name'    => 'MailerSend',
		'signup'  => 'https://refer.mailersend.com/mailster',
		'desc'    => __( 'Send your campaigns via MailerSend. You need to create an API Key in your MailerSend Account.', 'mailster' ),
		'article' => false,
		'plugin'  => 'mailster-mailersend/mailster-mailersend.php',
	),
	'gmail'      => array(
		'name'    => 'Gmail',
		'signup'  => 'https://www.google.com/intl/en/gmail/about/',
		'desc'    => __( 'Send your campaigns via Gmail. You need to create an App Password for Mailster in your Google Account.', 'mailster' ),
		'article' => '611bae3eb37d837a3d0e472d',
		'plugin'  => 'mailster-gmail/mailster-gmail.php',
	),
	'mailjet'    => array(
		'name'    => 'MailJet',
		'signup'  => 'https://get.sinchemail.com/mailster-mailjet',
		'desc'    => __( 'Send your campaigns via MailJet. You need to create an API Key in your MailJet Account.', 'mailster' ),
		'article' => '654df29e4e8b0f49d3d23b7f',
		'plugin'  => 'mailster-mailjet/mailster-mailjet.php',
	),

);

if ( isset( $methods[ $method ] ) ) {
	$current = $methods[ $method ];
	unset( $methods[ $method ] );
	$methods = array( $method => $current ) + $methods;
}

?>

<div class="deliverymethods">
<?php foreach ( $methods as $key => $data ) : ?>	
	<section class="<?php echo ( $method === $key ) ? 'current' : ''; ?>"  data-plugin="<?php echo esc_attr( $data['plugin'] ); ?>" data-name="<?php echo esc_attr( $data['name'] ); ?>" data-method="<?php echo esc_attr( $key ); ?>">
		<img src="https://static.mailster.co/images/wizard/<?php echo esc_attr( $key ); ?>.png" width="128" height="128" loading="lazy">
		<h3><?php echo esc_html( $data['name'] ); ?></h3>
		<p><?php echo esc_html( $data['desc'] ); ?></p>
		<div class="deliverytab" id="deliverytab-<?php echo esc_attr( $key ); ?>">
			<?php ( $method === $key ) ? do_action( 'mailster_deliverymethod_tab_' . $key ) : false; ?>
		</div>
		<p class="alignright">			
			<a class="button button-secondary send-test" title="<?php printf( esc_attr__( 'Send a test message to your current from address via "%s"', 'mailster' ), esc_html( $data['name'] ) ); ?>"><?php esc_html_e( 'Send Test', 'mailster' ); ?></a>
			<a class="button button-primary save-delivery"><?php esc_html_e( 'Save Delivery Settings', 'mailster' ); ?></a>
		</p>
		<p>
		<a class="button quick-install"><?php printf( esc_html__( 'Use %s', 'mailster' ), esc_html( $data['name'] ) ); ?></a>
		<span class="deliverystatus"></span>
		<?php if ( $data['article'] ) : ?>
			<?php esc_html_e( 'or', 'mailster' ); ?> <a class="button button-link" href="<?php echo mailster_url( 'https://kb.mailster.co/' . $data['article'] ); ?>" data-article="<?php echo esc_attr( $data['article'] ); ?>"><?php esc_html_e( 'Get Help', 'mailster' ); ?></a>
		<?php endif; ?>
		</p>
		<div><?php if ( $data['signup'] ) : ?>
			<a class="button button-link external" href="<?php echo mailster_url( 'https://mailster.co/go/' . $key ); ?>"><?php printf( esc_html__( 'Signup for an %s account', 'mailster' ), $data['name'] ); ?></a>
		<?php endif; ?></div>
	</section>
<?php endforeach; ?>
</div>
<input type="hidden" name="mailster_options[deliverymethod]" id="deliverymethod" value="<?php echo esc_attr( $method ); ?>" class="regular-text">

</form>

</div>
