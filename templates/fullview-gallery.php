<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Photo & Video Gallery - Full View</title>
	<?php wp_head(); ?>
	<style>
		body.fullview-page {
			margin: 0;
			padding: 0;
			font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
			background: #f8fafc;
			color: #1e293b;
			height: 100vh;
			overflow: hidden;
			display: flex;
			flex-direction: column;
		}

		.fullview-container {
			flex: 1;
			display: flex;
			flex-direction: column;
			padding: 20px;
			height: 100vh;
			box-sizing: border-box;
		}

		.fullview-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 15px;
			padding-bottom: 15px;
			border-bottom: 3px solid;
			border-image: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899) 1;
			flex-shrink: 0;
		}

		.fullview-header h1 {
			margin: 0;
			font-size: 2em;
			color: #1e293b;
		}

		.back-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 10px 20px;
			background: linear-gradient(135deg, #3b82f6, #8b5cf6);
			color: white;
			text-decoration: none;
			border-radius: 50px;
			font-weight: 600;
			transition: transform 0.2s, box-shadow 0.2s;
		}

		.back-link:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
		}

		.stats-bar {
			display: flex;
			gap: 20px;
			margin-bottom: 15px;
			padding-right: 20px;
			align-items: center;
			flex-shrink: 0;
		}

		.stat-item {
			text-align: center;
			background: white;
			padding: 8px 15px;
			border-radius: 8px;
			border: 1px solid #e2e8f0;
			min-width: 80px;
		}

		.stat-value {
			font-size: 1.2em;
			font-weight: 700;
			color: #3b82f6;
		}

		.stat-label {
			color: #64748b;
			font-size: 0.8em;
		}

		/* Masonry Gallery Styles */
		/* Hero Grid Gallery Styles */
		.gallery-container {
			flex: 1;
			overflow: hidden;
			min-height: 0;
			position: relative;
			padding-right: 0;
		}
		
		.gallery-grid {
			display: grid;
			grid-template-columns: repeat(5, 1fr); /* 5 columns */
			grid-template-rows: repeat(2, 1fr); /* 2 rows */
			gap: 10px;
			width: 100%;
			height: 100%;
		}
		
		/* Uniform Grid - No special Hero classes needed */
		/* Items will auto-flow into the 10 slots */

		.media-item {
			margin-bottom: 0;
			border-radius: 8px;
			overflow: hidden;
			background: #e2e8f0;
			cursor: pointer;
			position: relative;
			opacity: 0;
			animation: fadeIn 0.5s forwards;
			width: 100%;
			height: 100%;
			display: block; /* Reset flex */
		}

		@keyframes fadeIn {
			from { opacity: 0; transform: scale(0.98); }
			to { opacity: 1; transform: scale(1); }
		}

		.media-item img,
		.media-item video {
			width: 100%;
			height: 100%;
			max-height: none; /* Remove max-height constraint */
			display: block;
			object-fit: cover; /* Crop to fill */
			border-radius: 0; /* Container handles radius */
		}
		
		.video-indicator {
			position: absolute;
			top: 8px;
			right: 8px;
			background: rgba(0, 0, 0, 0.6);
			color: white;
			padding: 4px 6px;
			border-radius: 4px;
			font-size: 10px;
			font-weight: 600;
			display: flex;
			align-items: center;
			gap: 4px;
			z-index: 2;
		}

		.empty-state {
			text-align: center;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			width: 100%;
			color: #64748b;
			font-size: 1.5em;
		}

		/* Rotation Progress Bar */
		.rotation-progress {
			position: absolute;
			bottom: 0;
			left: 0;
			width: 100%;
			height: 4px;
			background: #e2e8f0;
			z-index: 100;
		}

		.rotation-bar {
			height: 100%;
			background: linear-gradient(90deg, #3b82f6, #ec4899);
			width: 0%;
			transition: width 0.1s linear;
		}

		/* Lightbox */
		#lightbox {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.95);
			z-index: 9999;
			align-items: center;
			justify-content: center;
		}

		#lightbox.active {
			display: flex;
		}

		#lightbox-content {
			max-width: 90%;
			max-height: 90%;
			position: relative;
		}

		#lightbox-content img,
		#lightbox-content video {
			max-width: 100%;
			max-height: 85vh;
			border-radius: 8px;
			box-shadow: 0 0 50px rgba(0,0,0,0.5);
		}

		#lightbox-close {
			position: absolute;
			top: 20px;
			right: 30px;
			color: white;
			font-size: 40px;
			cursor: pointer;
			opacity: 0.7;
			transition: opacity 0.2s;
			z-index: 10000;
		}

		#lightbox-close:hover {
			opacity: 1;
		}
	</style>
