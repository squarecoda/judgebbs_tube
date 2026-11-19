jQuery(document).ready(function($){
	$('.update-playlist-privacy').on('click', function(){
		const privacyDiv = $(this).closest('.playlist-privacy');

		const playlistId = $(this).attr('playlist_id');
		const direction = $(this).attr('direction');

		if(!window.confirm('Are you sure you want to update the privacy of this playlist to ' + direction + '?')) return;

		const data = {
			action: 'bbs_update_playlist_privacy',
			playlist_id: playlistId,
			direction: direction,
		};
		console.log(data);

		privacyDiv.addClass('updating');

		setTimeout(function(){
			$.post('/wp-admin/admin-ajax.php', data, function(response){
				console.log(response);
				const responseJson = JSON.parse(response);
				console.log(responseJson);

				privacyDiv.removeClass('public').removeClass('private').removeClass('updating');
				const classUpdate = direction == 'private' ? 'private' : 'public';
				privacyDiv.addClass(classUpdate);
			});
		}, 500);
	});
});