<?php
/**
 * Song Requests admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Display active session notice
Skate_Club_Admin_Menu::display_active_session_notice();

$active_session = Skate_Club_Session_Manager::get_active_session();
$session_id = $active_session ? $active_session->id : null;

$requests = array();
$total = 0;

if ( $session_id ) {
	$requests = Skate_Club_Song_Request::get_requests( $session_id, array( 'limit' => 100 ) );
	$total = Skate_Club_Song_Request::get_count( $session_id );
}
?>

<div class="wrap skate-club-admin">
	<h1>Song Requests</h1>

	<?php if ( ! $active_session ) : ?>
		<div class="notice notice-warning">
			<p>No active session. Please activate a session first.</p>
		</div>
	<?php else : ?>
		<p>Showing requests for: <strong><?php echo esc_html( $active_session->session_name ); ?></strong></p>
		<p>Total requests: <strong><?php echo intval( $total ); ?></strong></p>

		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1">Bulk Actions</option>
					<option value="delete">Delete</option>
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
					<th>ID</th>
					<th>Name</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Date of Birth</th>
					<th>Song Name</th>
					<th>Submitted</th>
					<th style="width: 100px;">Actions</th>
				</tr>
			</thead>
			<tbody id="the-list">
				<?php if ( empty( $requests ) ) : ?>
					<tr>
						<td colspan="9">No song requests yet. Share the QR code to start collecting requests!</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $requests as $request ) : ?>
						<tr data-request-id="<?php echo intval( $request->id ); ?>">
							<th scope="row" class="check-column">
								<label class="screen-reader-text" for="cb-select-<?php echo intval( $request->id ); ?>">Select Request</label>
								<input id="cb-select-<?php echo intval( $request->id ); ?>" type="checkbox" name="request[]" value="<?php echo intval( $request->id ); ?>">
							</th>
							<td><?php echo intval( $request->id ); ?></td>
							<td><?php echo esc_html( $request->first_name . ' ' . $request->last_name ); ?></td>
							<td><?php echo esc_html( $request->email ); ?></td>
							<td><?php echo esc_html( $request->phone ); ?></td>
							<td><?php echo esc_html( date( 'M j, Y', strtotime( $request->date_of_birth ) ) ); ?></td>
							<td><strong><?php echo esc_html( $request->song_name ); ?></strong></td>
							<td><?php echo esc_html( date( 'M j, g:i A', strtotime( $request->submitted_at ) ) ); ?></td>
							<td>
								<button type="button" class="button button-small remove-request">Remove</button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
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
			return; // Do nothing if not 'Delete'
		}

		const selected = [];
		$('tbody .check-column input[type="checkbox"]:checked').each(function() {
			selected.push($(this).val());
		});

		if (selected.length === 0) {
			alert('Please select items to delete.');
			return;
		}

		if (!confirm('Are you sure you want to delete ' + selected.length + ' items?')) {
			return;
		}

		// Perform Bulk Delete
		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_bulk_delete_requests', // New AJAX action we need to register
			nonce: skateClubAdmin.nonce,
			request_ids: selected
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert('Error: ' + (response.data || 'Unknown error'));
			}
		});
	});

	// Single Delete (Existing)
	$(document).on('click', '.remove-request', function() {
		if (!confirm('Remove this song request?')) return;

		const requestId = $(this).closest('tr').data('request-id');

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_remove_song_request',
			nonce: skateClubAdmin.nonce,
			request_id: requestId
		}, function(response) {
			if (response.success) {
				location.reload();
			}
		});
	});
});
</script>
