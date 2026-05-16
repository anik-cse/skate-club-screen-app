<?php
/**
 * Reporting admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Display active session notice
Skate_Club_Admin_Menu::display_active_session_notice();

// Get all sessions for dropdown
$all_sessions = Skate_Club_Session_Manager::get_sessions( array( 'limit' => 100 ) );

// Get selected session
$selected_session_id = ! empty( $_GET['session_id'] ) ? intval( $_GET['session_id'] ) : null;

// If no session selected but sessions exist, select the most recent
if ( ! $selected_session_id && ! empty( $all_sessions ) ) {
	$selected_session_id = $all_sessions[0]->id;
}

$session_data = array();
if ( $selected_session_id ) {
	$session = Skate_Club_Session_Manager::get_session( $selected_session_id );

	// Get song requests
	$song_requests = Skate_Club_Song_Request::get_requests( $selected_session_id, array( 'limit' => 500 ) );

	// Get raffle entries
	$raffle_entries = Skate_Club_Raffle_Draw::get_entries( $selected_session_id, array( 'limit' => 500 ) );

	// Get session stats
	$stats = Skate_Club_Session_Manager::get_session_stats( $selected_session_id );

	$session_data = array(
		'session'         => $session,
		'song_requests'   => $song_requests,
		'raffle_entries'  => $raffle_entries,
		'stats'           => $stats,
	);
}
?>

<div class="wrap skate-club-admin">
	<h1>Report</h1>

	<?php if ( empty( $all_sessions ) ) : ?>
		<div class="notice notice-warning">
			<p>No sessions found. Please create a session first.</p>
		</div>
	<?php else : ?>
		<!-- Session Selector and Export -->
		<div style="display: flex; gap: 20px; margin: 20px 0;">
			<div class="card" style="flex: 1;">
				<h2>Select Session</h2>
				<form method="get" action="">
					<input type="hidden" name="page" value="skate-club-reporting">
					<table class="form-table">
						<tr>
							<th><label for="session_id">Session</label></th>
							<td>
								<select id="session_id" name="session_id" class="regular-text" onchange="this.form.submit()">
									<?php foreach ( $all_sessions as $session ) : ?>
										<option value="<?php echo intval( $session->id ); ?>" <?php selected( $selected_session_id, $session->id ); ?>>
											<?php echo esc_html( $session->session_name ); ?>
											(<?php echo esc_html( date( 'M j, Y g:i A', strtotime( $session->session_date ) ) ); ?>)
											- <?php echo esc_html( ucfirst( $session->status ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
				</form>
			</div>

			<?php if ( $selected_session_id && ! empty( $session_data['session'] ) ) : ?>
			<div class="card" style="flex: 1;">
				<h2 style="margin-bottom: 10px;">Session Overview</h2>
				<div style="font-size: 13px; line-height: 1.8;">
					<div style="margin-bottom: 8px;">
						<strong style="display: inline-block; width: 120px;">Session:</strong>
						<?php echo esc_html( $session_data['session']->session_name ); ?>
						<span style="margin-left: 10px; padding: 2px 8px; background: <?php echo $session_data['session']->status === 'active' ? '#d4edda' : '#f8d7da'; ?>; border-radius: 3px; font-size: 11px;">
							<?php echo esc_html( ucfirst( $session_data['session']->status ) ); ?>
						</span>
					</div>
					<div style="margin-bottom: 8px;">
						<strong style="display: inline-block; width: 120px;">Date & Time:</strong>
						<?php echo esc_html( date( 'M j, Y g:i A', strtotime( $session_data['session']->session_date ) ) ); ?>
						<?php if ( ! empty( $session_data['session']->settings['end_time'] ) ) : ?>
							- <?php echo esc_html( date( 'g:i A', strtotime( $session_data['session']->settings['end_time'] ) ) ); ?>
						<?php endif; ?>
					</div>
					<div style="margin-bottom: 0;">
						<strong style="display: inline-block; width: 120px;">Activity:</strong>
						<span style="margin-right: 12px;">🎵 <?php echo intval( $session_data['stats']['song_requests'] ); ?> Songs</span>
						<span style="margin-right: 12px;">👍 <?php echo intval( $session_data['stats']['votes'] ); ?> Votes</span>
						<span style="margin-right: 12px;">📸 <?php echo intval( $session_data['stats']['media_uploads'] ); ?> Media</span>
						<span>🎫 <?php echo intval( $session_data['stats']['raffle_entries'] ); ?> Raffle</span>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<?php if ( $selected_session_id && ! empty( $session_data['session'] ) ) : ?>
			<!-- User Data (All Participants) -->
			<div class="card" style="margin: 20px 0; max-width: 100%; overflow-x: auto;">
				<h2>User Data - All Participants</h2>

				<?php
				// Consolidate all unique users from all modules
				$users = array();

				// Add users from song requests
				foreach ( $session_data['song_requests'] as $request ) {
					$key = strtolower( trim( $request->email ) );
					if ( ! isset( $users[ $key ] ) ) {
						$users[ $key ] = array(
							'first_name'    => $request->first_name,
							'last_name'     => $request->last_name,
							'email'         => $request->email,
							'phone'         => $request->phone,
							'song_request'  => true,
							'raffle_entry'  => false,
							'first_seen'    => $request->submitted_at,
						);
					} else {
						$users[ $key ]['song_request'] = true;
						if ( strtotime( $request->submitted_at ) < strtotime( $users[ $key ]['first_seen'] ) ) {
							$users[ $key ]['first_seen'] = $request->submitted_at;
						}
					}
				}

				// Add users from raffle entries
				foreach ( $session_data['raffle_entries'] as $entry ) {
					$key = strtolower( trim( $entry->email ) );
					if ( ! isset( $users[ $key ] ) ) {
						$users[ $key ] = array(
							'first_name'    => $entry->first_name,
							'last_name'     => $entry->last_name,
							'email'         => $entry->email,
							'phone'         => $entry->phone,
							'song_request'  => false,
							'raffle_entry'  => true,
							'first_seen'    => $entry->submitted_at,
						);
					} else {
						$users[ $key ]['raffle_entry'] = true;
						if ( strtotime( $entry->submitted_at ) < strtotime( $users[ $key ]['first_seen'] ) ) {
							$users[ $key ]['first_seen'] = $entry->submitted_at;
						}
					}
				}

				// Sort by first seen (earliest first)
				uasort( $users, function( $a, $b ) {
					return strtotime( $a['first_seen'] ) - strtotime( $b['first_seen'] );
				});
				?>

				<?php if ( ! empty( $users ) ) : ?>
					<p style="margin-bottom: 15px;">
						<strong>Total Unique Participants:</strong> <?php echo count( $users ); ?>
						<button type="button" class="button" id="export-user-data" style="margin-left: 15px;">
							<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Export User Data
						</button>
					</p>

					<table class="wp-list-table widefat fixed striped" style="width: 100%; table-layout: auto;">
						<thead>
							<tr>
								<th style="width: 50px;">#</th>
								<th style="min-width: 120px;">First Name</th>
								<th style="min-width: 120px;">Last Name</th>
								<th style="min-width: 200px;">Email</th>
								<th style="min-width: 130px;">Phone</th>
								<th style="width: 180px; cursor: pointer;" class="sortable" data-sort="participated">
									Participated In <span class="sort-arrow">⇅</span>
								</th>
								<th style="width: 150px; cursor: pointer;" class="sortable" data-sort="first-seen">
									First Seen <span class="sort-arrow">⇅</span>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$counter = 1;
							foreach ( $users as $user ) :
								$activities = array();
								$activity_count = 0;
								if ( $user['song_request'] ) {
									$activities[] = '<span style="background: #667eea; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">Song Request</span>';
									$activity_count++;
								}
								if ( $user['raffle_entry'] ) {
									$activities[] = '<span style="background: #764ba2; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">Raffle</span>';
									$activity_count++;
								}
							?>
								<tr data-participated="<?php echo $activity_count; ?>" data-first-seen="<?php echo strtotime( $user['first_seen'] ); ?>">
									<td><?php echo $counter++; ?></td>
									<td><?php echo esc_html( $user['first_name'] ); ?></td>
									<td><?php echo esc_html( $user['last_name'] ); ?></td>
									<td><?php echo esc_html( $user['email'] ); ?></td>
									<td><?php echo esc_html( $user['phone'] ); ?></td>
									<td><?php echo implode( ' ', $activities ); ?></td>
									<td><?php echo esc_html( date( 'M j, g:i A', strtotime( $user['first_seen'] ) ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p style="padding: 20px; text-align: center; color: #666;">No participants for this session.</p>
				<?php endif; ?>
			</div>

			<!-- Song Requests -->
			<div class="card" style="margin: 20px 0; max-width: 100%; overflow-x: auto;">
				<h2>Song Requests (<?php echo count( $session_data['song_requests'] ); ?>)</h2>

				<?php if ( ! empty( $session_data['song_requests'] ) ) : ?>
					<p style="margin-bottom: 15px;">
						<button type="button" class="button" id="export-song-requests">
							<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Export Song Requests
						</button>
					</p>

					<table class="wp-list-table widefat fixed striped" style="width: 100%; table-layout: auto;">
						<thead>
							<tr>
								<th style="width: 50px;">ID</th>
								<th style="min-width: 150px;">Song Name</th>
								<th style="min-width: 120px;">Artist</th>
								<th style="min-width: 120px;">First Name</th>
								<th style="min-width: 120px;">Last Name</th>
								<th style="min-width: 200px;">Email</th>
								<th style="min-width: 130px;">Phone</th>
								<th style="width: 150px;">Submitted At</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $session_data['song_requests'] as $request ) : ?>
								<tr>
									<td><?php echo intval( $request->id ); ?></td>
									<td><strong><?php echo esc_html( $request->song_name ); ?></strong></td>
									<td><?php echo esc_html( ! empty( $request->artist ) ? $request->artist : '-' ); ?></td>
									<td><?php echo esc_html( $request->first_name ); ?></td>
									<td><?php echo esc_html( $request->last_name ); ?></td>
									<td><?php echo esc_html( $request->email ); ?></td>
									<td><?php echo esc_html( $request->phone ); ?></td>
									<td><?php echo esc_html( date( 'M j, g:i A', strtotime( $request->submitted_at ) ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p style="padding: 20px; text-align: center; color: #666;">No song requests for this session.</p>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	const sessionId = <?php echo intval( $selected_session_id ?: 0 ); ?>;

	// Export User Data
	$('#export-user-data').on('click', function() {
		window.location.href = skateClubAdmin.ajaxUrl +
			'?action=skate_export_user_data&session_id=' + sessionId +
			'&nonce=' + skateClubAdmin.nonce;
	});

	// Export Song Requests
	$('#export-song-requests').on('click', function() {
		window.location.href = skateClubAdmin.ajaxUrl +
			'?action=skate_export_song_requests&session_id=' + sessionId +
			'&nonce=' + skateClubAdmin.nonce;
	});

	// Table sorting functionality
	let currentSort = { column: null, direction: 'asc' };

	$('.sortable').on('click', function() {
		const $header = $(this);
		const sortType = $header.data('sort');
		const $table = $header.closest('table');
		const $tbody = $table.find('tbody');
		const $rows = $tbody.find('tr').toArray();

		// Determine sort direction
		if (currentSort.column === sortType) {
			currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
		} else {
			currentSort.direction = 'asc';
			currentSort.column = sortType;
		}

		// Sort rows
		$rows.sort(function(a, b) {
			let aVal = $(a).attr('data-' + sortType);
			let bVal = $(b).attr('data-' + sortType);

			// Convert to numbers for comparison
			aVal = parseFloat(aVal) || 0;
			bVal = parseFloat(bVal) || 0;

			if (currentSort.direction === 'asc') {
				return aVal - bVal;
			} else {
				return bVal - aVal;
			}
		});

		// Update row numbers and reattach
		$tbody.empty();
		$.each($rows, function(index, row) {
			$(row).find('td:first').text(index + 1);
			$tbody.append(row);
		});

		// Update sort arrows
		$('.sortable .sort-arrow').text('⇅');
		if (currentSort.direction === 'asc') {
			$header.find('.sort-arrow').text('↑');
		} else {
			$header.find('.sort-arrow').text('↓');
		}
	});
});
</script>
