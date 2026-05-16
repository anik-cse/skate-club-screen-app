<?php
/**
 * Media Approval admin page.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin/pages
 */

// Display active session notice
Skate_Club_Admin_Menu::display_active_session_notice();

$active_session = Skate_Club_Session_Manager::get_active_session();
$session_id = $active_session ? $active_session->id : null;

$pending_media = array();
$all_media = array();
$media_stats = array(
	'total' => 0,
	'pending' => 0,
	'approved' => 0,
	'rejected' => 0,
);

if ( $session_id ) {
	$pending_media = Skate_Club_Media_Upload::get_pending_media( $session_id );

	// Get all media for this session
	$all_media = Skate_Club_Database::get_results(
		'media',
		array( 'session_id' => $session_id ),
		array(
			'order_by' => 'uploaded_at',
			'order'    => 'DESC',
			'limit'    => 200,
		)
	);

	// Add URLs to media objects
	$upload_dir = wp_upload_dir();
	foreach ( $all_media as $item ) {
		// Extract session ID and filename from path
		$path_parts = explode( '/skate-club/', $item->file_path );
		if ( count( $path_parts ) === 2 ) {
			$item->url = $upload_dir['baseurl'] . '/skate-club/' . $path_parts[1];

			// For images, add thumbnail URL (same as original for now)
			if ( $item->media_type === 'photo' ) {
				$item->thumbnail_url = $item->url;
			}

			// For videos, could add poster/thumbnail generation here
			if ( $item->media_type === 'video' ) {
				$item->poster_url = ''; // Placeholder for video thumbnail
			}
		}
	}

	// Calculate statistics
	$media_stats['total'] = count( $all_media );
	foreach ( $all_media as $media ) {
		if ( $media->status === 'pending' ) {
			$media_stats['pending']++;
		} elseif ( $media->status === 'approved' ) {
			$media_stats['approved']++;
		} elseif ( $media->status === 'rejected' ) {
			$media_stats['rejected']++;
		}
	}
}
?>

