<?php
/**
 * Song Voting admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Display active session notice
Skate_Club_Admin_Menu::display_active_session_notice();

$active_session = Skate_Club_Session_Manager::get_active_session();
$session_id = $active_session ? $active_session->id : null;

$voting_list = array();
$rankings = array();

if ( $session_id ) {
	$voting_list = Skate_Club_Song_Voting::get_voting_list( $session_id );
	$rankings = Skate_Club_Song_Voting::get_rankings( $session_id );
}
?>

<div class="wrap skate-club-admin">
	<h1>Song Voting & Rankings</h1>

	<?php if ( ! $active_session ) : ?>
		<div class="notice notice-warning">
			<p>No active session. Please activate a session first.</p>
		</div>
	<?php else : ?>
		<p>Session: <strong><?php echo esc_html( $active_session->session_name ); ?></strong></p>

		<div class="card" style="max-width: 800px; margin: 20px 0;">
			<h2>Create Voting List</h2>
			<form id="create-voting-list-form">
				<p>Add songs (one per line in format: Song Title | Artist Name)</p>
				<textarea id="songs-input" rows="10" style="width: 100%; font-family: monospace;">Song Title 1 | Artist Name 1
Song Title 2 | Artist Name 2
Song Title 3 | Artist Name 3</textarea>
				<p class="submit">
					<button type="submit" class="button button-primary">Create Voting List</button>
				</p>
			</form>
		</div>

		<?php if ( ! empty( $rankings ) ) : ?>
			<h2>Current Rankings</h2>
			
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
					<select name="action" id="bulk-action-selector-top">
						<option value="-1">Bulk Actions</option>
						<option value="delete">Remove</option>
					</select>
					<input type="submit" id="doaction" class="button action" value="Apply">
				</div>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column">
							<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
							<input id="cb-select-all-1" type="checkbox">
						</td>
						<th style="width: 60px;">Rank</th>
						<th>Song Title</th>
						<th>Artist</th>
						<th>Total Points</th>
						<th>Vote Count</th>
						<th style="width: 100px;">Actions</th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php foreach ( $rankings as $song ) : ?>
						<tr data-song-id="<?php echo intval( $song->song_id ); ?>">
							<th scope="row" class="check-column">
								<label class="screen-reader-text" for="cb-select-<?php echo intval( $song->song_id ); ?>">Select Song</label>
								<input id="cb-select-<?php echo intval( $song->song_id ); ?>" type="checkbox" name="song[]" value="<?php echo intval( $song->song_id ); ?>">
							</th>
							<td><strong><?php echo intval( $song->rank ); ?></strong></td>
							<td><?php echo esc_html( $song->song_title ); ?></td>
							<td><?php echo esc_html( $song->artist ?: 'N/A' ); ?></td>
							<td><?php echo intval( $song->total_points ); ?></td>
							<td><?php echo intval( $song->vote_count ); ?></td>
							<td>
								<button type="button" class="button button-small remove-song">Remove</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( ! empty( $voting_list ) ) : ?>
			<div class="notice notice-info">
				<p>Voting list created. Waiting for votes...</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('#create-voting-list-form').on('submit', function(e) {
		e.preventDefault();

		const input = $('#songs-input').val();
		const lines = input.split('\n').filter(line => line.trim());
		const songs = [];

		lines.forEach(line => {
			const parts = line.split('|').map(s => s.trim());
			if (parts.length >= 1) {
				songs.push({
					title: parts[0],
					artist: parts[1] || ''
				});
			}
		});

		if (songs.length === 0) {
			return;
		}

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_create_voting_list',
			nonce: skateClubAdmin.nonce,
			session_id: <?php echo intval( $session_id ?: 0 ); ?>,
			songs: songs
		}, function(response) {
			if (response.success) {
				location.reload();
			}
		});
	});

	// Select All functionality
	$('#cb-select-all-1').on('click', function() {
		const isChecked = $(this).prop('checked');
		$('.check-column input[type="checkbox"]').prop('checked', isChecked);
	});

	// Bulk Action Apply
	$('#doaction').on('click', function(e) {
		e.preventDefault();
		
		const action = $('#bulk-action-selector-top').val();
		if (action !== 'delete') {
			return;
		}

		const selected = [];
		$('tbody .check-column input[type="checkbox"]:checked').each(function() {
			selected.push($(this).val());
		});

		if (selected.length === 0) {
			alert('Please select items to remove.');
			return;
		}

		if (!confirm('Are you sure you want to remove ' + selected.length + ' songs from the voting list?')) {
			return;
		}

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_bulk_delete_voting_songs',
			nonce: skateClubAdmin.nonce,
			song_ids: selected
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert('Error: ' + (response.data || 'Unknown error'));
			}
		});
	});

	// Remove song from voting list
	$(document).on('click', '.remove-song', function() {
		if (!confirm('Remove this song from the voting list?')) return;

		const songId = $(this).closest('tr').data('song-id');

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_remove_voting_song',
			nonce: skateClubAdmin.nonce,
			song_id: songId
		}, function(response) {
			if (response.success) {
				location.reload();
			}
		});
	});
});
</script>
