<div class="wrap">
	<h1>Phanes Marketing License</h1>
	<?php settings_errors(); ?>
	<p class="description">Phanes Marketing is a merchant marketing tool that simplifies marketing management for a merchant's woocommerce store.</p>
	<form method="post" action="options.php">
		<?php
		settings_fields( $this->slug );

		do_settings_sections( $this->slug );

		submit_button( 'Save License' );
		?>
	</form>
</div>
