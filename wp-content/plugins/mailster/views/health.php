<div class="wrap" id="mailster-health">

	<div class="health_form_wrap step-1">
		<form class="health_form" action="" method="POST">
			<div class="precheck-score">
				<div class="precheck-status-icon"></div>
			</div>
			<h1><?php esc_html_e( 'Email Health Check', 'mailster' ); ?> <?php echo mailster()->beacon( '65a533cfa7319b2f91082484' ); ?></h1>
			<h2 class="howto"><?php esc_html_e( 'The Email Health Check confirms that your current delivery method is set up correctly. It examines the authentication of your server (including SPF, DKIM, and DMARC) to ensure compliance with email delivery best practices.', 'mailster' ); ?></h2>
			<p class="howto"><?php esc_html_e( 'Gmail and Yahoo Mail have introduced new requirements for email senders. Starting February 2024, these requirements will impact senders who distribute more than 5,000 bulk messages daily or have more than 0.3% of their messages reported as spam. Non-compliance with these new guidelines could lead to Gmail and Yahoo rejecting the delivery of messages to their users.', 'mailster' ); ?></p>
			<p class="howto"><strong><?php esc_html_e( 'When you initiate a health check, an email will be sent to our server to process the test and return the result. We do not share any of this data with a third party but we use it to check the content and your deliverability. We keep the right to track anonymously usage data.', 'mailster' ); ?></strong></p>

			<p class="error-msg">&nbsp;</p>
			<div class="precheck-results-wrap">
				<div class="precheck-results">
					
					<div id="precheck-authentication" class="precheck-body">
						<details id="precheck-spf">
							<summary><acronym title="Sender Policy Framework">SPF</acronym><span class="precheck-penality"></span></summary>
							<div class="precheck-result"></div>
						</details>
						<details id="precheck-dkim">
							<summary><acronym title="DomainKeys Identified Mail">DKIM</acronym><span class="precheck-penality"></span></summary>
							<div class="precheck-result"></div>
						</details>
						<details id="precheck-dmarc">
							<summary><acronym title="Domain-based Message Authentication, Reporting & Conformance">DMARC</acronym><span class="precheck-penality"></span></summary>
							<div class="precheck-result"></div>
						</details>
						<details id="precheck-rdns">
							<summary><acronym title="Reverse Domain Name Server lookup">rDNS</acronym><span class="precheck-penality"></span></summary>
							<div class="precheck-result"></div>
						</details>
						<details id="precheck-mx">
							<summary><acronym title="Mail Exchanger Record">MX</acronym><span class="precheck-penality"></span></summary>
							<div class="precheck-result"></div>
						</details>
						<details id="precheck-a">
							<summary><acronym title="Address record">A</acronym><span class="precheck-penality"></span></summary>
							<div class="precheck-result"></div>
						</details>
					</div>
					
					<details id="precheck-blocklist">
						<summary>Blocklist<span class="precheck-penality"></span></summary>
						<div class="precheck-result"></div>
					</details>

				</div>
			</div>
			<input type="submit" class="button button-hero button-primary" value="<?php esc_html_e( 'Start Health Check', 'mailster' ); ?>">
		</form>
		<p><a href="https://mailster.co/go/emailhealthcheck" class="external get-help"><?php esc_html_e( 'Get help from a Codeable Expert', 'mailster' ); ?></a></p>
		
	</div>

</div>
