<?php
/**
 * Spinner Wheel admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Display active session notice
Skate_Club_Admin_Menu::display_active_session_notice();

$active_session = Skate_Club_Session_Manager::get_active_session();
$session_id = $active_session ? $active_session->id : null;

$active_group = $session_id ? Skate_Club_Spinner_Wheel::get_active_group( $session_id ) : '';
$groups = $session_id ? Skate_Club_Spinner_Wheel::get_groups( $session_id ) : array();

$filter_group = isset( $_GET['filter_group'] ) ? sanitize_text_field( $_GET['filter_group'] ) : '';

$entries = array();
if ( $session_id ) {
	$entries = Skate_Club_Spinner_Wheel::get_entries( $session_id, $filter_group );
}
?>

<div class="wrap skate-club-admin">
	<h1>Spinner Wheel</h1>

	<?php if ( ! $active_session ) : ?>
		<div class="notice notice-warning">
			<p>No active session. Please activate a session first.</p>
		</div>
	<?php else : ?>
		<p>Session: <strong><?php echo esc_html( $active_session->session_name ); ?></strong></p>

		<!-- Display Control -->
		<div class="card" style="max-width: 600px; margin: 20px 0; border-left: 4px solid #2271b1;">
			<h2>Display Control</h2>
			<p>Control what is shown on the screen display.</p>
			
			<div style="display: flex; gap: 10px; align-items: center;">
				<label for="active_group_select"><strong>Active Group:</strong></label>
				<select id="active_group_select">
					<option value="all" <?php selected( empty($active_group) || $active_group === 'all' ); ?>>All Groups</option>
					<?php foreach ( $groups as $group ) : ?>
						<option value="<?php echo esc_attr( $group ); ?>" <?php selected( $active_group, $group ); ?>><?php echo esc_html( $group ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="button" class="button button-primary" id="update-active-group">Update Screen</button>
			</div>
			<p class="description">Select a group to limit the spinner wheel to only that group. Use "All Groups" to show everyone.</p>
		</div>

		<div class="card" style="max-width: 600px; margin: 20px 0;">
			<h2>Add Participant</h2>
			<form id="add-spinner-entry-form">
				<table class="form-table">
					<tr>
						<th><label for="participant_name">Participant Name</label></th>
						<td>
							<input type="text" id="participant_name" name="participant_name" class="regular-text" placeholder="Participant One" required>
						</td>
					</tr>
					<tr>
						<th><label for="group_select">Group</label></th>
						<td>
							<div id="group-select-wrapper">
								<select id="group_select" name="group_select">
									<option value="">-- Start a New Group --</option>
									<?php foreach ( $groups as $group ) : ?>
										<option value="<?php echo esc_attr( $group ); ?>"><?php echo esc_html( $group ); ?></option>
									<?php endforeach; ?>
									<option value="new_group">+ Create New Group</option>
								</select>
							</div>
							
							<div id="new-group-wrapper" style="<?php echo empty($groups) ? 'display:block;' : 'display:none;'; ?> margin-top: 5px;">
								<input type="text" id="new_group_name" name="new_group_name" class="regular-text" placeholder="Enter New Group Name">
								<?php if ( ! empty($groups) ) : ?>
									<button type="button" class="button button-small" id="cancel-new-group" style="margin-left: 5px;">Cancel</button>
								<?php endif; ?>
							</div>
							<p class="description">Select an existing group or create a new one.</p>
						</td>
					</tr>
					<tr>
						<th><label for="color">Color (optional)</label></th>
						<td>
							<input type="color" id="color" name="color">
							<p class="description">Leave blank for random color</p>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="submit" class="button button-primary">Add to Wheel</button>
				</p>
			</form>
		</div>

		<h2>Current Entries (<?php echo count( $entries ); ?>)</h2>
		
		<!-- Filter -->
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<select id="bulk-action-selector-top">
					<option value="-1">Bulk Actions</option>
					<option value="delete">Delete Selected</option>
				</select>
				<button type="button" id="doaction" class="button action">Apply</button>
			</div>
			<div class="alignleft actions">
				<select name="filter_group" id="filter_group_dropdown">
					<option value="">All Groups</option>
					<?php foreach ( $groups as $group ) : ?>
						<option value="<?php echo esc_attr( $group ); ?>" <?php selected( $filter_group, $group ); ?>><?php echo esc_html( $group ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="button" class="button" id="apply-filter">Filter</button>
			</div>
		</div>

		<?php if ( ! empty( $entries ) ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column">
							<input id="cb-select-all-1" type="checkbox">
						</td>
						<th style="width: 50px;">Color</th>
						<th>Name</th>
						<th>Group</th>
						<th>Last Won</th>
						<th style="width: 100px;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $entries as $entry ) : ?>
						<tr data-entry-id="<?php echo intval( $entry->id ); ?>">
							<th scope="row" class="check-column">
								<input type="checkbox" name="entry_item[]" class="entries-checkbox" value="<?php echo intval( $entry->id ); ?>">
							</th>
							<td>
								<div style="width: 30px; height: 30px; background: <?php echo esc_attr( $entry->color ); ?>; border: 1px solid #000;"></div>
							</td>
							<td><strong><?php echo esc_html( $entry->participant_name ); ?></strong></td>
							<td><?php echo ! empty( $entry->group_name ) ? '<span class="tag">' . esc_html( $entry->group_name ) . '</span>' : '&mdash;'; ?></td>
							<td><?php echo $entry->last_won_at ? esc_html( date( 'M j, g:i A', strtotime( $entry->last_won_at ) ) ) : 'Never'; ?></td>
							<td>
								<button type="button" class="button button-small remove-entry">Remove</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<style>
				.tag {
					background: #f0f0f1;
					padding: 2px 6px;
					border-radius: 4px;
					font-size: 12px;
					border: 1px solid #c3c4c7;
				}
			</style>
		<?php else : ?>
			<div class="notice notice-info">
				<p>No entries found.</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// ... (Rest of existing JS) ...

	// Select All Checkbox
	$('#cb-select-all-1').on('change', function() {
		$('.entries-checkbox').prop('checked', $(this).prop('checked'));
	});

	// Bulk Actions
	$('#doaction').on('click', function() {
		const action = $('#bulk-action-selector-top').val();
		if (action === 'delete') {
			const selected = [];
			$('.entries-checkbox:checked').each(function() {
				selected.push($(this).val());
			});

			if (selected.length === 0) {
				alert('No items selected.');
				return;
			}

			if (!confirm('Are you sure you want to delete ' + selected.length + ' items?')) return;

			$.post(skateClubAdmin.ajaxUrl, {
				action: 'skate_bulk_delete_spinner_entries',
				nonce: skateClubAdmin.nonce,
				entry_ids: selected
			}, function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
				}
			});
		}
	});

	// Generate random color from professional palette
	function getRandomColor() {
        // ... (existing implementation) ...
		const colors = [
			// Blues
			'#2196F3', '#1976D2', '#1565C0', '#0D47A1', '#03A9F4', '#0288D1', '#0277BD', '#01579B',
			'#00BCD4', '#0097A7', '#00838F', '#006064', '#3F51B5', '#303F9F', '#283593', '#1A237E',
			// Greens
			'#4CAF50', '#388E3C', '#2E7D32', '#1B5E20', '#8BC34A', '#689F38', '#558B2F', '#33691E',
			'#009688', '#00796B', '#00695C', '#004D40', '#66BB6A', '#43A047', '#2E7D32', '#1B5E20',
			// Purples & Pinks
			'#9C27B0', '#7B1FA2', '#6A1B9A', '#4A148C', '#673AB7', '#5E35B1', '#512DA8', '#311B92',
			'#E91E63', '#C2185B', '#AD1457', '#880E4F', '#F06292', '#EC407A', '#D81B60', '#C2185B',
			// Oranges & Reds
			'#FF5722', '#E64A19', '#D84315', '#BF360C', '#FF9800', '#F57C00', '#EF6C00', '#E65100',
			'#FF6F00', '#FF8F00', '#FFA000', '#FFB300', '#F44336', '#D32F2F', '#C62828', '#B71C1C',
			// Teals & Cyans
			'#00ACC1', '#00838F', '#006064', '#0097A7', '#26C6DA', '#00BCD4', '#00ACC1', '#0097A7',
			'#26A69A', '#009688', '#00897B', '#00796B', '#80CBC4', '#4DB6AC', '#26A69A', '#00897B',
			// Indigos & Deep Purples
			'#3F51B5', '#303F9F', '#283593', '#1A237E', '#5C6BC0', '#3F51B5', '#3949AB', '#303F9F',
			'#7E57C2', '#673AB7', '#5E35B1', '#512DA8', '#9575CD', '#7E57C2', '#673AB7', '#5E35B1',
			// Amber & Yellows
			'#FFC107', '#FFB300', '#FFA000', '#FF8F00', '#FFCA28', '#FFC107', '#FFB300', '#FFA000',
			'#FFD54F', '#FFCA28', '#FFC107', '#FFB300', '#FF6F00', '#F57F17', '#F9A825', '#F57F17'
		];
		return colors[Math.floor(Math.random() * colors.length)];
	}

	// Set random color on page load
	$('#color').val(getRandomColor());

	// Update Active Group
	$('#update-active-group').on('click', function() {
		const group = $('#active_group_select').val();
		const btn = $(this);
		btn.prop('disabled', true).text('Updating...');

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_set_active_spinner_group',
			nonce: skateClubAdmin.nonce,
			session_id: <?php echo intval( $session_id ?: 0 ); ?>,
			group_name: group
		}, function(response) {
			btn.prop('disabled', false).text('Update Screen');
			if (response.success) {
				alert('Screen updated! The spinner is now showing: ' + (group === 'all' ? 'All Groups' : group));
			} else {
				alert('Error: ' + response.data.message);
			}
		});
	});

	// Toggle New Group Input
	$('#group_select').on('change', function() {
		if ($(this).val() === 'new_group') {
			$('#new-group-wrapper').slideDown();
			$('#new_group_name').focus();
		} else {
			$('#new-group-wrapper').slideUp();
			$('#new_group_name').val(''); // Clear input if returning to select
		}
	});

	// Cancel New Group
	$('#cancel-new-group').on('click', function() {
		$('#group_select').val('').change(); // Reset to default
		$('#new-group-wrapper').slideUp();
	});

	// Filter Group
	$('#apply-filter').on('click', function() {
		const group = $('#filter_group_dropdown').val();
		const url = new URL(window.location.href);
		if (group) {
			url.searchParams.set('filter_group', group);
		} else {
			url.searchParams.delete('filter_group');
		}
		window.location.href = url.toString();
	});

	// Add entry
	$('#add-spinner-entry-form').on('submit', function(e) {
		e.preventDefault();

		// Use random color if field is empty
		let color = $('#color').val();
		if (!color) {
			color = getRandomColor();
		}

		// Determine group name
		let groupName = $('#group_select').val();
		if (groupName === 'new_group') {
			groupName = $('#new_group_name').val();
			if (!groupName) {
				alert('Please enter a name for the new group.');
				$('#new_group_name').focus();
				return;
			}
		}

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_add_spinner_entry',
			nonce: skateClubAdmin.nonce,
			session_id: <?php echo intval( $session_id ?: 0 ); ?>,
			participant_name: $('#participant_name').val(),
			group_name: groupName,
			color: color
		}, function(response) {
			if (response.success) {
				location.reload();
			}
		});
	});

	// Remove entry
	$(document).on('click', '.remove-entry', function() {
		if (!confirm('Remove this entry?')) return;

		const entryId = $(this).closest('tr').data('entry-id');

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_remove_spinner_entry',
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
