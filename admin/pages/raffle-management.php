<?php
/**
 * Raffle Management admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Display active session notice
Skate_Club_Admin_Menu::display_active_session_notice();

$active_session = Skate_Club_Session_Manager::get_active_session();
$session_id = $active_session ? $active_session->id : null;

$raffle_status = array();
$entries = array();

if ( $session_id ) {
	$raffle_status = Skate_Club_Raffle_Draw::get_status( $session_id );
	$entries = Skate_Club_Raffle_Draw::get_entries( $session_id, array( 'limit' => 200 ) );
}
?>

<div class="wrap skate-club-admin">
	<h1>Raffle Draw</h1>

	<?php if ( ! $active_session ) : ?>
		<div class="notice notice-warning">
			<p>No active session. Please activate a session first.</p>
		</div>
	<?php else : ?>
		<p>Session: <strong><?php echo esc_html( $active_session->session_name ); ?></strong></p>

		<div class="card" style="max-width: 600px; margin: 20px 0;">
			<h2>Raffle Status</h2>
			<p><strong>Status:</strong> <?php echo $raffle_status['is_open'] ? '<span style="color: green;">Open</span>' : '<span style="color: red;">Closed</span>'; ?></p>
			<p><strong>Total Entries:</strong> <?php echo intval( $raffle_status['entry_count'] ); ?></p>

			<?php if ( $raffle_status['winner'] ) : ?>
				<div style="padding: 20px; background: #4CAF50; color: white; border-radius: 4px; margin-top: 20px;">
					<h3 style="color: white; margin: 0 0 10px 0;">🎉 Winner Selected!</h3>
					<p style="font-size: 18px; margin: 0;"><strong><?php echo esc_html( $raffle_status['winner']->first_name . ' ' . $raffle_status['winner']->last_name ); ?></strong></p>
					<p style="margin: 5px 0 0 0;">
						<small>Email: <?php echo esc_html( $raffle_status['winner']->email ); ?></small><br>
						<small>Phone: <?php echo esc_html( $raffle_status['winner']->phone ); ?></small><br>
						<small>Selected: <?php echo esc_html( date( 'M j, Y g:i A', strtotime( $raffle_status['winner']->selected_at ) ) ); ?></small>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<h2>All Entries (<?php echo count( $entries ); ?>)</h2>

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

		<?php if ( ! empty( $entries ) ) : ?>
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
						<th>Submitted</th>
						<th style="width: 100px;">Actions</th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php foreach ( $entries as $entry ) : ?>
						<tr data-entry-id="<?php echo intval( $entry->id ); ?>" <?php echo ( $raffle_status['winner'] && $raffle_status['winner']->id == $entry->id ) ? 'style="background: #d4edda;"' : ''; ?>>
							<th scope="row" class="check-column">
								<label class="screen-reader-text" for="cb-select-<?php echo intval( $entry->id ); ?>">Select Entry</label>
								<input id="cb-select-<?php echo intval( $entry->id ); ?>" type="checkbox" name="entry[]" value="<?php echo intval( $entry->id ); ?>">
							</th>
							<td><?php echo intval( $entry->id ); ?></td>
							<td>
								<strong><?php echo esc_html( $entry->first_name . ' ' . $entry->last_name ); ?></strong>
								<?php if ( $raffle_status['winner'] && $raffle_status['winner']->id == $entry->id ) : ?>
									<span class="dashicons dashicons-awards" style="color: gold;"></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $entry->email ); ?></td>
							<td><?php echo esc_html( $entry->phone ); ?></td>
							<td><?php echo esc_html( date( 'M j, g:i A', strtotime( $entry->submitted_at ) ) ); ?></td>
							<td>
								<button type="button" class="button button-small remove-entry">Remove</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="notice notice-info">
				<p>No raffle entries yet. Share the QR code to start collecting entries!</p>
			</div>
		<?php endif; ?>
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
			return;
		}

		const selected = [];
		$('tbody .check-column input[type="checkbox"]:checked').each(function() {
			selected.push($(this).val());
		});

		if (selected.length === 0) {
			alert('Please select items to delete.');
			return;
		}

		if (!confirm('Are you sure you want to delete ' + selected.length + ' entries?')) {
			return;
		}

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_bulk_delete_raffle_entries',
			nonce: skateClubAdmin.nonce,
			entry_ids: selected
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert('Error: ' + (response.data || 'Unknown error'));
			}
		});
	});

	$(document).on('click', '.remove-entry', function() {
		if (!confirm('Remove this raffle entry?')) return;

		const entryId = $(this).closest('tr').data('entry-id');

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_remove_raffle_entry',
			nonce: skateClubAdmin.nonce,
			entry_id: entryId
		}, function(response) {
			if (response.success) {
				location.reload();
			}
		});
	});
});
</script>
