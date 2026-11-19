jQuery(document).ready(function($){
	$('input.datepicker-frontend').on('focus', function(e){
		let el = $(this);
		if (!el.hasClass('hasDatepicker')) {
			el.datepicker({
				showOtherMonths: true,
				dateFormat: 'm/d/yy',
				altFormat: 'yymmdd',
				changeMonth: true,
				changeYear: true,
				yearRange: 'c-100:c+5',
				beforeShow: function(input, inst) {
					inst.dpDiv.addClass('sc-field-editor-datepicker');
				},
				onSelect: function(value, datepicker) {
					let altFormat = datepicker.selectedYear.toString() + zeroPad(datepicker.selectedMonth + 1) + zeroPad(datepicker.selectedDay);
					let hiddenField = $(this).closest('.input-wrapper').find('.datepicker-value');
					console.log(altFormat);
					hiddenField.val(altFormat);
					el.change();
				}
			});
		}

		el.datepicker('show');
	});

	$('input.datepicker-frontend').on('change', function(){
		let el = $(this);
		
		let hiddenValue = '';
		if($(this).val()) {
			const [month, day, year] = $(this).val().split('/');
			hiddenValue = `${year}${month.padStart(2, '0')}${day.padStart(2, '0')}`;
		}

		$(this).closest('.input-wrapper').find('.datepicker-value').val(hiddenValue).change();
		setTimeout(function(){
			el.datepicker('destroy');
		}, 200);
	});

	function zeroPad(num) {
		var zero = 2 - num.toString().length + 1;
		return Array(+(zero > 0 && zero)).join("0") + num;		
	}

	$('.video-post .add-to-playlist[video_id]').on('click', function(){
		const modal = $('#add-video-to-playlist-modal');

		const videoId = $(this).attr('video_id');
		const videoTitle = $(this).attr('video_title');
		const videoContestant = $(this).attr('video_contestant');
		const video_date = $(this).attr('video_date');
		const judgeId = $(this).attr('judge_id');

		console.log('videoId', videoId);

		modal.find('.modal-video-title').html(videoTitle + ' - ' + videoContestant + ' (' + video_date + ')');
		modal.find('.scfe-modal-body').html('<div class="loading-playlists"><span class="fas fa-cog fa-spin"></span> Loading Playlists...</div><div id="modal-playlists-wrapper" class="processing"></div>');

		const data = {
			action: 'bbs_load_personal_playlists',
			judge_id: judgeId,
		};
		console.log(data);

		$.post('/wp-admin/admin-ajax.php', data, function(response){
			console.log(response);
			const responseJson = JSON.parse(response);
			console.log(responseJson);

			modal.find('.scfe-modal-body #modal-playlists-wrapper').html(responseJson.html).attr('video_id', videoId);
			const playlistsDiv = modal.find('.scfe-modal-body .modal-playlists');
			markVideosAddedToPlaylist(playlistsDiv, videoId);
			modal.find('.scfe-modal-body #modal-playlists-wrapper').removeClass('processing');
			modal.find('.scfe-modal-body .loading-playlists').remove();
		});
	});

	function markVideosAddedToPlaylist(el, videoId) {
		el.find('.modal-playlist').each(function(){
			const videos = $(this).attr('videos');
			const videoArrayStr = atob(videos);
			if(videoArrayStr.includes('{"video":' + videoId + '}')) {
				$(this).addClass('added');
			} else {
				$(this).removeClass('added');
			}
			$(this).removeClass('processing');
		});
	}

	$('#add-video-to-playlist-modal').on('click', '.modal-playlist .fas.fa-plus', function(){
		updatePlaylistVideos($(this), 'add');
	});

	$('#add-video-to-playlist-modal').on('click', '.modal-playlist .fas.fa-minus', function(){
		updatePlaylistVideos($(this), 'remove');
	});

	function updatePlaylistVideos(el, direction) {
		const parentDiv = el.closest('.modal-playlist');

		const videoId = $('#modal-playlists-wrapper').attr('video_id');
		const playlistId = parentDiv.attr('playlist_id');
		
		const data = {
			action: 'bbs_update_playlist_videos',
			playlist_id: playlistId,
			video_id: videoId,
			direction: direction
		};
		console.log(data);

		parentDiv.addClass('processing');

		$.post('/wp-admin/admin-ajax.php', data, function(response){
			console.log(response);
			const responseJson = JSON.parse(response);
			console.log(responseJson);

			const playlistVideos = btoa(JSON.stringify(responseJson.playlist_videos))
			parentDiv.attr('videos', playlistVideos);
			markVideosAddedToPlaylist(parentDiv.closest('.modal-playlists'), videoId);
			parentDiv.find('.video-count').html('<span class="fas fa-video"></span> ' + responseJson.playlist_videos.length);
		});
	}
});