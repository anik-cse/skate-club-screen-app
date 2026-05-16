<?php
/**
 * Media Upload Form Template.
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

$nonce = wp_create_nonce( 'skate_upload_media_' . $session_id );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Upload Media - <?php echo esc_html( $session->session_name ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="skate-form-page">

<div class="form-container">
	<h1>Upload Photo or Video</h1>
	<p>Share your photos and videos from <?php echo esc_html( $session->session_name ); ?></p>
	<p><small>Allowed: JPG, PNG, GIF (max 10MB) | MP4, MOV, AVI, WEBM, MKV (max 50MB)</small></p>

	<form id="media-upload-form" class="skate-form" enctype="multipart/form-data">
		<div class="form-group">
			<label for="media_file">Select Photo or Video *</label>
			<input type="file" id="media_file" name="media_file" accept="image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm,video/ogg,video/3gpp" multiple required>
		</div>

		<div id="upload-preview" style="display: none; margin: 20px 0;">
			<img id="preview-image" style="max-width: 100%; display: none;">
			<video id="preview-video" controls style="max-width: 100%; display: none;"></video>
		</div>

		<div id="upload-progress" style="display: none;">
			<div class="progress-bar">
				<div class="progress-fill" style="width: 0%"></div>
			</div>
			<p id="progress-text">Uploading...</p>
		</div>

		<div class="form-group consent-group">
			<label for="user_consent">
				<input type="checkbox" id="user_consent" name="user_consent" required>
				<span>I have permission to upload this content and agree to the <a href="/terms-and-privacy-policy/" target="_blank">terms and privacy policy</a>*</span>
			</label>
		</div>

		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>">
		<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">

		<button type="submit" class="submit-btn">Upload</button>
	</form>

	<div id="form-message" style="display: none;"></div>
</div>

<script>
jQuery(document).ready(function($) {
	// Preview file
	$('#media_file').on('change', function(e) {
		const files = e.target.files;
		const previewContainer = $('#upload-preview');
		
		// Clear previous previews
		previewContainer.empty().hide();
		
		if (!files || files.length === 0) return;

		previewContainer.show();
		
		// CSS for grid layout
		previewContainer.css({
			'display': 'grid',
			'grid-template-columns': 'repeat(auto-fill, minmax(100px, 1fr))',
			'gap': '10px'
		});

		Array.from(files).forEach(file => {
			const reader = new FileReader();
			const itemContainer = $('<div class="preview-item" style="position: relative; aspect-ratio: 1;"></div>');
			
			reader.onload = function(e) {
				if (file.type.startsWith('image/')) {
					itemContainer.append(
						$('<img>').attr('src', e.target.result)
							.css({
								'width': '100%',
								'height': '100%',
								'object-fit': 'cover',
								'border-radius': '4px'
							})
					);
				} else if (file.type.startsWith('video/')) {
					itemContainer.append(
						$('<video>').attr('src', e.target.result)
							.css({
								'width': '100%',
								'height': '100%',
								'object-fit': 'cover',
								'border-radius': '4px',
								'background': '#000'
							})
					);
				}
				previewContainer.append(itemContainer);
			};
			reader.readAsDataURL(file);
		});
	});

	// Compression Helper
	async function compressImage(file, maxWidth = 1920, quality = 0.8) {
		return new Promise((resolve) => {
			if (!file.type.startsWith('image/')) {
				resolve(file); // Return original if not image
				return;
			}

			const reader = new FileReader();
			reader.readAsDataURL(file);
			reader.onload = event => {
				const img = new Image();
				img.src = event.target.result;
				img.onload = () => {
					let width = img.width;
					let height = img.height;

					// Resize logic
					if (width > maxWidth) {
						height = Math.round((height * maxWidth) / width);
						width = maxWidth;
					}

					const canvas = document.createElement('canvas');
					canvas.width = width;
					canvas.height = height;
					const ctx = canvas.getContext('2d');
					ctx.drawImage(img, 0, 0, width, height);

					canvas.toBlob((blob) => {
						if (!blob) {
							resolve(file); // Fallback
							return;
						}
						// Create new File object
						const newFile = new File([blob], file.name, {
							type: 'image/jpeg',
							lastModified: Date.now(),
						});
						resolve(newFile);
					}, 'image/jpeg', quality);
				};
			};
		});
	}

	$('#media-upload-form').on('submit', async function(e) {
		e.preventDefault();

		const btn = $(this).find('.submit-btn');
		btn.prop('disabled', true).text('Compressing & Uploading...');
		$('#upload-progress').show();
		$('#progress-text').text('Processing images...');

		const formData = new FormData();
		formData.append('action', 'skate_upload_media');
		
		const fileInput = $('#media_file')[0];
		const files = fileInput.files;

		// Process files concurrently
		const filePromises = Array.from(files).map(file => compressImage(file));
		const processedFiles = await Promise.all(filePromises);

		processedFiles.forEach(file => {
			formData.append('media_file[]', file);
		});
		
		formData.append('session_id', $('input[name="session_id"]').val());
		formData.append('nonce', $('input[name="nonce"]').val());

		$.ajax({
			url: skateClubForm.ajaxUrl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			xhr: function() {
				const xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener('progress', function(e) {
					if (e.lengthComputable) {
						const percent = Math.round((e.loaded / e.total) * 100);
						$('.progress-fill').css('width', percent + '%');
						$('#progress-text').text('Uploading: ' + percent + '%');
					}
				}, false);
				return xhr;
			},
			success: function(response) {
				$('#upload-progress').hide();
				$('#form-message').show().html(
					'<div class="' + (response.success ? 'success' : 'error') + '">' +
					(response.data.message || response.data) +
					'</div>'
				);

				if (response.success) {
					$('#media-upload-form')[0].reset();
					$('#upload-preview').empty().hide();
					btn.text('Upload'); // Reset button text even on success for UX if window stays open
					
					setTimeout(function() {
						// Try to close window - if it fails, show message
						window.close();
						// If window didn't close (private browsing), show message
						setTimeout(function() {
							if (!window.closed) {
								$('#form-message').html('<div class="success">Media uploaded successfully! You can now close this tab.</div>');
							}
						}, 100);
					}, 2000);
				} else {
					btn.prop('disabled', false).text('Upload');
				}
			},
			error: function() {
				$('#upload-progress').hide();
				$('#form-message').show().html('<div class="error">Upload failed. Please try again.</div>');
				btn.prop('disabled', false).text('Upload');
			}
		});
	});
});
</script>

<?php wp_footer(); ?>
</body>
</html>
