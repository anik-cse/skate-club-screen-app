<?php
/**
 * Settings admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Display active session notice
Skate_Club_Admin_Menu::display_active_session_notice();

// Display settings update notices
if ( isset( $_GET['settings-updated'] ) ) {
	echo '<div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully!</strong></p></div>';
}
if ( isset( $_GET['settings-error'] ) ) {
	$error_message = sanitize_text_field( $_GET['settings-error'] );
	echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> ' . esc_html( $error_message ) . '</p></div>';
}

// Get current settings
$settings = get_option( 'skate_club_settings', array() );

// Default settings
$defaults = array(
	'club_name'  => '',
	'logo_url'   => '',
	'default_session_duration' => 4, // hours
);

$settings = wp_parse_args( $settings, $defaults );
?>

<div class="wrap skate-club-admin">
	<h1>Settings</h1>

	<form id="settings-form" method="post">
		<?php wp_nonce_field( 'skate_save_settings', 'settings_nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="club_name">Club Name</label>
				</th>
				<td>
					<input type="text"
					       id="club_name"
					       name="club_name"
					       value="<?php echo esc_attr( $settings['club_name'] ); ?>"
					       class="regular-text">
					<p class="description">Default club name for new sessions</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="logo_url">Club Logo</label>
				</th>
				<td>
					<div id="logo-preview" style="margin-bottom: 10px;">
						<?php if ( ! empty( $settings['logo_url'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['logo_url'] ); ?>"
							     alt="Logo Preview"
							     style="max-width: 200px; max-height: 100px; display: block; margin-bottom: 10px;">
						<?php endif; ?>
					</div>
					<input type="hidden"
					       id="logo_url"
					       name="logo_url"
					       value="<?php echo esc_attr( $settings['logo_url'] ); ?>">
					<button type="button" class="button" id="upload-logo-button">
						<?php echo ! empty( $settings['logo_url'] ) ? 'Change Logo' : 'Upload Logo'; ?>
					</button>
					<?php if ( ! empty( $settings['logo_url'] ) ) : ?>
						<button type="button" class="button" id="remove-logo-button">Remove Logo</button>
					<?php endif; ?>
					<p class="description">Default logo for new sessions (recommended size: 200x100px)</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="default_session_duration">Default Session Duration</label>
				</th>
				<td>
					<input type="number"
					       id="default_session_duration"
					       name="default_session_duration"
					       value="<?php echo esc_attr( $settings['default_session_duration'] ); ?>"
					       min="1"
					       max="24"
					       class="small-text"> hours
					<p class="description">Default duration for new sessions</p>
				</td>
			</tr>
		</table>

		<h2>Screen Display Settings</h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="screen_refresh_interval">Refresh Interval</label>
				</th>
				<td>
					<input type="number"
					       id="screen_refresh_interval"
					       name="screen_refresh_interval"
					       value="<?php echo esc_attr( ! empty( $settings['screen_refresh_interval'] ) ? $settings['screen_refresh_interval'] : 5 ); ?>"
					       min="1"
					       max="60"
					       class="small-text"> seconds
					<p class="description">How often the screen display refreshes data</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="media_rotation_interval">Media Rotation Interval</label>
				</th>
				<td>
					<input type="number"
					       id="media_rotation_interval"
					       name="media_rotation_interval"
					       value="<?php echo esc_attr( ! empty( $settings['media_rotation_interval'] ) ? $settings['media_rotation_interval'] : 5 ); ?>"
					       min="1"
					       max="60"
					       class="small-text"> seconds
					<p class="description">How often media gallery rotates images/videos</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="show_top_panel">Show Top Panel</label>
				</th>
				<td>
					<label>
						<input type="checkbox"
						       id="show_top_panel"
						       name="show_top_panel"
						       value="1"
						       <?php checked( ! empty( $settings['show_top_panel'] ) ); ?>>
						Display the top panel on screen display
					</label>
					<p class="description">Show or hide the top information panel (default: hidden)</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="spinner_arrow_position">Spinner Wheel Arrow Position</label>
				</th>
				<td>
					<select id="spinner_arrow_position" name="spinner_arrow_position">
						<option value="top" <?php selected( ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top', 'top' ); ?>>Top</option>
						<option value="right" <?php selected( ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top', 'right' ); ?>>Right</option>
						<option value="bottom" <?php selected( ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top', 'bottom' ); ?>>Bottom</option>
						<option value="left" <?php selected( ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top', 'left' ); ?>>Left</option>
					</select>
					<p class="description">Position of the arrow pointer on the spinner wheel</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="spinner_wheel_label">Spinner Wheel Label</label>
				</th>
				<td>
					<input type="text"
					       id="spinner_wheel_label"
					       name="spinner_wheel_label"
					       value="<?php echo esc_attr( ! empty( $settings['spinner_wheel_label'] ) ? $settings['spinner_wheel_label'] : 'Participant' ); ?>"
					       class="regular-text"
					       placeholder="Participant">
					<p class="description">Label for spinner wheel entries (e.g., "Participant" or "Reward")</p>
				</td>
			</tr>
		</table>

		<h2>Module Settings</h2>
		<table class="form-table">
			<tr>
				<th scope="row">Song Requests Limit</th>
				<td>
					<input type="number"
					       id="song_requests_recent_limit"
					       name="song_requests_recent_limit"
					       value="<?php echo esc_attr( ! empty( $settings['song_requests_recent_limit'] ) ? $settings['song_requests_recent_limit'] : 5 ); ?>"
					       min="1"
					       max="20"
					       class="small-text">
					<p class="description">Number of recent song requests to display on screen</p>
				</td>
			</tr>

			<tr>
				<th scope="row">Song Rankings Limit</th>
				<td>
					<input type="number"
					       id="song_rankings_limit"
					       name="song_rankings_limit"
					       value="<?php echo esc_attr( ! empty( $settings['song_rankings_limit'] ) ? $settings['song_rankings_limit'] : 10 ); ?>"
					       min="1"
					       max="50"
					       class="small-text">
					<p class="description">Number of top songs to display in rankings</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="max_image_size">Max Image Upload Size</label>
				</th>
				<td>
					<input type="number"
					       id="max_image_size"
					       name="max_image_size"
					       value="<?php echo esc_attr( ! empty( $settings['max_image_size'] ) ? $settings['max_image_size'] : 10 ); ?>"
					       min="1"
					       max="50"
					       class="small-text"> MB
					<p class="description">Maximum file size for photo uploads (default: 10MB)</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="max_video_size">Max Video Upload Size</label>
				</th>
				<td>
					<input type="number"
					       id="max_video_size"
					       name="max_video_size"
					       value="<?php echo esc_attr( ! empty( $settings['max_video_size'] ) ? $settings['max_video_size'] : 50 ); ?>"
					       min="1"
					       max="500"
					       class="small-text"> MB
					<p class="description">Maximum file size for video uploads (default: 50MB)</p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary">Save Settings</button>
		</p>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	// WordPress Media Uploader
	var mediaUploader;

	$('#upload-logo-button').on('click', function(e) {
		e.preventDefault();

		// If the uploader object has already been created, reopen the dialog
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}

		// Create the media uploader
		mediaUploader = wp.media({
			title: 'Choose Club Logo',
			button: {
				text: 'Use this logo'
			},
			multiple: false,
			library: {
				type: 'image'
			}
		});

		// When an image is selected, run a callback
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();

			// Set the logo URL in the hidden field
			$('#logo_url').val(attachment.url);

			// Update the preview
			$('#logo-preview').html(
				'<img src="' + attachment.url + '" alt="Logo Preview" ' +
				'style="max-width: 200px; max-height: 100px; display: block; margin-bottom: 10px;">'
			);

			// Update button text and show remove button
			$('#upload-logo-button').text('Change Logo');
			if ($('#remove-logo-button').length === 0) {
				$('#upload-logo-button').after(
					'<button type="button" class="button" id="remove-logo-button">Remove Logo</button>'
				);
			}
		});

		// Open the uploader dialog
		mediaUploader.open();
	});

	// Remove logo
	$(document).on('click', '#remove-logo-button', function(e) {
		e.preventDefault();

		if (confirm('Are you sure you want to remove the logo?')) {
			$('#logo_url').val('');
			$('#logo-preview').html('');
			$('#upload-logo-button').text('Upload Logo');
			$(this).remove();
		}
	});

	// Save settings
	$('#settings-form').on('submit', function(e) {
		e.preventDefault();

		const formData = {
			action: 'skate_save_settings',
			nonce: skateClubAdmin.nonce,
			club_name: $('#club_name').val(),
			logo_url: $('#logo_url').val(),
			default_session_duration: $('#default_session_duration').val(),
			screen_refresh_interval: $('#screen_refresh_interval').val(),
			media_rotation_interval: $('#media_rotation_interval').val(),
			show_top_panel: $('#show_top_panel').is(':checked') ? 1 : 0,
			spinner_arrow_position: $('#spinner_arrow_position').val(),
			spinner_wheel_label: $('#spinner_wheel_label').val(),
			song_requests_recent_limit: $('#song_requests_recent_limit').val(),
			song_rankings_limit: $('#song_rankings_limit').val(),
			max_image_size: $('#max_image_size').val(),
			max_video_size: $('#max_video_size').val()
		};

		$.post(skateClubAdmin.ajaxUrl, formData, function(response) {
			if (response.success) {
				window.location.href = window.location.pathname + '?page=skate-club-settings&settings-updated=1';
			} else {
				const errorMsg = response.data ? response.data.message : 'Unknown error';
				window.location.href = window.location.pathname + '?page=skate-club-settings&settings-error=' + encodeURIComponent(errorMsg);
			}
		}).fail(function() {
			window.location.href = window.location.pathname + '?page=skate-club-settings&settings-error=' + encodeURIComponent('Failed to save settings');
		});
	});
});
</script>
