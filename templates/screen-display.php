<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Skate Club Screen Display</title>
	<?php wp_head(); ?>
</head>
<?php
$settings = get_option( 'skate_club_settings', array() );
$show_top_panel = ! empty( $settings['show_top_panel'] );
$body_class = 'skate-screen-display' . ( $show_top_panel ? ' has-top-panel' : ' no-top-panel' );
?>
<body class="<?php echo esc_attr( $body_class ); ?>">

<div id="screen-container">
	<?php
	$settings = get_option( 'skate_club_settings', array() );
	$show_top_panel = ! empty( $settings['show_top_panel'] );
	?>

	<!-- Top Panel -->
	<?php if ( $show_top_panel ) : ?>
	<div id="top-panel">
		<div id="logo-section">
			<img id="club-logo" src="" alt="Club Logo" style="display: none;">
			<h1 id="club-name"></h1>
		</div>
		<div id="session-info">
			<h2 id="session-name"></h2>
		</div>
	</div>
	<?php endif; ?>

	<!-- Main Content Area - Three Columns -->
	<div id="main-content">
		<!-- Column 1: Songs Queue (25% width) -->
		<div class="module-widget" id="songs-queue-widget">
			<div class="widget-header">
				<h3>Song Ranking</h3>
				<a href="<?php echo esc_url( home_url( '/skate-club-fullview-songs/' ) ); ?>" target="_blank" class="fullview-link" title="Open in full view">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
				</a>
			</div>
			<div class="module-content">
				<div id="song-rankings-list">
					<p class="empty-state">Be the first to request or vote for songs!</p>
				</div>
				<div class="qr-section-dual">
					<div class="qr-item">
						<img class="qr-code" data-qr-type="song_request" src="" alt="Scan to request a song">
						<p class="qr-label">Request</p>
					</div>
					<div class="qr-item">
						<img class="qr-code" data-qr-type="vote_songs" src="" alt="Scan to vote">
						<p class="qr-label">Vote</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Column 2: Spinner Wheel + Raffle Draw (40% width) -->
		<div id="middle-column">
			<!-- Spinner Wheel (65% height) -->
			<div class="module-widget" id="spinner-widget">
				<div class="widget-header">
					<h3>Spinner Wheel</h3>
					<a href="<?php echo esc_url( home_url( '/skate-club-fullview-spinner/' ) ); ?>" target="_blank" class="fullview-link" title="Open in full view">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
					</a>
				</div>
				<div class="module-content">
					<div id="spinner-container">
						<div id="spinner-wheel-wrapper">
							<?php
							$arrow_position = ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top';
							$spinner_label = ! empty( $settings['spinner_wheel_label'] ) ? $settings['spinner_wheel_label'] : 'Participant';
							?>
							<div id="spinner-arrow" class="arrow-<?php echo esc_attr( $arrow_position ); ?>"></div>
							<canvas id="spinner-canvas"></canvas>
						</div>
						<div id="spinner-info">

							<div id="spinner-result" style="display: none;">
								<h2>Winner!</h2>
								<p id="spinner-winner-name"></p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Raffle Draw (35% height) -->
			<div class="module-widget" id="raffle-widget">
				<div class="raffle-header">
					<div class="widget-header">
						<h3>Raffle Draw</h3>
						<a href="<?php echo esc_url( home_url( '/skate-club-fullview-raffle/' ) ); ?>" target="_blank" class="fullview-link" title="Open in full view">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
						</a>
					</div>
					<div class="raffle-qr">
						<img class="qr-code" data-qr-type="enter_raffle" src="" alt="Scan to enter raffle">
						<span class="qr-label">Join</span>
					</div>
				</div>
				<div class="module-content raffle-content-flex">
					<div class="raffle-left">
						<p class="count">Entries: <span id="raffle-count">0</span></p>
						<button type="button" id="draw-raffle-btn" style="display: none;">Draw Winner</button>
					</div>
					<div id="raffle-winner" class="raffle-right" style="display: none;">
						<h2>Winner!</h2>
						<p id="winner-name"></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Column 3: Photo & Video Gallery (45% width) -->
		<div class="module-widget" id="media-gallery-widget">
			<div class="gallery-header">
				<div class="widget-header">
					<h3>Photo & Video Gallery</h3>
					<a href="<?php echo esc_url( home_url( '/skate-club-fullview-gallery/' ) ); ?>" target="_blank" class="fullview-link" title="Open in full view">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
					</a>
				</div>
				<div class="gallery-qr" style="display: none;">
					<img class="qr-code" data-qr-type="upload_media" src="" alt="Scan to upload">
					<span class="qr-label">Upload</span>
				</div>
			</div>
			<div class="module-content">
				<div id="gallery-grid-container">
					<!-- Media items injected here -->
				</div>
				<p class="empty-state" id="gallery-empty" style="display:none;">Be the first to share a photo!</p>
			</div>
		</div>
	</div>
