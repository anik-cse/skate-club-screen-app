<?php
/**
 * Song Request Form Template.
 *
 * @package    Skate_Club_Screen
 */

// Get currently active session instead of requiring URL parameters
$session = Skate_Club_Session_Manager::get_active_session();

if ( ! $session ) {
	include __DIR__ . '/no-active-session.php';
	exit;
}

$session_id = $session->id;

$nonce = wp_create_nonce( 'skate_song_request_' . $session_id );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Song Request - <?php echo esc_html( $session->session_name ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="skate-form-page">

<div class="form-container">
	<h1>Song Request</h1>
	<p>Request your favorite song for <?php echo esc_html( $session->session_name ); ?></p>

	<form id="song-request-form" class="skate-form">
		<div class="form-group">
			<label for="first_name">First Name *</label>
			<input type="text" id="first_name" name="first_name" required>
		</div>

		<div class="form-group">
			<label for="last_name">Last Name *</label>
			<input type="text" id="last_name" name="last_name" required>
		</div>

		<div class="form-group">
			<label for="song_name">Requested Song Name *</label>
			<input type="text" id="song_name" name="song_name" required placeholder="Artist - Song Title">
		</div>

		<div class="form-group consent-group">
			<label for="user_consent">
				<input type="checkbox" id="user_consent" name="user_consent" required>
				<span>I agree to the <a href="/terms-and-privacy-policy/" target="_blank">terms and privacy policy</a>*</span>
			</label>
		</div>

		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>">
		<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">

		<button type="submit" class="submit-btn">Submit Request</button>
	</form>

	<div id="form-message" style="display: none;"></div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#song-request-form').on('submit', function(e) {
		e.preventDefault();

		const btn = $(this).find('.submit-btn');
		btn.prop('disabled', true).text('Submitting...');

		const formData = {
			action: 'skate_submit_song_request',
			first_name: $('#first_name').val(),
			last_name: $('#last_name').val(),
			song_name: $('#song_name').val(),
			session_id: $('input[name="session_id"]').val(),
			nonce: $('input[name="nonce"]').val()
		};

		$.post(skateClubForm.ajaxUrl, formData, function(response) {
			$('#form-message').show().html(
				'<div class="' + (response.success ? 'success' : 'error') + '">' +
				response.data.message +
				'</div>'
			);

			if (response.success) {
				$('#song-request-form')[0].reset();
				setTimeout(function() {
					// Try to close window - if it fails, show message
					window.close();
					// If window didn't close (private browsing), show message
					setTimeout(function() {
						if (!window.closed) {
							$('#form-message').html('<div class="success">Request submitted successfully! You can now close this tab.</div>');
						}
					}, 100);
				}, 2000);
			} else {
				btn.prop('disabled', false).text('Submit Request');
			}
		});
	});
});
</script>

<?php wp_footer(); ?>
</body>
</html>
