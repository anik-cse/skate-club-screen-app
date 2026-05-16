<?php
/**
 * Template for when no active session is found.
 *
 * @package    Skate_Club_Screen
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>No Active Session - Skate Club</title>
	<?php wp_head(); ?>
	<style>
		body.skate-form-page {
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 100vh;
			background: radial-gradient(circle at top left, #f1f5f9, #e2e8f0);
			margin: 0;
			font-family: 'Inter', system-ui, -apple-system, sans-serif;
		}

		.no-session-card {
			text-align: center;
			padding: 50px 40px;
			background: rgba(255, 255, 255, 0.9);
			backdrop-filter: blur(16px);
			border-radius: 24px;
			box-shadow: 0 15px 50px rgba(59, 130, 246, 0.15), inset 0 2px 0 rgba(255, 255, 255, 1);
			max-width: 480px;
			width: 90%;
			border: 1px solid rgba(226, 232, 240, 0.8);
		}

		.no-session-icon {
			font-size: 80px;
			margin-bottom: 25px;
			animation: floating 3s ease-in-out infinite;
			display: inline-block;
			filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.1));
		}

		@keyframes floating {
			0%, 100% { transform: translateY(0px) rotate(-5deg); }
			50% { transform: translateY(-15px) rotate(5deg); }
		}

		.no-session-card h1 {
			font-size: 32px;
			font-weight: 800;
			color: #1e293b;
			margin-bottom: 15px;
			letter-spacing: -0.5px;
			background: linear-gradient(135deg, #3b82f6, #ec4899);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
		}

		.no-session-card p {
			color: #475569;
			font-size: 17px;
			line-height: 1.6;
			margin-bottom: 35px;
			font-weight: 500;
		}

		.refresh-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 12px;
			width: 100%;
			background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
			background-size: 200% auto;
			color: white;
			border: none;
			padding: 16px 24px;
			border-radius: 50px;
			font-size: 18px;
			font-weight: 700;
			cursor: pointer;
			transition: all 0.3s ease;
			text-decoration: none;
			box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
			animation: gradientShifting 4s ease infinite;
		}

		@keyframes gradientShifting {
			0% { background-position: 0% 50%; }
			50% { background-position: 100% 50%; }
			100% { background-position: 0% 50%; }
		}

		.refresh-btn:hover {
			transform: translateY(-3px) scale(1.02);
			box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
		}

		.refresh-btn svg {
			width: 24px;
			height: 24px;
			transition: transform 0.5s ease;
		}

		.refresh-btn:hover svg {
			transform: rotate(180deg);
		}
		
		#wpadminbar { display: none !important; } /* Hide admin bar on frontend form */
		html { margin-top: 0 !important; }
	</style>
</head>
<body class="skate-form-page">
	<div class="no-session-card">
		<div class="no-session-icon">⛸️</div>
		<h1>Hang Tight!</h1>
		<p>There is no active session right now. Grab a drink, lace up your skates, and wait for the event to roll off!</p>
		<button onclick="window.location.reload()" class="refresh-btn">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21.5 2v6h-6"></path>
				<path d="M21.34 15.57a10 10 0 1 1-.59-9.21l-3 3"></path>
			</svg>
			Refresh Page
		</button>
	</div>

<script>
	// Auto-refresh the page every 30 seconds to automatically log the user in when a session starts.
	setInterval(() => {
		window.location.reload();
	}, 15000);
</script>

<?php wp_footer(); ?>
</body>
</html>