</div>

<!-- Fireworks Canvas -->
<canvas id="fireworks-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10000;"></canvas>

<!-- Raffle Draw Animation Modal -->
<div id="raffle-draw-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(248, 250, 252, 0.98); z-index: 9999; align-items: center; justify-content: center;">
	<!-- ... (rest of modal hidden for brevity, unchanged) ... -->
	<div class="raffle-modal-content" style="text-align: center; color: #1e293b; max-width: 1000px; padding: 50px; position: relative;">
		<div class="modal-glow" style="position: absolute; top: 50%; left: 50%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%); transform: translate(-50%, -50%); animation: modalGlow 3s ease-in-out infinite; pointer-events: none;"></div>
		<h1 style="color: #1e293b; font-size: 72px; margin-bottom: 60px; text-shadow: 0 2px 20px rgba(59, 130, 246, 0.2); position: relative; z-index: 1; background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: textShimmer 2s ease-in-out infinite;">DRAWING WINNER</h1>
		<div id="name-display" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%); padding: 80px 80px; border-radius: 30px; box-shadow: 0 4px 40px rgba(59, 130, 246, 0.2), inset 0 2px 20px rgba(59, 130, 246, 0.05); margin-bottom: 50px; min-height: 200px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(59, 130, 246, 0.3); position: relative; z-index: 1;">
			<div id="animated-name" style="font-size: 80px; font-weight: 800; color: #1e293b; text-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);"></div>
		</div>
		<div id="draw-progress" style="font-size: 28px; color: #64748b; margin-top: 40px; text-shadow: none; position: relative; z-index: 1; letter-spacing: 3px; text-transform: uppercase;"></div>
	</div>
</div>

<style>
/* ... styles ... */
@keyframes modalGlow { 0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); } 50% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); } }
@keyframes textShimmer { 0%, 100% { filter: brightness(1); } 50% { filter: brightness(1.3); } }
.celebration-emoji { position: fixed; font-size: 60px; pointer-events: none; z-index: 10001; animation: floatUp 3s ease-out forwards; }
@keyframes floatUp { 0% { transform: translateY(0) rotate(0deg); opacity: 1; } 100% { transform: translateY(-300px) rotate(360deg); opacity: 0; } }
</style>