</head>
<body class="fullview-page">

<div class="fullview-container">
	<div class="fullview-header">
		<h1>Photo & Video Gallery</h1>
		<div class="stats-bar">
			<div class="stat-item">
				<div class="stat-value" id="page-info">1/1</div>
				<div class="stat-label">Page</div>
			</div>
			<div class="stat-item">
				<div class="stat-value" id="total-media">0</div>
				<div class="stat-label">Total</div>
			</div>
			<div class="stat-item">
				<div class="stat-value" id="photo-count">0</div>
				<div class="stat-label">Photos</div>
			</div>
			<div class="stat-item">
				<div class="stat-value" id="video-count">0</div>
				<div class="stat-label">Videos</div>
			</div>
		</div>
		<a href="<?php echo esc_url( home_url( '/skate-club-screen-display/' ) ); ?>" class="back-link">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			Back
		</a>
	</div>

	<div class="gallery-container">
		<div class="gallery-grid" id="gallery-grid">
			<!-- Media items will be injected here -->
		</div>
		<div class="empty-state" id="gallery-empty" style="display: none;">
			<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 15px; opacity: 0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
			<br>
			No media available.
		</div>
	</div>
	
	<div class="rotation-progress">
		<div class="rotation-bar" id="rotation-bar"></div>
	</div>
</div>

<div id="lightbox">
	<span id="lightbox-close">&times;</span>
	<div id="lightbox-content"></div>
</div>

<script>
var skateClubScreen = {
	ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>'
};

