<section id="show-secret-key">
	<div class="show-secret-key-container">
		<h4 class="text-left">
			<span>Installation Key</span>
		</h4>
		<div id="secret-key-content">
			<div>
				Use this key on your AifLift dashboard to get started.
			</div><br/>
			<div style="display: flex; gap: 8px; align-items: center;">
				<input type="password" id="secret-key" name="secret_key" value="<?php echo esc_attr( $this->bvinfo->getDefaultSecret() ); ?>" class="widefat" style="flex: 1;" readonly>
				<button type="button" id="view-key" class="button">View Key</button>
				<button type="button" id="copy-key" class="button" onclick="copyToClipboard()">Copy Key</button>
			</div>
		</div>
	</div>
</section>
<script>
	function copyToClipboard() {
		var copyText = document.getElementById("secret-key");
		copyText.type = 'text';
		navigator.clipboard.writeText("<?php echo esc_attr( $this->bvinfo->getDefaultSecret() ); ?>");
		copyText.type = 'password';
		var copyButton = document.getElementById("copy-key");
		copyButton.textContent = 'Copied!';
		document.getElementById('view-key').textContent = 'View Key';
		var copyButton = document.getElementById("copy-key");
		copyButton.textContent = 'Copied!';
		setTimeout(() => copyButton.textContent = 'Copy Key', 2000);
	}
	document.getElementById('view-key').addEventListener('click', function() {
		var keyField = document.getElementById("secret-key");
		if (keyField.type === "password") {
			keyField.type = "text";
			this.textContent = 'Hide Key';
		} else {
			keyField.type = "password";
			this.textContent = 'View Key';
		}
	});
</script>