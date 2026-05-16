<?php
/**
 * Song Voting Form Template.
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

$voting_list = Skate_Club_Song_Voting::get_voting_list( $session_id );
$nonce = wp_create_nonce( 'skate_vote_songs_' . $session_id );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Vote Songs - <?php echo esc_html( $session->session_name ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="skate-form-page">

<div class="form-container">
	<h1>Vote for Songs</h1>
	<p>Rank your favorite songs (1 = favorite)</p>

	<?php if ( empty( $voting_list ) ) : ?>
		<p>No songs available for voting yet.</p>
	<?php else : ?>
		<form id="song-voting-form" class="skate-form">
			<h3>Rank the Songs</h3>
			<p><small>Select a rank for each song (leave blank to skip)</small></p>

			<div class="songs-scroll-container">
				<?php foreach ( $voting_list as $song ) : ?>
					<div class="song-rank-item">
						<div class="song-info">
							<strong><?php echo esc_html( $song->song_title ); ?></strong>
							<?php if ( $song->artist ) : ?>
								<span class="artist">- <?php echo esc_html( $song->artist ); ?></span>
							<?php endif; ?>
						</div>
						<select name="rank[<?php echo esc_attr( $song->id ); ?>]" class="rank-select">
							<option value="">0</option>
							<?php for ( $i = 1; $i <= count( $voting_list ); $i++ ) : ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="form-group consent-group">
				<label for="user_consent">
					<input type="checkbox" id="user_consent" name="user_consent" required>
					<span>I agree to the <a href="/terms-and-privacy-policy/" target="_blank">terms and privacy policy</a></span>
				</label>
			</div>

			<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
			<input type="hidden" id="browser_fingerprint" name="browser_fingerprint" value="">

			<button type="submit" class="submit-btn" style="z-index: 10;">Submit Vote</button>
		</form>

		<div id="form-message" style="display: none;"></div>
	<?php endif; ?>
</div>

<script>
// Prevent theme/plugin conflicts by overriding handleFixBottom with safe version
window.handleFixBottom = function() {
	// Safe wrapper to prevent theme/plugin errors on form pages
	try {
		const elements = document.querySelectorAll('.fix-bottom, [data-fix-bottom]');
		elements.forEach(function(el) {
			if (el && el.classList) {
				// Element exists, safe to manipulate
			}
		});
	} catch(e) {
		// Silently catch errors
	}
};

jQuery(document).ready(function($) {
	// Generate browser fingerprint
	if (typeof generateFingerprint === 'function') {
		$('#browser_fingerprint').val(generateFingerprint());
	}

	// Dynamic Rank Availability
	function updateRankAvailability() {
		const selectedValues = [];
		$('.rank-select').each(function() {
			const val = $(this).val();
			if (val) selectedValues.push(val);
		});

		$('.rank-select').each(function() {
			const currentSelect = $(this);
			const currentVal = currentSelect.val();

			currentSelect.find('option').each(function() {
				const optVal = $(this).val();
				if (optVal === "") return;

				// Disable if selected elsewhere (in selectedValues AND not current value)
				if (selectedValues.includes(optVal) && optVal !== currentVal) {
					$(this).prop('disabled', true);
				} else {
					$(this).prop('disabled', false);
				}
			});
		});
	}

	// Bind change event and run initially
	$('.rank-select').on('change', updateRankAvailability);
	updateRankAvailability();

	$('#song-voting-form').on('submit', function(e) {
		e.preventDefault();

		const btn = $(this).find('.submit-btn');
		btn.prop('disabled', true).text('Submitting...');

		const ranks = {};
		$('.rank-select').each(function() {
			const songId = $(this).attr('name').match(/\[(\d+)\]/)[1];
			const rank = $(this).val();
			if (rank) {
				ranks[songId] = rank;
			}
		});

		if (Object.keys(ranks).length === 0) {
			$('#form-message').show().html(
				'<div class="error">Please rank at least one song</div>'
			);
			btn.prop('disabled', false).text('Submit Vote');
			return;
		}

		// Validate duplicates (redundant check)
		const values = Object.values(ranks);
		const uniqueValues = new Set(values);
		if (values.length !== uniqueValues.size) {
			$('#form-message').show().html(
				'<div class="error">Duplicate ranks are not allowed. Please assign a unique rank to each song.</div>'
			);
			btn.prop('disabled', false).text('Submit Vote');
			return;
		}

		const formData = {
			action: 'skate_submit_vote',
			browser_fingerprint: $('#browser_fingerprint').val(),
			ranks: ranks,
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
				setTimeout(function() {
					// Try to close window - if it fails, show message
					window.close();
					// If window didn't close (private browsing), show message
					setTimeout(function() {
						if (!window.closed) {
							$('#form-message').html('<div class="success">Vote submitted successfully! You can now close this tab.</div>');
						}
					}, 100);
				}, 2000);
			} else {
				btn.prop('disabled', false).text('Submit Vote');
			}
		});
	});
});
</script>

<?php wp_footer(); ?>
</body>
</html>
