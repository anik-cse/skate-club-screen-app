<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Spinner Wheel - Full View</title>
	<?php wp_head(); ?>
	<style>
		body.fullview-page {
			margin: 0;
			padding: 20px;
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
			max-width: 1000px;
			margin: 0 auto;
			text-align: center;
			display: flex;
			flex-direction: column;
			min-height: 0;
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
			font-size: 2.5em;
			color: #1e293b;
		}

		.back-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 12px 24px;
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

		#spinner-wheel-wrapper {
			position: relative;
			/* Force square that fits within width AND height limits */
			width: min(500px, 70vmin);
			height: min(500px, 70vmin);
			margin: 20px auto;
			flex-shrink: 0;
		}

		#spinner-canvas {
			width: 100%;
			height: 100%;
			cursor: pointer;
			border-radius: 50%;
			box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
		}

		#spinner-arrow {
			position: absolute;
			width: 0;
			height: 0;
			z-index: 999;
			filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
		}

		#spinner-arrow.arrow-top {
			top: -5px;
			left: 50%;
			transform: translateX(-50%);
			border-left: 20px solid transparent;
			border-right: 20px solid transparent;
			border-top: 40px solid #ec4899;
		}

		#spinner-arrow.arrow-right {
			right: -5px;
			top: 50%;
			transform: translateY(-50%);
			border-top: 20px solid transparent;
			border-bottom: 20px solid transparent;
			border-right: 40px solid #ec4899;
		}

		#spinner-arrow.arrow-bottom {
			bottom: -5px;
			left: 50%;
			transform: translateX(-50%);
			border-left: 20px solid transparent;
			border-right: 20px solid transparent;
			border-bottom: 40px solid #ec4899;
		}

		#spinner-arrow.arrow-left {
			left: -5px;
			top: 50%;
			transform: translateY(-50%);
			border-top: 20px solid transparent;
			border-bottom: 20px solid transparent;
			border-left: 40px solid #ec4899;
		}

		.spinner-info {
			margin-top: 10px;
			flex-shrink: 0;
		}

		.participant-count {
			font-size: 1.3em;
			color: #64748b;
			margin-bottom: 20px;
		}

		.participant-count span {
			background: linear-gradient(135deg, #3b82f6, #8b5cf6);
			color: white;
			padding: 6px 16px;
			border-radius: 25px;
			font-weight: 700;
		}

		#winner-display {
			display: none;
			padding: 30px;
			background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1));
			border: 2px solid #10b981;
			border-radius: 16px;
			margin-top: 30px;
			overflow-wrap: break-word;
			word-wrap: break-word;
		}

		#winner-display h2 {
			margin: 0 0 15px;
			font-size: 1.8em;
			color: #1e293b;
			text-transform: uppercase;
			letter-spacing: 0.1em;
		}

		#winner-name {
			font-size: 2.5em;
			font-weight: 700;
			color: #10b981;
			margin: 0;
			overflow-wrap: break-word;
			word-wrap: break-word;
			max-width: 100%;
		}

		/* Fireworks Canvas */
		#fireworks-canvas {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			pointer-events: none;
			z-index: 9999;
		}

		.celebration-emoji {
			position: fixed;
			font-size: 60px;
			pointer-events: none;
			z-index: 10000;
			animation: floatUp 3s ease-out forwards;
		}

		@keyframes floatUp {
			0% {
				transform: translateY(0) rotate(0deg);
				opacity: 1;
			}
			100% {
				transform: translateY(-300px) rotate(360deg);
				opacity: 0;
			}
		}

		@media (max-width: 600px) {
			#spinner-wheel-wrapper {
				width: 300px;
				height: 300px;
			}
		}
	</style>
</head>
<body class="fullview-page">

<canvas id="fireworks-canvas"></canvas>

<div class="fullview-container">
	<div class="fullview-header">
		<h1>Spinner Wheel</h1>
		<a href="<?php echo esc_url( home_url( '/skate-club-screen-display/' ) ); ?>" class="back-link">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			Back to Display
		</a>
	</div>

	<div id="spinner-wheel-wrapper">
		<?php
		$settings = get_option( 'skate_club_settings', array() );
		$arrow_position = ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top';
		?>
		<div id="spinner-arrow" class="arrow-<?php echo esc_attr( $arrow_position ); ?>"></div>
		<canvas id="spinner-canvas" width="500" height="500"></canvas>
	</div>

	<div class="spinner-info">
		<p class="participant-count">Participants: <span id="spinner-count">0</span></p>
		<div id="winner-display">
			<h2>Winner!</h2>
			<p id="winner-name"></p>
		</div>
	</div>
</div>

<script>
var skateClubScreen = {
	ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
	spinnerArrowPosition: '<?php echo esc_js( $arrow_position ); ?>'
};

