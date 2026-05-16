<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Raffle Draw - Full View</title>
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
			max-width: 800px;
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

		.raffle-stats {
			display: flex;
			justify-content: center;
			gap: 40px;
			margin: 15px 0;
			flex-shrink: 0;
		}

		.stat-card {
			background: white;
			padding: 30px 50px;
			border-radius: 16px;
			border: 1px solid #e2e8f0;
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
		}

		.stat-value {
			font-size: 3em;
			font-weight: 700;
			color: #3b82f6;
		}

		.stat-label {
			color: #64748b;
			font-size: 1.1em;
			margin-top: 5px;
		}

		#draw-btn {
			padding: 15px 50px;
			background: linear-gradient(135deg, #3b82f6, #8b5cf6);
			color: white;
			border: none;
			border-radius: 50px;
			font-size: 1.3em;
			font-weight: 700;
			cursor: pointer;
			text-transform: uppercase;
			letter-spacing: 0.1em;
			transition: all 0.3s ease;
			margin: 15px 0;
			flex-shrink: 0;
		}

		#draw-btn:hover {
			transform: translateY(-3px);
			box-shadow: 0 6px 25px rgba(59, 130, 246, 0.5);
		}

		#draw-btn:disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}

		#winner-card {
			display: none;
			background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1));
			border: 3px solid #10b981;
			border-radius: 20px;
			padding: 30px;
			margin-top: 15px;
			flex-shrink: 0;
		}

		#winner-card h2 {
			margin: 0 0 20px;
			font-size: 2em;
			color: #1e293b;
			text-transform: uppercase;
			letter-spacing: 0.15em;
		}

		#winner-name-display {
			font-size: 3.5em;
			font-weight: 700;
			color: #10b981;
			margin: 0;
		}

		#draw-animation {
			display: none;
			background: rgba(248, 250, 252, 0.98);
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 1000;
			align-items: center;
			justify-content: center;
			flex-direction: column;
		}

		.modal-glow {
			position: absolute;
			top: 50%;
			left: 50%;
			width: 600px;
			height: 600px;
			background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
			transform: translate(-50%, -50%);
			animation: modalGlow 3s ease-in-out infinite;
			pointer-events: none;
			z-index: 0;
		}

		@keyframes modalGlow {
			0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
			50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.8; }
		}

		@keyframes rafflePulse {
			0%, 100% { transform: scale(1); }
			50% { transform: scale(1.05); }
		}

		#name-display-container {
			background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
			padding: 60px 80px;
			border-radius: 30px;
			box-shadow: 0 4px 40px rgba(59, 130, 246, 0.2), inset 0 2px 20px rgba(59, 130, 246, 0.05);
			min-height: 180px;
			display: flex;
			align-items: center;
			justify-content: center;
			border: 2px solid rgba(59, 130, 246, 0.3);
			position: relative;
			z-index: 1;
			transition: all 0.3s ease;
		}

		#animated-name-display {
			font-size: 4em;
			font-weight: 800;
			color: #1e293b;
			text-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
		}

		#draw-progress {
			margin-top: 30px;
			font-size: 1.3em;
			color: #64748b;
			position: relative;
			z-index: 1;
		}

		#fireworks-canvas {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			pointer-events: none;
			z-index: 10000;
		}

		.celebration-emoji {
			position: fixed;
			font-size: 60px;
			pointer-events: none;
			z-index: 10001;
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
	</style>
</head>
<body class="fullview-page">

<div class="fullview-container">
	<div class="fullview-header">
		<h1>Raffle Draw</h1>
		<a href="<?php echo esc_url( home_url( '/skate-club-screen-display/' ) ); ?>" class="back-link">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			Back to Display
		</a>
	</div>

	<div class="raffle-stats">
		<div class="stat-card">
			<div class="stat-value" id="entry-count">0</div>
			<div class="stat-label">Total Entries</div>
		</div>
	</div>

	<button id="draw-btn">Draw Winner</button>

	<div id="winner-card">
		<h2>Winner!</h2>
		<p id="winner-name-display"></p>
	</div>
</div>

<canvas id="fireworks-canvas"></canvas>

<div id="draw-animation">
	<div class="modal-glow"></div>
	<div id="name-display-container">
		<div id="animated-name-display"></div>
	</div>
	<div id="draw-progress">Shuffling entries...</div>
</div>

<script>
var skateClubScreen = {
	ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>'
};

document.addEventListener('DOMContentLoaded', function() {
	let raffleEntries = [];
	let sessionId = null;

	// Fireworks setup
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

		// Additional random fireworks
		for (let i = 0; i < 3; i++) {
			setTimeout(() => {
				createFirework(
					100 + Math.random() * (fireworksCanvas.width - 200),
					100 + Math.random() * (fireworksCanvas.height * 0.5)
				);
			}, 1500 + i * 300);
		}

		animateFireworks();
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

	function fetchData() {
		fetch(skateClubScreen.ajaxUrl + '?action=skate_get_screen_data')
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					raffleEntries = data.data.raffle.entries || [];
					sessionId = data.data.session.id;
					if (data.data.security && data.data.security.draw_nonce) {
						window.skateRaffleNonce = data.data.security.draw_nonce;
					}
					document.getElementById('entry-count').textContent = data.data.raffle.entry_count;
					document.getElementById('draw-btn').disabled = raffleEntries.length === 0;

					if (data.data.raffle.winner) {
						document.getElementById('winner-name-display').textContent =
							data.data.raffle.winner.first_name + ' ' + data.data.raffle.winner.last_name;
						document.getElementById('winner-card').style.display = 'block';
					}
				}
			});
	}

	document.getElementById('draw-btn').addEventListener('click', function() {
		if (raffleEntries.length === 0) return;

		document.getElementById('draw-animation').style.display = 'flex';
		document.getElementById('draw-progress').textContent = 'Shuffling entries...';

		let currentIndex = 0;
		let intervalSpeed = 50;
		let iterations = 0;
		const maxIterations = 80;
		let animationInterval;

		function animateDraw() {
			document.getElementById('animated-name-display').textContent =
				raffleEntries[currentIndex].first_name + ' ' + raffleEntries[currentIndex].last_name;

			currentIndex = (currentIndex + 1) % raffleEntries.length;
			iterations++;

			if (iterations > maxIterations - 30) {
				intervalSpeed += 15;
				clearInterval(animationInterval);
				animationInterval = setInterval(animateDraw, intervalSpeed);
			}

			if (iterations >= maxIterations) {
				clearInterval(animationInterval);
				selectWinner();
			}

			const progress = Math.min(100, Math.floor((iterations / maxIterations) * 100));
			document.getElementById('draw-progress').textContent = progress + '% complete...';
		}

		animationInterval = setInterval(animateDraw, intervalSpeed);
	});

	function selectWinner() {
		fetch(skateClubScreen.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: 'action=skate_draw_raffle_winner&session_id=' + sessionId + '&nonce=' + (window.skateRaffleNonce || '')
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				const winner = data.data.winner;
				const nameDisplay = document.getElementById('animated-name-display');
				const container = document.getElementById('name-display-container');

				nameDisplay.textContent = winner.first_name + ' ' + winner.last_name;
				document.getElementById('draw-progress').innerHTML =
					'<span style="color: #10b981; font-size: 1.5em; font-weight: 700;">WINNER!</span>';

				// Add celebration effect
				container.style.animation = 'rafflePulse 0.6s ease-in-out 4';
				container.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(59, 130, 246, 0.1) 100%)';
				container.style.borderColor = '#10b981';
				container.style.boxShadow = '0 4px 40px rgba(16, 185, 129, 0.3), inset 0 2px 20px rgba(16, 185, 129, 0.1)';

				// Trigger fireworks and celebration emojis
				triggerFireworks();
				triggerCelebrationEmojis();

				setTimeout(function() {
					document.getElementById('draw-animation').style.display = 'none';
					document.getElementById('winner-name-display').textContent =
						winner.first_name + ' ' + winner.last_name;
					document.getElementById('winner-card').style.display = 'block';
					fetchData(); // Refresh data
				}, 4000);
			}
		});
	}

	fetchData();
	setInterval(fetchData, 10000);
});
</script>

<?php wp_footer(); ?>
</body>
</html>