<script>
// Localize script variables
<?php
$settings = get_option( 'skate_club_settings', array() );
$arrow_position = ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top';
?>
var skateClubScreen = {
	ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
	spinnerArrowPosition: '<?php echo esc_js( $arrow_position ); ?>'
};
console.log('Screen display script loading...', skateClubScreen);
jQuery(document).ready(function($) {
	console.log('jQuery ready, starting initialization...');
	
	// Gallery State
	let mediaItems = [];
	let galleryPage = 0;
	const itemsPerPage = 5; // 5 items + 1 QR = 6 items (3x2 grid)
	const rotationDuration = 10000; // 10 seconds
	let galleryRotationInterval;

	// Spinner State
	let spinnerEntries = [];
	// ... (rest of spinner vars) ...
	let spinnerAngle = 0;
	let isSpinning = false;
	let animationFrameId = null;
	let arrowPosition = skateClubScreen.spinnerArrowPosition || 'top';
	// ...

	// ... (fetchScreenData remains similar, just calling new gallery function) ...
	function fetchScreenData() {
		// ... existing fetch logic ...
		$.get(skateClubScreen.ajaxUrl, { action: 'skate_get_screen_data' }, function(response) {
			if (response.success) {
				updateDisplay(response.data);
			}
		});
	}

	function updateDisplay(data) {
		console.log('Update display data:', data);
		console.log('QR codes:', data.qr_codes);

		// Update session info
		if (data.session) {
			window.currentSessionId = data.session.id; // Store session ID for raffle draw
			$('#session-name').text(data.session.name);
			$('#session-date').text(new Date(data.session.date).toLocaleString());

			if (data.session.club_name) {
				$('#club-name').text(data.session.club_name);
			}

			if (data.session.logo_url) {
				$('#club-logo').attr('src', data.session.logo_url).show();
			}

			// Display QR codes from server
			if (data.qr_codes) {
				console.log('Calling displayQRCodes with:', data.qr_codes);
				displayQRCodes(data.qr_codes);
			} else {
				console.log('No QR codes in response');
			}
		}

		// Update song rankings or show recent requests if no voting list exists
		if (data.song_rankings && data.song_rankings.length > 0) {
			// Display ranked songs with points
			let html = '<ol class="rankings-list">';
			data.song_rankings.slice(0, 10).forEach(function(song) {
				html += '<li><span class="song-title">' + escapeHtml(song.song_title) + '</span> ';
				if (song.artist) {
					html += '<span class="artist">- ' + escapeHtml(song.artist) + '</span> ';
				}
				// html += '<span class="points">(' + song.total_points + ' pts)</span></li>';
			});
			html += '</ol>';
			$('#song-rankings-list').html(html);
		} else if (data.song_requests && data.song_requests.recent && data.song_requests.recent.length > 0) {
			// Fallback: Display recent song requests if no voting list created
			let html = '<ul class="recent-list">';
			data.song_requests.recent.forEach(function(request) {
				html += '<li><strong>' + escapeHtml(request.song_name) + '</strong>';
				if (request.artist) {
					html += ' - ' + escapeHtml(request.artist);
				}
				html += ' <em>(requested by ' + escapeHtml(request.first_name) + ')</em></li>';
			});
			html += '</ul>';
			$('#song-rankings-list').html(html);
		} else {
			// No songs at all
			$('#song-rankings-list').html('<p class="empty-state">Be the first to request or vote for songs!</p>');
		}
		
		// Update Spinner
		if (data.spinner && data.spinner.entries) {
			spinnerEntries = data.spinner.entries;
			// $('#spinner-count').text(spinnerEntries.length); // Removed per request
			if (data.spinner.arrow_position) arrowPosition = data.spinner.arrow_position;
			// Draw wheel once (static until clicked) but check spin state
			if (spinnerEntries.length > 0 && !isSpinning) {
				// Only redraw if not spinning to avoid glitching
				drawSpinnerWheel(false);
			}
		}

		// Security Nonce
		if (data.security && data.security.draw_nonce) {
			window.skateRaffleNonce = data.security.draw_nonce;
		}
		
		// Update Raffle
		$('#raffle-count').text(data.raffle.entry_count);
		if (data.raffle.entry_count > 0) {
			$('#draw-raffle-btn').show();
		} else {
			$('#draw-raffle-btn').hide();
		}

		if (data.raffle.winner) {
			$('#winner-name').text(data.raffle.winner.first_name + ' ' + data.raffle.winner.last_name);
			$('#raffle-winner').show();
		} else {
			$('#raffle-winner').hide();
		}
		// Store raffle entries for draw animation
		window.raffleEntries = data.raffle.entries || [];

		// Update Gallery
		if (data.media_gallery) {
			const prevLength = mediaItems.length;
			mediaItems = data.media_gallery;
			
			// Initial render or if empty
			if (prevLength === 0 && mediaItems.length >= 0) {
				renderGalleryPage();
				startGalleryRotation();
			} else {
				// If we have items and they just changed, the rotation will pick them up
				// But we should re-render current page immediately to show new items if adding to empty
				renderGalleryPage();
			}
		} else {
			mediaItems = [];
			renderGalleryPage();
		}
	}

	function displayQRCodes(qrCodes) {
		console.log('displayQRCodes called with:', qrCodes);

		// QR codes are base64 data URIs from server
		$.each(qrCodes, function(type, dataUri) {
			console.log('Setting QR code for type:', type, 'URI length:', dataUri ? dataUri.length : 0);
			var $img = $('img.qr-code[data-qr-type="' + type + '"]');
			console.log('Found', $img.length, 'image(s) for type:', type);
			$img.attr('src', dataUri);
		});
		
		// Force gallery re-render to pick up the updated QR code
		renderGalleryPage();
	}

	function renderGalleryPage() {
		// Calculate pagination based on media items (itemsPerPage = 5)
		const totalPages = Math.ceil(mediaItems.length / itemsPerPage) || 1;
		if (galleryPage >= totalPages) galleryPage = 0;

		const start = galleryPage * itemsPerPage;
		const end = start + itemsPerPage;
		const pageItems = mediaItems.slice(start, end);

		let html = '';
		
		// 1. Add QR Code as the first item
		const qrSrc = $('img.qr-code[data-qr-type="upload_media"]').first().attr('src');
		if (qrSrc) {
			html += `<div class="gallery-item qr-gallery-item" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; border: 2px dashed #e2e8f0; cursor: default;">
				<img src="${qrSrc}" alt="Scan to upload" style="width: 80%; height: 80%; object-fit: contain; border-radius: 0;">
				<span style="font-weight: 700; color: #64748b; font-size: 14px; margin-top: 5px;">Scan to Upload</span>
			</div>`;
		}

		if (mediaItems.length === 0 && !qrSrc) {
			$('#gallery-grid-container').empty();
			$('#gallery-empty').show();
			return;
		}
		
		$('#gallery-empty').hide();

		// 2. Add media items
		pageItems.forEach(item => {
			html += `<div class="gallery-item">`;
			if (item.media_type === 'photo') {
				html += `<img src="${item.url}" alt="Gallery" loading="lazy">`;
			} else {
				html += `<video src="${item.url}" autoplay muted loop playsinline></video>`;
			}
			html += `</div>`;
		});

		$('#gallery-grid-container').html(html);
		
		// Ensure videos autoplay
		$('#gallery-grid-container video').each(function() {
			var video = this;
			video.play().catch(function(error) {
				console.log("Video autoplay failed:", error);
				// Ensure muted is set (although generic attribute handles it)
				video.muted = true;
				video.play().catch(function(e) { console.error("Retry failed", e); });
			});
		});
	}

	function startGalleryRotation() {
		if (galleryRotationInterval) clearInterval(galleryRotationInterval);
		
		galleryRotationInterval = setInterval(() => {
			if (mediaItems.length > itemsPerPage) {
				galleryPage++;
				renderGalleryPage();
			}
		}, rotationDuration);
	}
    // ... (rest of file) ...

	// Spinner Wheel Functions
	// Helper function to lighten colors
	function lightenColor(color, percent) {
		const num = parseInt(color.replace('#', ''), 16);
		const amt = Math.round(2.55 * percent);
		const R = (num >> 16) + amt;
		const G = (num >> 8 & 0x00FF) + amt;
		const B = (num & 0x0000FF) + amt;
		return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
			(G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
			(B < 255 ? B < 1 ? 0 : B : 255))
			.toString(16).slice(1);
	}

	// Arrow angle based on position setting
	// Canvas: 0 = right, positive = clockwise
	function getArrowAngle() {
		switch(arrowPosition) {
			case 'right':  return 0;
			case 'bottom': return Math.PI / 2;
			case 'left':   return Math.PI;
			case 'top':
			default:       return Math.PI * 1.5; // 270 degrees
		}
	}

	// Animation state
	let spinStartTime = 0;
	let spinStartAngle = 0;
	let spinTargetAngle = 0;
	let spinDuration = 5000;
	let selectedWinnerIndex = -1;

	// Smooth easing function
	function easeOutQuint(t) {
		return 1 - Math.pow(1 - t, 5);
	}

	// Track last canvas size to avoid unnecessary resizes
	let lastCanvasSize = 0;

	// Resize canvas to match wrapper size
	function resizeSpinnerCanvas() {
		const wrapper = document.getElementById('spinner-wheel-wrapper');
		const canvas = document.getElementById('spinner-canvas');

		if (!wrapper || !canvas) return;

		// Use getBoundingClientRect for more accurate sizing with container queries
		const rect = wrapper.getBoundingClientRect();
		const size = Math.floor(rect.width);

		// Only resize if size actually changed (avoid unnecessary redraws)
		if (size === lastCanvasSize && size > 0) {
			return;
		}

		lastCanvasSize = size;

		// Set canvas resolution (for crisp rendering)
		// Use devicePixelRatio for sharp rendering on high-DPI displays
		const dpr = window.devicePixelRatio || 1;
		canvas.width = size * dpr;
		canvas.height = size * dpr;

		// Get context and reset any previous transforms
		const ctx = canvas.getContext('2d');
		ctx.setTransform(1, 0, 0, 1, 0, 0); // Reset transform
		ctx.scale(dpr, dpr); // Apply new scale

		// Set CSS size to maintain correct visual size
		canvas.style.width = size + 'px';
		canvas.style.height = size + 'px';

		// Redraw if we have entries (static, no animation)
		if (spinnerEntries.length > 0 && !isSpinning) {
			drawSpinnerWheel(false);
		}
	}

	// Draw the wheel - called once for static display, or repeatedly during animation
	function drawSpinnerWheel(animate) {
		const canvas = document.getElementById('spinner-canvas');
		if (!canvas || spinnerEntries.length === 0) {
			return;
		}

		const ctx = canvas.getContext('2d');
		const size = parseInt(canvas.style.width) || canvas.width;

		// Guard against invalid size
		if (size < 50) {
			return;
		}

		const centerX = size / 2;
		const centerY = size / 2;
		const radius = Math.max(20, Math.min(centerX, centerY) - 15);

		// Clear canvas
		ctx.clearRect(0, 0, size, size);

		const sliceAngle = (2 * Math.PI) / spinnerEntries.length;
		const colors = [
			'#3b82f6', '#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#06b6d4', '#6366f1', '#14b8a6',
			'#f43f5e', '#84cc16', '#0ea5e9', '#a855f7', '#eab308', '#22c55e', '#e11d48', '#0891b2'
		];

		// Draw outer ring
		ctx.beginPath();
		ctx.arc(centerX, centerY, radius + 5, 0, 2 * Math.PI);
		ctx.strokeStyle = '#3b82f6';
		ctx.lineWidth = 4;
		ctx.shadowColor = 'rgba(59, 130, 246, 0.5)';
		ctx.shadowBlur = 10;
		ctx.stroke();
		ctx.shadowBlur = 0;

		// Draw slices
		spinnerEntries.forEach(function(entry, index) {
			const startAngle = spinnerAngle + (index * sliceAngle);
			const endAngle = startAngle + sliceAngle;
			const baseColor = entry.color || colors[index % colors.length];

			// Draw slice
			ctx.beginPath();
			ctx.moveTo(centerX, centerY);
			ctx.arc(centerX, centerY, radius, startAngle, endAngle);
			ctx.closePath();
			ctx.fillStyle = baseColor;
			ctx.fill();
			ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
			ctx.lineWidth = 2;
			ctx.stroke();

			// Draw text with proper truncation
			ctx.save();
			ctx.translate(centerX, centerY);
			ctx.rotate(startAngle + sliceAngle / 2);
			ctx.textAlign = 'center';
			ctx.textBaseline = 'middle';
			ctx.fillStyle = '#fff';
			ctx.shadowColor = 'rgba(0, 0, 0, 0.7)';
			ctx.shadowBlur = 4;
			const fontSize = Math.max(11, Math.min(size * 0.045, 22));
			ctx.font = `bold ${fontSize}px Arial, sans-serif`;

			// Calculate available width for text (from center padding to edge padding)
			const centerPadding = size * 0.12; // Space from center
			const edgePadding = size * 0.08; // Space from edge
			const availableWidth = radius - centerPadding - edgePadding;
			const textPosition = centerPadding + (availableWidth / 2) + (size * 0.05);

			// Truncate text if too long
			let displayText = entry.participant_name;
			let textWidth = ctx.measureText(displayText).width;
			if (textWidth > availableWidth) {
				while (textWidth > availableWidth - 10 && displayText.length > 3) {
					displayText = displayText.slice(0, -1);
					textWidth = ctx.measureText(displayText + '…').width;
				}
				displayText += '…';
			}

			ctx.fillText(displayText, textPosition, 0);
			ctx.restore();
		});

		// Draw center circle
		const centerRadius = size * 0.08;
		ctx.beginPath();
		ctx.arc(centerX, centerY, centerRadius, 0, 2 * Math.PI);
		ctx.fillStyle = '#ffffff';
		ctx.fill();
		ctx.strokeStyle = '#3b82f6';
		ctx.lineWidth = 3;
		ctx.stroke();

		// Draw "SPIN" text
		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';
		ctx.fillStyle = '#3b82f6';
		ctx.font = `bold ${Math.max(12, size * 0.04)}px Arial`;
		ctx.fillText('SPIN', centerX, centerY);

		// Handle animation if spinning
		if (isSpinning && animate !== false) {
			const now = performance.now();
			const elapsed = now - spinStartTime;
			const progress = Math.min(elapsed / spinDuration, 1);
			const eased = easeOutQuint(progress);

			spinnerAngle = spinStartAngle + (spinTargetAngle - spinStartAngle) * eased;

			if (progress >= 1) {
				// Animation complete - reset state
				spinnerAngle = spinTargetAngle;
				isSpinning = false;
				if (animationFrameId) {
					cancelAnimationFrame(animationFrameId);
				}
				animationFrameId = null;

				// Draw final frame then show winner
				drawSpinnerWheel(false);
				showSpinnerWinner();
			} else {
				// Continue animation
				animationFrameId = requestAnimationFrame(function() {
					drawSpinnerWheel(true);
				});
			}
		}
	}

	function spinWheel() {
		if (isSpinning || spinnerEntries.length === 0) return;

		// Reset animation state completely
		if (animationFrameId) {
			cancelAnimationFrame(animationFrameId);
			animationFrameId = null;
		}

		$('#spinner-result').hide();

		// Select random winner
		selectedWinnerIndex = Math.floor(Math.random() * spinnerEntries.length);

		const numEntries = spinnerEntries.length;
		const sliceAngle = (2 * Math.PI) / numEntries;
		const arrowAngle = getArrowAngle();

		// Calculate where the wheel needs to stop
		// When drawn, slice i occupies: spinnerAngle + i*sliceAngle to spinnerAngle + (i+1)*sliceAngle
		// Slice center is at: spinnerAngle + i*sliceAngle + sliceAngle/2
		// For winner slice center to align with arrow:
		// spinnerAngle + selectedWinnerIndex*sliceAngle + sliceAngle/2 = arrowAngle
		// spinnerAngle = arrowAngle - selectedWinnerIndex*sliceAngle - sliceAngle/2

		let stopAngle = arrowAngle - (selectedWinnerIndex * sliceAngle) - (sliceAngle / 2);

		// Add small random offset so it doesn't always land exactly center
		stopAngle += (Math.random() - 0.5) * sliceAngle * 0.4;

		// Normalize stop angle to 0-2π
		stopAngle = ((stopAngle % (2 * Math.PI)) + (2 * Math.PI)) % (2 * Math.PI);

		// Calculate how many full rotations plus the final position
		const minRotations = 5;
		const extraRotations = Math.floor(Math.random() * 3);
		const totalRotations = (minRotations + extraRotations) * 2 * Math.PI;

		// Start from current position
		spinStartAngle = spinnerAngle;

		// Target = start + rotations + (stopAngle - normalizedStart)
		const normalizedStart = ((spinnerAngle % (2 * Math.PI)) + (2 * Math.PI)) % (2 * Math.PI);
		let angleDiff = stopAngle - normalizedStart;
		if (angleDiff < 0) angleDiff += 2 * Math.PI;

		spinTargetAngle = spinnerAngle + totalRotations + angleDiff;

		spinStartTime = performance.now();
		spinDuration = 4000 + Math.random() * 1500;
		isSpinning = true;

		console.log('🎰 SPIN DEBUG:', {
			arrowPosition: arrowPosition,
			arrowAngle: (arrowAngle * 180 / Math.PI).toFixed(1) + '°',
			winnerIndex: selectedWinnerIndex,
			winnerName: spinnerEntries[selectedWinnerIndex].participant_name,
			stopAngle: (stopAngle * 180 / Math.PI).toFixed(1) + '°',
			totalRotation: ((spinTargetAngle - spinStartAngle) * 180 / Math.PI).toFixed(1) + '°'
		});

		drawSpinnerWheel(true);
	}

	function showSpinnerWinner() {
		const winner = spinnerEntries[selectedWinnerIndex];
		console.log('🎯 Result:', winner.participant_name);
		$('#spinner-winner-name').text(winner.participant_name);
		$('#spinner-result').fadeIn();

		// Trigger fireworks and celebration emojis
		if (typeof window.triggerFireworks === 'function') {
			window.triggerFireworks();
		}
		if (typeof window.triggerCelebrationEmojis === 'function') {
			window.triggerCelebrationEmojis();
		}
	}

	// Spin handler - click canvas to spin
	$('#spinner-canvas').on('click', function() {
		spinWheel();
	});

	// Raffle Draw Handler
	$('#draw-raffle-btn').on('click', function() {
		if (!window.raffleEntries || window.raffleEntries.length === 0) {
			return; // Silently return if no entries
		}

		// Show modal
		$('#raffle-draw-modal').css('display', 'flex');
		$('#draw-progress').text('Shuffling entries...');

		// Animation variables
		let currentIndex = 0;
		let intervalSpeed = 50; // Start fast
		let iterations = 0;
		const maxIterations = 80; // Total cycles before slowing down
		let animationInterval;

		// Shuffle and cycle through names
		function animateDraw() {
			// Display current name
			$('#animated-name').text(window.raffleEntries[currentIndex].first_name + ' ' + window.raffleEntries[currentIndex].last_name);

			// Move to next entry
			currentIndex = (currentIndex + 1) % window.raffleEntries.length;
			iterations++;

			// Slow down animation as we approach the end
			if (iterations > maxIterations - 30) {
				intervalSpeed += 15; // Gradually slow down
				clearInterval(animationInterval);
				animationInterval = setInterval(animateDraw, intervalSpeed);
			}

			// Stop and select winner
			if (iterations >= maxIterations) {
				clearInterval(animationInterval);
				selectRaffleWinner();
			}

			// Update progress
			const progress = Math.min(100, Math.floor((iterations / maxIterations) * 100));
			$('#draw-progress').text(progress + '% complete...');
		}

		// Start animation
		animationInterval = setInterval(animateDraw, intervalSpeed);
	});

	function escapeHtml(text) {
		if (!text) return text;
		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}

	function selectRaffleWinner() {
		// Get active session ID from screen data
		$.post(skateClubScreen.ajaxUrl, {
			action: 'skate_draw_raffle_winner',
			session_id: window.currentSessionId || 0,
			nonce: window.skateRaffleNonce || ''
		}, function(response) {
			if (response.success) {
				const winner = response.data.winner;

				// Show winner with celebration
				$('#animated-name').text(winner.first_name + ' ' + winner.last_name);
				$('#draw-progress').html('<span style="color: #10b981; font-size: 40px; font-weight: 700;">WINNER!</span>');

				// Add celebration effect
				$('#name-display').css({
					'animation': 'rafflePulse 0.6s ease-in-out 4',
					'background': 'linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(59, 130, 246, 0.1) 100%)',
					'border-color': '#10b981',
					'box-shadow': '0 4px 40px rgba(16, 185, 129, 0.3), inset 0 2px 20px rgba(16, 185, 129, 0.1)'
				});

				// Trigger fireworks and celebration emojis
				if (typeof window.triggerFireworks === 'function') {
					window.triggerFireworks();
				}
				if (typeof window.triggerCelebrationEmojis === 'function') {
					window.triggerCelebrationEmojis();
				}

				// Close modal and refresh after 4 seconds
				setTimeout(function() {
					$('#raffle-draw-modal').hide();
					fetchScreenData(); // Refresh to show new winner
				}, 4000);
			} else {
				// Silently close modal on error
				$('#raffle-draw-modal').hide();
			}
		}).fail(function() {
			// Silently close modal on error
			$('#raffle-draw-modal').hide();
		});
	}

	// Initial fetch
	fetchScreenData();

	// Poll every 10 seconds for updates
	setInterval(fetchScreenData, 10000);

	// Initialize canvas size
	resizeSpinnerCanvas();

	// Resize handling with debounce
	let resizeTimeout;

	// Use ResizeObserver for more accurate container-based resizing
	const wrapper = document.getElementById('spinner-wheel-wrapper');
	if (wrapper && window.ResizeObserver) {
		const resizeObserver = new ResizeObserver(function(entries) {
			// Debounce the resize
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function() {
				// Only resize if wrapper actually changed size
				const entry = entries[0];
				if (entry && entry.contentRect) {
					resizeSpinnerCanvas();
				}
			}, 150);
		});
		resizeObserver.observe(wrapper);
	}

	// Fallback to window resize for older browsers
	$(window).on('resize', function() {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(resizeSpinnerCanvas, 150);
	});

	// ==================== FIREWORKS ANIMATION ====================
	const fireworksCanvas = document.getElementById('fireworks-canvas');
	const fwCtx = fireworksCanvas ? fireworksCanvas.getContext('2d') : null;
	let fireworksParticles = [];
	let fireworksActive = false;
	let fireworksAnimationId = null;

	function resizeFireworksCanvas() {
		if (!fireworksCanvas) return;
		fireworksCanvas.width = window.innerWidth;
		fireworksCanvas.height = window.innerHeight;
	}

	resizeFireworksCanvas();
	$(window).on('resize', resizeFireworksCanvas);

	class FireworkParticle {
		constructor(x, y, color, velocity, size, decay, gravity) {
			this.x = x;
			this.y = y;
			this.color = color;
			this.velocity = velocity;
			this.size = size;
			this.alpha = 1;
			this.decay = decay;
			this.gravity = gravity;
			this.trail = [];
			this.trailLength = 5;
		}

		update() {
			this.trail.push({ x: this.x, y: this.y, alpha: this.alpha });
			if (this.trail.length > this.trailLength) {
				this.trail.shift();
			}
			this.velocity.y += this.gravity;
			this.x += this.velocity.x;
			this.y += this.velocity.y;
			this.alpha -= this.decay;
		}

		draw(ctx) {
			// Draw trail
			this.trail.forEach((point, index) => {
				const trailAlpha = (index / this.trailLength) * point.alpha * 0.5;
				ctx.beginPath();
				ctx.arc(point.x, point.y, this.size * 0.5, 0, Math.PI * 2);
				ctx.fillStyle = this.color.replace('1)', trailAlpha + ')');
				ctx.fill();
			});

			// Draw particle
			ctx.beginPath();
			ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
			ctx.fillStyle = this.color.replace('1)', this.alpha + ')');
			ctx.fill();
		}
	}

	function createFirework(x, y) {
		const colors = [
			'rgba(59, 130, 246, 1)',   // Blue
			'rgba(139, 92, 246, 1)',   // Purple
			'rgba(236, 72, 153, 1)',   // Pink
			'rgba(16, 185, 129, 1)',   // Green
			'rgba(245, 158, 11, 1)',   // Orange
			'rgba(6, 182, 212, 1)',    // Cyan
			'rgba(244, 63, 94, 1)'     // Rose
		];

		const particleCount = 80 + Math.random() * 40;
		const color = colors[Math.floor(Math.random() * colors.length)];

		for (let i = 0; i < particleCount; i++) {
			const angle = (Math.PI * 2 / particleCount) * i + Math.random() * 0.2;
			const speed = 3 + Math.random() * 5;
			fireworksParticles.push(new FireworkParticle(
				x, y, color,
				{ x: Math.cos(angle) * speed, y: Math.sin(angle) * speed },
				2 + Math.random() * 2,
				0.015 + Math.random() * 0.01,
				0.05
			));
		}
	}

	function animateFireworks() {
		if (!fwCtx || !fireworksCanvas) return;

		fwCtx.clearRect(0, 0, fireworksCanvas.width, fireworksCanvas.height);

		fireworksParticles = fireworksParticles.filter(p => p.alpha > 0);

		fireworksParticles.forEach(particle => {
			particle.update();
			particle.draw(fwCtx);
		});

		if (fireworksParticles.length > 0) {
			fireworksAnimationId = requestAnimationFrame(animateFireworks);
		} else {
			fireworksActive = false;
			if (fireworksAnimationId) {
				cancelAnimationFrame(fireworksAnimationId);
				fireworksAnimationId = null;
			}
		}
	}

	window.triggerFireworks = function() {
		if (!fireworksCanvas) return;

		fireworksActive = true;
		const positions = [
			{ x: fireworksCanvas.width * 0.25, y: fireworksCanvas.height * 0.4 },
			{ x: fireworksCanvas.width * 0.5, y: fireworksCanvas.height * 0.3 },
			{ x: fireworksCanvas.width * 0.75, y: fireworksCanvas.height * 0.4 },
			{ x: fireworksCanvas.width * 0.35, y: fireworksCanvas.height * 0.5 },
			{ x: fireworksCanvas.width * 0.65, y: fireworksCanvas.height * 0.5 },
			{ x: fireworksCanvas.width * 0.2, y: fireworksCanvas.height * 0.6 },
			{ x: fireworksCanvas.width * 0.8, y: fireworksCanvas.height * 0.6 }
		];

		positions.forEach((pos, i) => {
			setTimeout(() => createFirework(pos.x, pos.y), i * 200);
		});

		// Additional random fireworks
		for (let i = 0; i < 5; i++) {
			setTimeout(() => {
				createFirework(
					100 + Math.random() * (fireworksCanvas.width - 200),
					100 + Math.random() * (fireworksCanvas.height * 0.5)
				);
			}, 1500 + i * 300);
		}

		animateFireworks();
	};

	window.triggerCelebrationEmojis = function() {
		const emojis = ['🎉', '🎊', '✨', '🎈', '🏆', '⭐', '🌟', '💫', '🎆', '🎇'];
		const celebrationCount = 20;

		for (let i = 0; i < celebrationCount; i++) {
			setTimeout(() => {
				const emoji = document.createElement('div');
				emoji.className = 'celebration-emoji';
				emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];
				emoji.style.left = Math.random() * window.innerWidth + 'px';
				emoji.style.top = window.innerHeight + 'px';
				emoji.style.animationDelay = '0s';
				emoji.style.animationDuration = (2 + Math.random() * 2) + 's';

				document.body.appendChild(emoji);

				setTimeout(() => emoji.remove(), 4000);
			}, i * 100);
		}
	};
});
</script>

<script>
// Suppress theme errors that reference missing elements
window.addEventListener('error', function(e) {
	if (e.message && e.message.includes('classList')) {
		e.preventDefault();
		return true;
	}
}, true);
</script>

<?php wp_footer(); ?>
</body>
</html>
