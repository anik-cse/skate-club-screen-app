<?php
/**
 * Session Management admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Get settings
$settings = get_option( 'skate_club_settings', array( 'default_session_duration' => 4 ) );
$default_duration = ! empty( $settings['default_session_duration'] ) ? intval( $settings['default_session_duration'] ) : 4;

// Get active session
$active_session = Skate_Club_Session_Manager::get_active_session();

// Generate QR codes for active session
$active_qr_codes = array();
$active_session_hash = '';
if ( $active_session ) {
	$active_qr_codes = Skate_Club_QR_Generator::generate_session_qr_codes( $active_session->id );
	$active_session_hash = Skate_Club_Security::generate_session_hash( $active_session->id );
}

// Pagination and sorting parameters
$per_page = 10;
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';
$order = isset( $_GET['order'] ) && in_array( strtolower( $_GET['order'] ), array( 'asc', 'desc' ) ) ? strtoupper( $_GET['order'] ) : 'DESC';

// Validate orderby column
$allowed_orderby = array( 'id', 'session_date', 'status' );
if ( ! in_array( $orderby, $allowed_orderby ) ) {
	$orderby = 'id';
}

// Get total count for pagination
global $wpdb;
$table_name = $wpdb->prefix . 'skate_sessions';
$total_sessions = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
$total_pages = ceil( $total_sessions / $per_page );

// Get sessions with pagination and sorting
$offset = ( $current_page - 1 ) * $per_page;
$all_sessions = Skate_Club_Session_Manager::get_sessions( array(
	'limit' => $per_page,
	'offset' => $offset,
	'orderby' => $orderby,
	'order' => $order
) );

?>

<div class="wrap skate-club-admin">
	<h1>Session Management</h1>

	<?php
	// Display admin notices
	if ( isset( $_GET['session-created'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Session created successfully!</strong></p></div>';
	}
	if ( isset( $_GET['session-activated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Session activated successfully!</strong></p></div>';
	}
	if ( isset( $_GET['session-closed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Session closed successfully!</strong></p></div>';
	}
	if ( isset( $_GET['session-error'] ) ) {
		$error_message = sanitize_text_field( $_GET['session-error'] );
		echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> ' . esc_html( $error_message ) . '</p></div>';
	}
	?>

	<?php if ( $active_session ) : ?>
		<div class="notice notice-success">
			<p><strong>Active Session:</strong> <?php echo esc_html( $active_session->session_name ); ?>
			(<?php echo esc_html( $active_session->session_date ); ?>)</p>
			<p>
				<a href="<?php echo esc_url( home_url( '/skate-club-screen-display/' ) ); ?>" target="_blank" class="button">
					View Screen Display
				</a>
				<button type="button" class="button" id="show-qr-codes">Show QR Codes</button>
				<button type="button" class="button button-secondary" data-session-id="<?php echo esc_attr( $active_session->id ); ?>" id="close-session">
					Close Session
				</button>
			</p>
		</div>

		<div id="qr-codes-container" style="display: none; margin: 20px 0;">
			<h2>QR Codes for Active Session</h2>
			<div class="qr-codes-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
				<div class="qr-code-item">
					<h3>Song Request</h3>
					<img src="<?php echo esc_attr( ! empty( $active_qr_codes['song_request'] ) ? $active_qr_codes['song_request'] : '' ); ?>" alt="Song Request QR Code" style="max-width: 220px;">
					<p>
						<a href="<?php echo esc_url( home_url( '/skate-club-submit-song-request/' ) ); ?>" target="_blank" class="button button-secondary">
							Open Form
						</a>
					</p>
				</div>
				<div class="qr-code-item">
					<h3>Vote Songs</h3>
					<img src="<?php echo esc_attr( ! empty( $active_qr_codes['vote_songs'] ) ? $active_qr_codes['vote_songs'] : '' ); ?>" alt="Vote Songs QR Code" style="max-width: 220px;">
					<p>
						<a href="<?php echo esc_url( home_url( '/skate-club-vote-songs/' ) ); ?>" target="_blank" class="button button-secondary">
							Open Form
						</a>
					</p>
				</div>
				<div class="qr-code-item">
					<h3>Upload Media</h3>
					<img src="<?php echo esc_attr( ! empty( $active_qr_codes['upload_media'] ) ? $active_qr_codes['upload_media'] : '' ); ?>" alt="Upload Media QR Code" style="max-width: 220px;">
					<p>
						<a href="<?php echo esc_url( home_url( '/skate-club-upload-media/' ) ); ?>" target="_blank" class="button button-secondary">
							Open Form
						</a>
					</p>
				</div>
				<div class="qr-code-item">
					<h3>Enter Raffle</h3>
					<img src="<?php echo esc_attr( ! empty( $active_qr_codes['enter_raffle'] ) ? $active_qr_codes['enter_raffle'] : '' ); ?>" alt="Enter Raffle QR Code" style="max-width: 220px;">
					<p>
						<a href="<?php echo esc_url( home_url( '/skate-club-enter-raffle/' ) ); ?>" target="_blank" class="button button-secondary">
							Open Form
						</a>
					</p>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="card" style="max-width: 600px; margin: 20px 0;">
		<h2>Create New Session</h2>
		<form id="create-session-form">
			<table class="form-table">
				<tr>
					<th><label for="session_name">Session Name</label></th>
					<td>
						<input type="text" id="session_name" name="session_name" class="regular-text" placeholder="Friday Night Skate" required>
					</td>
				</tr>
				<tr>
					<th><label for="start_time">Start Time</label></th>
					<td>
						<input type="datetime-local" id="start_time" name="start_time" required>
					</td>
				</tr>
				<tr>
					<th><label for="end_time">End Time</label></th>
					<td>
						<input type="datetime-local" id="end_time" name="end_time" required>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button button-primary">Create Session</button>
			</p>
		</form>
	</div>

	<h2>All Sessions</h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<?php
				// Get current page URL
				$current_url = remove_query_arg( array( 'orderby', 'order', 'paged' ) );
				?>
				<th>
					<?php
					$id_order = ( $orderby === 'id' && $order === 'ASC' ) ? 'DESC' : 'ASC';
					$id_url = add_query_arg( array( 'orderby' => 'id', 'order' => $id_order ), $current_url );
					?>
					<a href="<?php echo esc_url( $id_url ); ?>">
						ID<?php if ( $orderby === 'id' ) { echo ( $order === 'ASC' ) ? ' &#9650;' : ' &#9660;'; } ?>
					</a>
				</th>
				<th>Name</th>
				<th>
					<?php
					$date_order = ( $orderby === 'session_date' && $order === 'ASC' ) ? 'DESC' : 'ASC';
					$date_url = add_query_arg( array( 'orderby' => 'session_date', 'order' => $date_order ), $current_url );
					?>
					<a href="<?php echo esc_url( $date_url ); ?>">
						Date<?php if ( $orderby === 'session_date' ) { echo ( $order === 'ASC' ) ? ' &#9650;' : ' &#9660;'; } ?>
					</a>
				</th>
				<th>
					<?php
					$status_order = ( $orderby === 'status' && $order === 'ASC' ) ? 'DESC' : 'ASC';
					$status_url = add_query_arg( array( 'orderby' => 'status', 'order' => $status_order ), $current_url );
					?>
					<a href="<?php echo esc_url( $status_url ); ?>">
						Status<?php if ( $orderby === 'status' ) { echo ( $order === 'ASC' ) ? ' &#9650;' : ' &#9660;'; } ?>
					</a>
				</th>
				<th>Statistics</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $all_sessions ) ) : ?>
				<tr>
					<td colspan="6">No sessions found. Create your first session above.</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $all_sessions as $session ) :
					$stats = Skate_Club_Session_Manager::get_session_stats( $session->id );
				?>
					<tr>
						<td><?php echo esc_html( $session->id ); ?></td>
						<td><strong><?php echo esc_html( $session->session_name ); ?></strong></td>
						<td><?php echo esc_html( date( 'M j, Y g:i A', strtotime( $session->session_date ) ) ); ?></td>
						<td>
							<?php
							$status_class = array(
								'draft'  => 'notice-info',
								'active' => 'notice-success',
								'closed' => 'notice-warning',
							);
							?>
							<span class="notice <?php echo esc_attr( $status_class[ $session->status ] ); ?> inline" style="padding: 2px 8px;">
								<?php echo esc_html( ucfirst( $session->status ) ); ?>
							</span>
						</td>
						<td>
							<small>
								Requests: <?php echo intval( $stats['song_requests'] ); ?> |
								Votes: <?php echo intval( $stats['votes'] ); ?> |
								Media: <?php echo intval( $stats['media_uploads'] ); ?> |
								Raffle: <?php echo intval( $stats['raffle_entries'] ); ?>
							</small>
						</td>
						<td>
							<?php if ( $session->status === 'draft' ) : ?>
								<button type="button" class="button button-primary activate-session"
								        data-session-id="<?php echo esc_attr( $session->id ); ?>">
									Activate
								</button>
							<?php elseif ( $session->status === 'active' ) : ?>
								<button type="button" class="button button-secondary close-session"
								        data-session-id="<?php echo esc_attr( $session->id ); ?>">
									Close
								</button>
							<?php endif; ?>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'skate-club-reporting', 'session_id' => $session->id ), admin_url( 'admin.php' ) ) ); ?>"
							   class="button">
								View Report
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				$start_item = ( ( $current_page - 1 ) * $per_page ) + 1;
				$end_item = min( $current_page * $per_page, $total_sessions );
				$base_url = remove_query_arg( 'paged' );
				?>
				<span class="displaying-num"><?php echo esc_html( sprintf( '%d items', $total_sessions ) ); ?></span>
				<span class="pagination-links">
					<?php
					// First page link
					if ( $current_page > 1 ) {
						$first_url = add_query_arg( 'paged', 1, $base_url );
						echo '<a class="first-page button" href="' . esc_url( $first_url ) . '"><span aria-hidden="true">&laquo;</span></a> ';
					} else {
						echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span> ';
					}

					// Previous page link
					if ( $current_page > 1 ) {
						$prev_url = add_query_arg( 'paged', $current_page - 1, $base_url );
						echo '<a class="prev-page button" href="' . esc_url( $prev_url ) . '"><span aria-hidden="true">&lsaquo;</span></a> ';
					} else {
						echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span> ';
					}

					// Page numbers
					echo '<span class="paging-input">';
					echo '<span class="tablenav-paging-text">';
					echo esc_html( $current_page ) . ' of <span class="total-pages">' . esc_html( $total_pages ) . '</span>';
					echo '</span>';
					echo '</span> ';

					// Next page link
					if ( $current_page < $total_pages ) {
						$next_url = add_query_arg( 'paged', $current_page + 1, $base_url );
						echo '<a class="next-page button" href="' . esc_url( $next_url ) . '"><span aria-hidden="true">&rsaquo;</span></a> ';
					} else {
						echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span> ';
					}

					// Last page link
					if ( $current_page < $total_pages ) {
						$last_url = add_query_arg( 'paged', $total_pages, $base_url );
						echo '<a class="last-page button" href="' . esc_url( $last_url ) . '"><span aria-hidden="true">&raquo;</span></a>';
					} else {
						echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
					}
					?>
				</span>
			</div>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Set default start and end times
	const defaultDuration = <?php echo intval( $default_duration ); ?>;
	const adminPageUrl = '<?php echo esc_js( admin_url( 'admin.php?page=skate-club-sessions' ) ); ?>';
	const now = new Date();
	now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

	// Set start time to current date/time
	$('#start_time').val(now.toISOString().slice(0, 16));

	// Set end time to current date/time + default duration
	const endTime = new Date(now);
	endTime.setHours(endTime.getHours() + defaultDuration);
	$('#end_time').val(endTime.toISOString().slice(0, 16));

	// Update end time when start time changes
	$('#start_time').on('change', function() {
		const startVal = new Date($(this).val());
		const newEndTime = new Date(startVal);
		newEndTime.setHours(newEndTime.getHours() + defaultDuration);
		newEndTime.setMinutes(newEndTime.getMinutes() - newEndTime.getTimezoneOffset());
		$('#end_time').val(newEndTime.toISOString().slice(0, 16));
	});

	// Create session
	$('#create-session-form').on('submit', function(e) {
		e.preventDefault();

		const formData = {
			action: 'skate_create_session',
			nonce: skateClubAdmin.nonce,
			session_name: $('#session_name').val(),
			start_time: $('#start_time').val().replace('T', ' ') + ':00',
			end_time: $('#end_time').val().replace('T', ' ') + ':00'
		};

		$.post(skateClubAdmin.ajaxUrl, formData, function(response) {
			if (response.success) {
				window.location.href = adminPageUrl + '&session-created=1';
			} else {
				window.location.href = adminPageUrl + '&session-error=' + encodeURIComponent(response.data.message);
			}
		}).fail(function() {
			window.location.href = adminPageUrl + '&session-error=' + encodeURIComponent('Failed to create session');
		});
	});

	// Activate session
	$(document).on('click', '.activate-session', function() {
		const sessionId = $(this).data('session-id');

		if (!confirm('Activate this session? This will deactivate any currently active session.')) {
			return;
		}

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_activate_session',
			nonce: skateClubAdmin.nonce,
			session_id: sessionId
		}, function(response) {
			if (response.success) {
				window.location.href = adminPageUrl + '&session-activated=1';
			} else {
				window.location.href = adminPageUrl + '&session-error=' + encodeURIComponent(response.data.message);
			}
		}).fail(function() {
			window.location.href = adminPageUrl + '&session-error=' + encodeURIComponent('Failed to activate session');
		});
	});

	// Close session
	$(document).on('click', '.close-session, #close-session', function() {
		const sessionId = $(this).data('session-id');

		if (!confirm('Close this session? This will reset the frontend display and prevent new submissions.')) {
			return;
		}

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_close_session',
			nonce: skateClubAdmin.nonce,
			session_id: sessionId
		}, function(response) {
			if (response.success) {
				window.location.href = adminPageUrl + '&session-closed=1';
			} else {
				window.location.href = adminPageUrl + '&session-error=' + encodeURIComponent(response.data.message);
			}
		}).fail(function() {
			window.location.href = adminPageUrl + '&session-error=' + encodeURIComponent('Failed to close session');
		});
	});

	// Show QR codes
	$('#show-qr-codes').on('click', function() {
		const container = $('#qr-codes-container');

		if (container.is(':visible')) {
			container.hide();
			$(this).text('Show QR Codes');
		} else {
			container.show();
			$(this).text('Hide QR Codes');
		}
	});
});
</script>
