jQuery(function ($) {
	if (typeof window.rtmedia_migration === 'undefined') {
		return;
	}

	var done = parseInt(window.rtmedia_migration.done || 0, 10);
	var total = parseInt(window.rtmedia_migration.total || 0, 10);
	var adminAjax = String(window.rtmedia_migration.admin_ajax || '');

	$("#toplevel_page_rtmedia-settings").addClass("wp-has-current-submenu");
	$("#toplevel_page_rtmedia-settings").removeClass("wp-not-current-submenu");
	$("#toplevel_page_rtmedia-settings").addClass("wp-menu-open");
	$("#toplevel_page_rtmedia-settings>a").addClass("wp-menu-open");
	$("#toplevel_page_rtmedia-settings>a").addClass("wp-has-current-submenu");

	if (total < 1) {
		$("#submit").attr("disabled", "disabled");
	}

	function updateProgress(currentDone, currentTotal, pendingText) {
		var pct = Math.ceil((currentDone / currentTotal) * 100);
		if (pct > 100) {
			pct = 100;
		}
		$("#rtprogressbar>div").css("width", pct + "%");
		$("span.finished").text(currentDone);
		$("span.total").text(currentTotal);
		if (typeof pendingText !== 'undefined') {
			$("span.pending").text(pendingText);
		}
	}

	function showSyncing(show) {
		if (show) {
			$("#rtMediaSyncing").show();
		} else {
			$("#rtMediaSyncing").hide();
		}
	}

	function dbStartMigration(currentDone, currentTotal) {
		if (currentDone < currentTotal) {
			showSyncing(true);
			$.ajax({
				url: adminAjax,
				type: 'post',
				data: {
					action: 'bp_media_rt_db_migration',
					done: currentDone
				}
			}).done(function (sdata) {
				var data;
				try {
					data = JSON.parse(sdata);
				} catch (e) {
					$("#submit").attr('disabled', '');
					return;
				}

				if (data && data.status) {
					var newDone = parseInt(data.done, 10);
					var newTotal = parseInt(data.total, 10);
					updateProgress(newDone, newTotal, data.pending);
					dbStartMigration(newDone, newTotal);
				} else {
					alert('Migration completed.');
					showSyncing(false);
				}
			}).fail(function () {
				alert('Error During Migration, Please Refresh Page then try again');
				$("#submit").removeAttr('disabled');
			});
		} else {
			alert('Migration completed.');
			showSyncing(false);
		}
	}

	$(document).on('click', '#submit', function (e) {
		e.preventDefault();
		dbStartMigration(done, total);
		$(this).attr('disabled', 'disabled');
	});
});