document.addEventListener('DOMContentLoaded', function() {
	let spinnerEntries = [];
	let spinnerAngle = 0;
	let isSpinning = false;
	let animationFrameId = null;
	let arrowPosition = skateClubScreen.spinnerArrowPosition || 'top';

	let spinStartTime = 0;
	let spinStartAngle = 0;
	let spinTargetAngle = 0;
	let spinDuration = 5000;
	let selectedWinnerIndex = -1;

	const canvas = document.getElementById('spinner-canvas');
	const ctx = canvas.getContext('2d');

	// Fireworks
	const fireworksCanvas = document.getElementById('fireworks-canvas');
	const fCtx = fireworksCanvas.getContext('2d');
	let particles = [];
	let fireworksActive = false;

	function resizeFireworks() {
		fireworksCanvas.width = window.innerWidth;
		fireworksCanvas.height = window.innerHeight;
	}
	resizeFireworks();
	window.addEventListener('resize', resizeFireworks);

	function Particle(x, y, color) {
		this.x = x;
		this.y = y;
		this.color = color;
		this.velocity = {
			x: (Math.random() - 0.5) * 10,
			y: (Math.random() - 0.5) * 10
		};
		this.alpha = 1;
		this.decay = Math.random() * 0.015 + 0.005;
		this.size = Math.random() * 4 + 2;
	}

	Particle.prototype.update = function() {
		this.velocity.y += 0.1;
		this.x += this.velocity.x;
		this.y += this.velocity.y;
		this.alpha -= this.decay;
	};

	Particle.prototype.draw = function() {
		fCtx.save();
		fCtx.globalAlpha = this.alpha;
		fCtx.fillStyle = this.color;
		fCtx.beginPath();
		fCtx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
		fCtx.fill();
		fCtx.restore();
	};

	function createFirework(x, y) {
		const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#ef4444'];
		for (let i = 0; i < 80; i++) {
			particles.push(new Particle(x, y, colors[Math.floor(Math.random() * colors.length)]));
		}
	}

	function animateFireworks() {
		if (!fireworksActive) return;

		fCtx.clearRect(0, 0, fireworksCanvas.width, fireworksCanvas.height);

		particles = particles.filter(p => p.alpha > 0);
		particles.forEach(p => {
			p.update();
			p.draw();
		});

		if (particles.length > 0) {
			requestAnimationFrame(animateFireworks);
		} else {
			fireworksActive = false;
			fCtx.clearRect(0, 0, fireworksCanvas.width, fireworksCanvas.height);
		}
	}

	function triggerFireworks() {
		fireworksActive = true;
		const positions = [
			{ x: fireworksCanvas.width * 0.25, y: fireworksCanvas.height * 0.4 },
			{ x: fireworksCanvas.width * 0.5, y: fireworksCanvas.height * 0.3 },
			{ x: fireworksCanvas.width * 0.75, y: fireworksCanvas.height * 0.4 },
			{ x: fireworksCanvas.width * 0.3, y: fireworksCanvas.height * 0.5 },
			{ x: fireworksCanvas.width * 0.7, y: fireworksCanvas.height * 0.5 },
		];

		positions.forEach((pos, i) => {
			setTimeout(() => createFirework(pos.x, pos.y), i * 200);
		});

		animateFireworks();
	}

	function easeOutQuint(t) {
		return 1 - Math.pow(1 - t, 5);
	}

	function getArrowAngle() {
		switch(arrowPosition) {
			case 'right': return 0;
			case 'bottom': return Math.PI / 2;
			case 'left': return Math.PI;
			case 'top':
			default: return Math.PI * 1.5;
		}
	}

	function fetchData() {
		fetch(skateClubScreen.ajaxUrl + '?action=skate_get_screen_data')
			.then(response => response.json())
			.then(data => {
				if (data.success && data.data.spinner) {
					spinnerEntries = data.data.spinner.entries || [];
					document.getElementById('spinner-count').textContent = spinnerEntries.length;
					if (spinnerEntries.length > 0 && !isSpinning) {
						drawSpinnerWheel(false);
					}
				}
			});
	}

	function drawSpinnerWheel(animate) {
		if (spinnerEntries.length === 0) return;

		const size = 500;
		const centerX = size / 2;
		const centerY = size / 2;
		const radius = centerX - 20;

		ctx.clearRect(0, 0, size, size);

		const sliceAngle = (2 * Math.PI) / spinnerEntries.length;
		const colors = [
			'#3b82f6', '#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#06b6d4', '#6366f1', '#14b8a6',
			'#f43f5e', '#84cc16', '#0ea5e9', '#a855f7', '#eab308', '#22c55e', '#e11d48', '#0891b2'
		];

		// Outer ring
		ctx.beginPath();
		ctx.arc(centerX, centerY, radius + 5, 0, 2 * Math.PI);
		ctx.strokeStyle = '#3b82f6';
		ctx.lineWidth = 4;
		ctx.stroke();

		// Slices
		spinnerEntries.forEach(function(entry, index) {
			const startAngle = spinnerAngle + (index * sliceAngle);
			const endAngle = startAngle + sliceAngle;
			const baseColor = entry.color || colors[index % colors.length];

			ctx.beginPath();
			ctx.moveTo(centerX, centerY);
			ctx.arc(centerX, centerY, radius, startAngle, endAngle);
			ctx.closePath();
			ctx.fillStyle = baseColor;
			ctx.fill();
			ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
			ctx.lineWidth = 2;
			ctx.stroke();

			// Text
			ctx.save();
			ctx.translate(centerX, centerY);
			ctx.rotate(startAngle + sliceAngle / 2);
			ctx.textAlign = 'center';
			ctx.textBaseline = 'middle';
			ctx.fillStyle = '#fff';
			ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
			ctx.shadowBlur = 3;
			ctx.font = 'bold 16px Arial, sans-serif';

			let displayText = entry.participant_name;
			const maxWidth = radius * 0.6;
			while (ctx.measureText(displayText).width > maxWidth && displayText.length > 3) {
				displayText = displayText.slice(0, -1);
			}
			if (displayText !== entry.participant_name) displayText += '...';

			ctx.fillText(displayText, radius * 0.6, 0);
			ctx.restore();
		});

		// Center circle
		ctx.beginPath();
		ctx.arc(centerX, centerY, 40, 0, 2 * Math.PI);
		ctx.fillStyle = '#ffffff';
		ctx.fill();
		ctx.strokeStyle = '#3b82f6';
		ctx.lineWidth = 3;
		ctx.stroke();

		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';
		ctx.fillStyle = '#3b82f6';
		ctx.font = 'bold 18px Arial';
		ctx.fillText('SPIN', centerX, centerY);

		// Animation
		if (isSpinning && animate !== false) {
			const now = performance.now();
			const elapsed = now - spinStartTime;
			const progress = Math.min(elapsed / spinDuration, 1);
			const eased = easeOutQuint(progress);

			spinnerAngle = spinStartAngle + (spinTargetAngle - spinStartAngle) * eased;

			if (progress >= 1) {
				spinnerAngle = spinTargetAngle;
				isSpinning = false;
				animationFrameId = null;
				drawSpinnerWheel(false);
				showWinner();
			} else {
				animationFrameId = requestAnimationFrame(function() {
					drawSpinnerWheel(true);
				});
			}
		}
	}

	function spinWheel() {
		if (isSpinning || spinnerEntries.length === 0) return;

		if (animationFrameId) {
			cancelAnimationFrame(animationFrameId);
			animationFrameId = null;
		}

		document.getElementById('winner-display').style.display = 'none';

		selectedWinnerIndex = Math.floor(Math.random() * spinnerEntries.length);

		const numEntries = spinnerEntries.length;
		const sliceAngle = (2 * Math.PI) / numEntries;
		const arrowAngle = getArrowAngle();

		let stopAngle = arrowAngle - (selectedWinnerIndex * sliceAngle) - (sliceAngle / 2);
		stopAngle += (Math.random() - 0.5) * sliceAngle * 0.4;
		stopAngle = ((stopAngle % (2 * Math.PI)) + (2 * Math.PI)) % (2 * Math.PI);

		const minRotations = 5;
		const extraRotations = Math.floor(Math.random() * 3);
		const totalRotations = (minRotations + extraRotations) * 2 * Math.PI;

		spinStartAngle = spinnerAngle;
		const normalizedStart = ((spinnerAngle % (2 * Math.PI)) + (2 * Math.PI)) % (2 * Math.PI);
		let angleDiff = stopAngle - normalizedStart;
		if (angleDiff < 0) angleDiff += 2 * Math.PI;

		spinTargetAngle = spinnerAngle + totalRotations + angleDiff;
		spinStartTime = performance.now();
		spinDuration = 4000 + Math.random() * 1500;
		isSpinning = true;

		drawSpinnerWheel(true);
	}

	function triggerCelebrationEmojis() {
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
	}

	function showWinner() {
		const winner = spinnerEntries[selectedWinnerIndex];
		document.getElementById('winner-name').textContent = winner.participant_name;
		document.getElementById('winner-display').style.display = 'block';
		triggerFireworks();
		triggerCelebrationEmojis();
	}

	canvas.addEventListener('click', spinWheel);

	fetchData();
	setInterval(fetchData, 10000);
});
</script>

<?php wp_footer(); ?>
</body>
</html>
