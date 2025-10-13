jQuery(document).ready(function () {
	var $root = jQuery('#rtm-importer-root');
	if (!$root.length) {
		return;
	}

	var importerType = $root.data('importer');
	var done = parseInt($root.data('done') || 0, 10);
	var total = parseInt($root.data('total') || 0, 10);
	var lastId = parseInt($root.data('last-id') || 0, 10);
	var nonceFieldId = String($root.data('nonce-field-id') || '');
	var adminAjax = String($root.data('admin-ajax') || (window.rtmedia_admin && window.rtmedia_admin.rtmedia_admin_ajax) || '');

	if (total < 1) {
		jQuery('#submit').attr('disabled', 'disabled');
	}

	function updateProgressBar(currentDone, currentTotal) {
		var pct = Math.ceil((currentDone / currentTotal) * 100);
		if (pct > 100) {
			pct = 100;
		}
		jQuery('#rtprogressbar>div').css('width', pct + '%');
		jQuery('span.finished').text(currentDone);
		jQuery('span.total').text(currentTotal);
	}

	function showPending(pendingText) {
		jQuery('span.pending').text(pendingText);
	}

	function showSyncing(show) {
		if (show) {
			jQuery('#rtMediaSyncing').show();
		} else {
			jQuery('#rtMediaSyncing').hide();
		}
	}

	var failedIds = [];

	function startMigration(currentDone, currentTotal, lastProcessedId) {
		if (currentDone < currentTotal) {
			showSyncing(true);

			var action = '';
			if ('media-size' === importerType) {
				action = 'rtmedia_media_size_import';
			} else if ('activity-upgrade' === importerType) {
				action = 'rtmedia_activity_upgrade';
			}

			var ajaxData = {
				action: action,
				done: currentDone,
				last_id: lastProcessedId,
				nonce: jQuery.trim(jQuery('#' + nonceFieldId).val())
			};

			jQuery.ajax({
				url: adminAjax,
				type: 'post',
				data: ajaxData
			}).done(function (sdata) {
				var data;
				try {
					data = JSON.parse(sdata);
				} catch (e) {
					jQuery('#submit').attr('disabled', '');
					return;
				}

				if (data && data.status) {
					var newDone = parseInt(data.done, 10);
					var newTotal = parseInt(data.total, 10);
					updateProgressBar(newDone, newTotal);
					showPending(data.pending);

					if ('media-size' === importerType) {
						if (data.imported === false) {
							failedIds.push(data.media_id);
						}
						startMigration(newDone, newTotal, parseInt(data.media_id, 10));
					} else {
						if (data.imported === false) {
							failedIds.push(data.activity_id);
						}
						startMigration(newDone, newTotal, parseInt(data.activity_id, 10));
					}
				} else {
					alert('Migration completed.');
					showSyncing(false);
				}
			}).fail(function () {
				alert('Error During Migration, Please Refresh Page then try again');
				jQuery('#submit').removeAttr('disabled');
			});
		} else {
			if ('activity-upgrade' === importerType) {
				jQuery.post(adminAjax, { action: 'rtmedia_activity_done_upgrade' }, function () {
					alert('Database upgrade completed.');
				});
			} else {
				alert('Migration completed.');
			}

			if (failedIds.length > 0) {
				if ('media-size' === importerType) {
					jQuery('span.pending').text('Media with ID: ' + failedIds.join(', ') + " can not be imported. Please check your server error log for more details. Don't worry, you can end importing media size now :)");
				} else {
					jQuery('span.pending').html("Some activities are failed to upgrade, Don't worry about that.");
				}
			}

			showSyncing(false);
		}
	}

	jQuery(document).on('click', '#submit', function (e) {
		e.preventDefault();
		jQuery(this).attr('disabled', 'disabled');
		startMigration(done, total, lastId);
	});
});