<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Songs Queue - Full View</title>
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
			max-width: 1600px;
			margin: 0 auto;
			display: flex;
			flex-direction: column;
			min-height: 0;
			width: 100%;
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

		.stats-bar {
			display: flex;
			gap: 30px;
			margin-bottom: 15px;
			padding: 15px;
			background: white;
			border-radius: 12px;
			border: 1px solid #e2e8f0;
			flex-shrink: 0;
		}

		.stat-item {
			text-align: center;
		}

		.stat-value {
			font-size: 2em;
			font-weight: 700;
			color: #3b82f6;
		}

		.stat-label {
			color: #64748b;
			font-size: 0.9em;
		}

		.songs-columns-container {
			flex: 1;
			display: grid;
			grid-auto-flow: column;
			grid-template-rows: repeat(10, 1fr); /* Exactly 10 items per column */
			grid-auto-columns: minmax(350px, 450px);
			gap: 20px;
			min-height: 0;
			overflow-y: hidden; /* No vertical scroll */
			overflow-x: auto; /* Allow horizontal expansion */
			min-height: 0;
			overflow-y: hidden; /* No vertical scroll */
			overflow-x: auto; /* Allow horizontal expansion */
			padding: 5px; /* Compact padding */
			align-content: start;
		}

		/* Hide scrollbars but keep functionality */
		.songs-columns-container::-webkit-scrollbar {
			width: 0;
			height: 0;
			display: none;
		}

		.song-item {
			display: flex;
			align-items: center;
			gap: 15px;
			padding: 10px 20px;
			background: white;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
			transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
			height: 100%;
			box-sizing: border-box;
		}

		.song-item:hover {
			transform: scale(1.02);
			box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
			border-color: #3b82f6;
			z-index: 10;
		}

		.song-rank {
			width: 40px;
			height: 40px;
			background: #f1f5f9;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 700;
			font-size: 1em;
			color: #64748b;
			flex-shrink: 0;
			/* Removed shadow for cleaner look */
		}

		.song-rank.gold {
			background: #fee2e2;
			color: #ef4444;
		}

		.song-rank.silver {
			background: #ffedd5;
			color: #f97316;
		}

		.song-rank.bronze {
			background: #fef9c3;
			color: #eab308;
		}

	</style>
</head>
<body class="fullview-page">

<div class="fullview-container">
	<div class="fullview-header">
		<h1>Song Ranking</h1>
		<a href="<?php echo esc_url( home_url( '/skate-club-screen-display/' ) ); ?>" class="back-link">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			Back to Display
		</a>
	</div>

	<div class="stats-bar">
		<div class="stat-item">
			<div class="stat-value" id="total-songs">0</div>
			<div class="stat-label">Total Songs</div>
		</div>
		<div class="stat-item">
			<div class="stat-value" id="total-votes">0</div>
			<div class="stat-label">Total Votes</div>
		</div>
	</div>

	<div id="songs-list" class="songs-columns-container">
		<div class="empty-state">Loading songs...</div>
	</div>
</div>

<script>
var skateClubScreen = {
	ajaxUrl: '<?php echo admin_url( 'admin-ajax.php' ); ?>'
};

document.addEventListener('DOMContentLoaded', function() {
	function fetchSongs() {
		fetch(skateClubScreen.ajaxUrl + '?action=skate_get_screen_data')
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					renderSongs(data.data);
				}
			});
	}

	function renderSongs(data) {
		const container = document.getElementById('songs-list');
		const rankings = data.song_rankings || [];
		const requests = (data.song_requests && data.song_requests.recent) || [];
		const songs = rankings.length > 0 ? rankings : requests;

		document.getElementById('total-songs').textContent = songs.length;

		let totalVotes = 0;
		rankings.forEach(song => totalVotes += parseInt(song.vote_count || 0));
		document.getElementById('total-votes').textContent = totalVotes;

		if (songs.length === 0) {
			container.innerHTML = '<div class="empty-state">No songs requested yet. Be the first!</div>';
			return;
		}

        // Simpler rendering: Flat list, CSS Grid handles layout
		container.innerHTML = songs.map((song, index) => {
			const rank = index + 1;
			let rankClass = '';
			if (rank === 1) rankClass = 'gold';
			else if (rank === 2) rankClass = 'silver';
			else if (rank === 3) rankClass = 'bronze';

			const title = escapeHtml(song.song_title || song.song_name || 'Untitled');
			const artist = escapeHtml(song.artist || '');

			return `
				<div class="song-item">
					<div class="song-rank ${rankClass}">${rank}</div>
					<div class="song-details">
						<div class="song-title">${title}</div>
						${artist ? `<div class="song-artist">${artist}</div>` : ''}
					</div>
				</div>
			`;
		}).join('');
	}

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

	fetchSongs();
	setInterval(fetchSongs, 10000);
});
</script>

<?php wp_footer(); ?>
</body>
</html>