<div class="wrap skate-club-admin">
	<h1>Media Approval</h1>

	<?php if ( ! $active_session ) : ?>
		<div class="notice notice-warning">
			<p>No active session. Please activate a session first.</p>
		</div>
	<?php else : ?>
		<p>Session: <strong><?php echo esc_html( $active_session->session_name ); ?></strong></p>
		<p>Pending approval: <strong><?php echo count( $pending_media ); ?></strong> items</p>

		<?php if ( ! empty( $pending_media ) ) : ?>
			<p>
				<button type="button" class="button button-primary" id="approve-all">Approve All</button>
				<button type="button" class="button button-secondary" id="reject-all">Reject All</button>
			</p>

			<div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
				<?php foreach ( $pending_media as $media ) : ?>
					<div class="media-item" data-media-id="<?php echo intval( $media->id ); ?>" style="border: 1px solid #ccc; padding: 10px; border-radius: 4px;">
						<?php if ( $media->media_type === 'photo' ) : ?>
							<img src="<?php echo esc_url( $media->url ); ?>" alt="Uploaded photo" style="width: 100%; height: auto;">
						<?php else : ?>
							<video src="<?php echo esc_url( $media->url ); ?>" controls style="width: 100%; height: auto;"></video>
						<?php endif; ?>
						<p><small>Type: <?php echo esc_html( ucfirst( $media->media_type ) ); ?></small></p>
						<p><small>Size: <?php echo esc_html( size_format( $media->file_size ) ); ?></small></p>
						<p><small>Uploaded: <?php echo esc_html( date( 'M j, g:i A', strtotime( $media->uploaded_at ) ) ); ?></small></p>
						<p>
							<button type="button" class="button button-small button-primary approve-item">Approve</button>
							<button type="button" class="button button-small button-secondary reject-item">Reject</button>
						</p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="notice notice-info">
				<p>No pending media. All uploads have been reviewed!</p>
			</div>
		<?php endif; ?>

		<!-- Media Statistics -->
		<div class="card" style="max-width: 100%; margin: 40px 0 20px 0;">
			<h2>Media Statistics</h2>
			<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 15px;">
				<div style="padding: 15px; background: #f0f0f1; border-radius: 4px; text-align: center;">
					<div id="stat-total" style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo intval( $media_stats['total'] ); ?></div>
					<div style="font-size: 13px; color: #646970; margin-top: 5px;">Total Media</div>
				</div>
				<div style="padding: 15px; background: #fff3cd; border-radius: 4px; text-align: center;">
					<div id="stat-pending" style="font-size: 24px; font-weight: bold; color: #856404;"><?php echo intval( $media_stats['pending'] ); ?></div>
					<div style="font-size: 13px; color: #856404; margin-top: 5px;">Pending Review</div>
				</div>
				<div style="padding: 15px; background: #d4edda; border-radius: 4px; text-align: center;">
					<div id="stat-approved" style="font-size: 24px; font-weight: bold; color: #155724;"><?php echo intval( $media_stats['approved'] ); ?></div>
					<div style="font-size: 13px; color: #155724; margin-top: 5px;">Approved</div>
				</div>
				<div style="padding: 15px; background: #f8d7da; border-radius: 4px; text-align: center;">
					<div id="stat-rejected" style="font-size: 24px; font-weight: bold; color: #721c24;"><?php echo intval( $media_stats['rejected'] ); ?></div>
					<div style="font-size: 13px; color: #721c24; margin-top: 5px;">Rejected</div>
				</div>
			</div>
		</div>

		<!-- All Media -->
		<h2>All Media (<?php echo count( $all_media ); ?>)</h2>

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

		<?php if ( ! empty( $all_media ) ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column">
							<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
							<input id="cb-select-all-1" type="checkbox">
						</td>
						<th style="width: 50px;">ID</th>
						<th style="width: 120px;">Preview</th>
						<th style="width: 80px;">Type</th>
						<th style="width: 100px;">Size</th>
						<th style="width: 120px;">Status</th>
						<th>Uploaded</th>
						<th style="width: 100px;">Actions</th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php foreach ( $all_media as $media ) :
						$row_style = '';
						if ( $media->status === 'approved' ) {
							$row_style = 'background: #d4edda;';
						} elseif ( $media->status === 'rejected' ) {
							$row_style = 'background: #f8d7da;';
						}
					?>
						<tr data-media-id="<?php echo intval( $media->id ); ?>" data-status="<?php echo esc_attr( $media->status ); ?>" style="<?php echo esc_attr( $row_style ); ?>">
							<th scope="row" class="check-column">
								<label class="screen-reader-text" for="cb-select-<?php echo intval( $media->id ); ?>">Select Media</label>
								<input id="cb-select-<?php echo intval( $media->id ); ?>" type="checkbox" name="media[]" value="<?php echo intval( $media->id ); ?>">
							</th>
							<td><?php echo intval( $media->id ); ?></td>
							<td>
								<?php if ( $media->media_type === 'photo' ) : ?>
									<img src="<?php echo esc_url( $media->url ); ?>" alt="Media preview" style="width: 100px; height: auto; border-radius: 4px; border: 1px solid #ccc;">
								<?php else : ?>
									<video src="<?php echo esc_url( $media->url ); ?>" style="width: 100px; height: auto; border-radius: 4px; border: 1px solid #ccc;"></video>
								<?php endif; ?>
							</td>
							<td><strong><?php echo esc_html( ucfirst( $media->media_type ) ); ?></strong></td>
							<td><?php echo esc_html( size_format( $media->file_size ) ); ?></td>
							<td>
								<?php
								$status_colors = array(
									'pending'  => array( 'bg' => '#fff3cd', 'text' => '#856404' ),
									'approved' => array( 'bg' => '#28a745', 'text' => '#ffffff' ),
									'rejected' => array( 'bg' => '#dc3545', 'text' => '#ffffff' ),
								);
								$status = $media->status;
								$color = isset( $status_colors[ $status ] ) ? $status_colors[ $status ] : $status_colors['pending'];
								?>
								<span style="display: inline-block; padding: 3px 10px; background: <?php echo esc_attr( $color['bg'] ); ?>; color: <?php echo esc_attr( $color['text'] ); ?>; border-radius: 3px; font-size: 12px; font-weight: 500;">
									<?php echo esc_html( ucfirst( $status ) ); ?>
								</span>
							</td>
							<td><?php echo esc_html( date( 'M j, g:i A', strtotime( $media->uploaded_at ) ) ); ?></td>
							<td>
								<button type="button" class="button button-small remove-media-item">Remove</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="notice notice-info">
				<p>No media uploaded for this session yet.</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// ... existing functions ...
	
	function updatePendingCount() {
		const remaining = $('.media-grid .media-item').length; // Corrected selector
		$('p strong').first().text(remaining);

		if (remaining === 0) {
			$('#approve-all, #reject-all').prop('disabled', true);
		}
	}

	function updateStatistics(action, count) {
		// Decrease pending
		const currentPending = parseInt($('#stat-pending').text()) || 0;
		$('#stat-pending').text(Math.max(0, currentPending - count));

		// Increase approved or rejected
		if (action === 'approve') {
			const currentApproved = parseInt($('#stat-approved').text()) || 0;
			$('#stat-approved').text(currentApproved + count);
		} else if (action === 'reject') {
			const currentRejected = parseInt($('#stat-rejected').text()) || 0;
			$('#stat-rejected').text(currentRejected + count);
		}
	}

	function updateTableRow(mediaId, action) {
		const $row = $('tbody tr[data-media-id="' + mediaId + '"]');
		if ($row.length === 0) return;

		const newStatus = action === 'approve' ? 'approved' : 'rejected';
		$row.attr('data-status', newStatus);

		// Update background color
		if (newStatus === 'approved') {
			$row.css('background', '#d4edda');
		} else if (newStatus === 'rejected') {
			$row.css('background', '#f8d7da');
		}

		// Update status badge
		const $statusCell = $row.find('td:nth-child(6)'); // Fixed index due to checkbox col
		if (newStatus === 'approved') {
			$statusCell.html('<span style="display: inline-block; padding: 3px 10px; background: #28a745; color: #ffffff; border-radius: 3px; font-size: 12px; font-weight: 500;">Approved</span>');
		} else if (newStatus === 'rejected') {
			$statusCell.html('<span style="display: inline-block; padding: 3px 10px; background: #dc3545; color: #ffffff; border-radius: 3px; font-size: 12px; font-weight: 500;">Rejected</span>');
		}
	}

	function processMedia(mediaIds, action, $items) {
		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_' + action + '_media',
			nonce: skateClubAdmin.nonce,
			media_ids: mediaIds
		}, function(response) {
			if (response.success) {
				// Update statistics
				updateStatistics(action, mediaIds.length);

				// Update table rows
				mediaIds.forEach(function(mediaId) {
					updateTableRow(mediaId, action);
				});

				// Remove items with fade effect
				$items.fadeOut(400, function() {
					$(this).remove();
					updatePendingCount();
				});

				// Show success message
				const actionText = action === 'approve' ? 'approved' : 'rejected';
				const notice = $('<div class="notice notice-success is-dismissible"><p><strong>Media ' + actionText + ' successfully!</strong></p></div>');
				$('.wrap h1').after(notice);

				// Auto-dismiss after 3 seconds
				setTimeout(function() {
					notice.fadeOut(300, function() { $(this).remove(); });
				}, 3000);
			} else {
				const notice = $('<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> ' + response.data.message + '</p></div>');
				$('.wrap h1').after(notice);
			}
		});
	}

	// Approve single item
	$(document).on('click', '.approve-item', function() {
		const $item = $(this).closest('.media-item');
		const mediaId = $item.data('media-id');
		processMedia([mediaId], 'approve', $item);
	});

	// Reject single item
	$(document).on('click', '.reject-item', function() {
		const $item = $(this).closest('.media-item');
		const mediaId = $item.data('media-id');
		processMedia([mediaId], 'reject', $item);
	});

	// Approve all
	$('#approve-all').on('click', function() {
		if (!confirm('Approve all pending media?')) return;

		const $items = $('.media-grid .media-item'); // Corrected selector
		const mediaIds = $items.map(function() {
			return $(this).data('media-id');
		}).get();

		if (mediaIds.length === 0) return;

		processMedia(mediaIds, 'approve', $items);
	});

	// Reject all
	$('#reject-all').on('click', function() {
		if (!confirm('Reject all pending media?')) return;

		const $items = $('.media-grid .media-item'); // Corrected selector
		const mediaIds = $items.map(function() {
			return $(this).data('media-id');
		}).get();

		if (mediaIds.length === 0) return;

		processMedia(mediaIds, 'reject', $items);
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
			alert('Please select items to delete.');
			return;
		}

		if (!confirm('Are you sure you want to permanently delete ' + selected.length + ' media items?')) {
			return;
		}

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_bulk_delete_media',
			nonce: skateClubAdmin.nonce,
			media_ids: selected
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert('Error: ' + (response.data || 'Unknown error'));
			}
		});
	});

	// Remove media from All Media table
	$(document).on('click', '.remove-media-item', function() {
		if (!confirm('Remove this media item permanently?')) return;

		const $row = $(this).closest('tr');
		const mediaId = $row.data('media-id');

		$.post(skateClubAdmin.ajaxUrl, {
			action: 'skate_remove_media',
			nonce: skateClubAdmin.nonce,
			media_id: mediaId
		}, function(response) {
			if (response.success) {
				$row.fadeOut(300, function() {
					$(this).remove();
				});
			}
		});
	});
});
</script>