document.addEventListener('DOMContentLoaded', function() {
	const lightbox = document.getElementById('lightbox');
	const lightboxContent = document.getElementById('lightbox-content');
	const galleryGrid = document.getElementById('gallery-grid');
	const galleryEmpty = document.getElementById('gallery-empty');
	const rotationBar = document.getElementById('rotation-bar');
	
	let mediaItems = [];
	let currentPage = 0;
	const itemsPerPage = 9; // 9 items + 1 QR = 10 items (5 cols x 2 rows)
	const rotationDuration = 10000; // 10 seconds
	let rotationTimer;
	let progressInterval;
	let lastFetchTime = 0;
	let qrCodeUrl = '';

	function fetchGallery() {
		fetch(skateClubScreen.ajaxUrl + '?action=skate_get_screen_data')
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					const newMedia = data.data.media_gallery || [];
					
					if (data.data.qr_codes && data.data.qr_codes['upload_media']) {
						qrCodeUrl = data.data.qr_codes['upload_media'];
					}
					
					// Update stats
					document.getElementById('total-media').textContent = newMedia.length;
					document.getElementById('photo-count').textContent = newMedia.filter(m => m.media_type === 'photo').length;
					document.getElementById('video-count').textContent = newMedia.filter(m => m.media_type === 'video').length;
					
					// Update media list
					const previousCount = mediaItems.length;
					mediaItems = newMedia;
					
					// If first load (no children) or count changed, render immediately
					if (galleryGrid.children.length === 0 || mediaItems.length !== previousCount) {
						renderCurrentPage();
						
						// Start rotation if it hasn't started
						if (!progressInterval) {
							startRotation();
						}
					}
				}
			});
	}

	function renderCurrentPage() {
		const totalPages = Math.ceil(mediaItems.length / itemsPerPage) || 1;
		if (currentPage >= totalPages) currentPage = 0;
		
		document.getElementById('page-info').textContent = (currentPage + 1) + '/' + totalPages;

		const start = currentPage * itemsPerPage;
		const end = start + itemsPerPage;
		const pageItems = mediaItems.slice(start, end);
		
		let html = '';
		
		if (qrCodeUrl) {
			html += `<div class="media-item qr-media-item" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; border: 2px dashed #e2e8f0; animation-delay: 0s;">
				<img src="${qrCodeUrl}" alt="Scan to upload" style="width: 80%; height: 80%; object-fit: contain; border-radius: 0;">
				<span style="font-weight: 700; color: #64748b; font-size: 16px; margin-top: 10px;">Scan to Upload</span>
			</div>`;
		}

		if (mediaItems.length === 0 && !qrCodeUrl) {
			galleryEmpty.style.display = 'block';
			galleryGrid.innerHTML = '';
			document.getElementById('page-info').textContent = '0/0';
			return;
		}

		galleryEmpty.style.display = 'none';
		
		pageItems.forEach((item, index) => {
			let content = '';
			if (item.media_type === 'photo') {
				content = `<img src="${item.url}" alt="Gallery photo" loading="eager">`;
			} else {
				content = `
					<div class="video-indicator">
						<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
						Video
					</div>
					<video src="${item.url}" muted loop playsinline onmouseover="this.play()" onmouseout="this.pause()"></video>
				`;
			}
			
			// Add animation delay for staggered effect (index + 1 because QR code is 0)
			const delay = (index + 1) * 0.1;
			html += `<div class="media-item" style="animation-delay: ${delay}s" data-type="${item.media_type}" data-url="${item.url}">
				${content}
			</div>`;
		});
		
		galleryGrid.innerHTML = html;

		// Add click handlers
		document.querySelectorAll('.media-item:not(.qr-media-item)').forEach(item => {
			item.addEventListener('click', function() {
				openLightbox(this.dataset.type, this.dataset.url);
			});
		});
	}
		


	function startRotation() {
		if (rotationTimer) clearInterval(rotationTimer);
		if (progressInterval) clearInterval(progressInterval);
		
		let startTime = Date.now();
		
		// Progress bar animation
		progressInterval = setInterval(() => {
			if (lightbox.classList.contains('active')) return; // Pause when lightbox active
			
			const elapsed = Date.now() - startTime;
			const progress = (elapsed / rotationDuration) * 100;
			
			if (progress >= 100) {
				startTime = Date.now();
				rotatePage();
			} else {
				rotationBar.style.width = progress + '%';
			}
		}, 100);
	}
	
	function rotatePage() {
		if (mediaItems.length <= itemsPerPage) return; // No need to rotate if fits on one page
		
		currentPage++;
		renderCurrentPage();
	}

	function openLightbox(type, url) {
		if (type === 'photo') {
			lightboxContent.innerHTML = `<img src="${url}" alt="Full size">`;
		} else {
			lightboxContent.innerHTML = `<video src="${url}" controls autoplay></video>`;
		}
		lightbox.classList.add('active');
	}

	document.getElementById('lightbox-close').addEventListener('click', function() {
		lightbox.classList.remove('active');
		lightboxContent.innerHTML = '';
	});

	lightbox.addEventListener('click', function(e) {
		if (e.target === lightbox) {
			lightbox.classList.remove('active');
			lightboxContent.innerHTML = '';
		}
	});

	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && lightbox.classList.contains('active')) {
			lightbox.classList.remove('active');
			lightboxContent.innerHTML = '';
		}
	});

	// Initial fetch
	fetchGallery();
	
	// Check for new content every 30 seconds
	setInterval(fetchGallery, 30000);
});
</script>

<?php wp_footer(); ?>
</body>
</html>
