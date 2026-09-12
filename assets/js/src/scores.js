jQuery(document).ready(function($){
	if($('.reference-score-wrapper').length) {
		updateOverallScore();

		$('.reference-score-display .edit-score-button').on('click', function(){
			const parentDiv = $(this).closest('.reference-score-display');

			parentDiv.addClass('editing');
		});	
		$('.reference-score-display .cancel-edit-score').on('click', function(){
			const parentDiv = $(this).closest('.reference-score-display');

			parentDiv.removeClass('editing');
		});	

		$('.reference-score-display .score-edit .submit-score-edit').on('click', function(){
			updateReferenceScore($(this).closest('.score-edit'));
		});	

		$('.reference-score-display .score-edit .score-value').on('keydown', function(e){
			if(e.originalEvent.key == 'Enter') {
				e.preventDefault();
				updateReferenceScore($(this).closest('.score-edit'));
			}
		});	

		$('.reference-score-wrapper.contest-scores .reference-score-display .score-edit .score-value').on('keydown', function(e){
			if(e.originalEvent.key == 'Enter') {
				e.preventDefault();
			}

			const el = $(this);
			setTimeout(function(){
				el.closest('.reference-score-display').attr('score', el.val());
				updateOverallScore();
			}, 500);
		});	

		function updateReferenceScore(el) {
			const parentDiv = el.closest('.reference-score-display');

			const category = el.attr('category');
			const input = el.find('input[category="' + category + '"]');
			const value = input.val();
			const postId = el.closest('.reference-score-wrapper').attr('post_id');

			console.log(value);

			if(value || value === '0') {
				input.val('');
				parentDiv.removeClass('editing');
				parentDiv.find('.score-view').html('<span class="fas fa-cog fa-spin"></span>');

				const data = {
					action: 'bbs_update_reference_score',
					category: category,
					score: value,
					post_id: postId,
					time: Date.now(),
				};
				console.log(data);

				$.post('/wp-admin/admin-ajax.php', data, function(response){
					console.log(response);
					responseJson = JSON.parse(response);
					console.log(responseJson);
	
					parentDiv.attr('score', responseJson.score);
					parentDiv.find('.history.scfe-modal-trigger').attr('update_history', btoa(JSON.stringify(responseJson.score_history)));
					parentDiv.find('.score-view').html(responseJson.score);

					parentDiv.closest('.reference-score-wrapper').find('.reference-score-display.overall .score-view').html('<span class="fas fa-cog fa-spin"></span>');
					setTimeout(function(){
						updateOverallScore();
					}, 300);
				});
			} else {
				window.alert('Please enter a value before submitting');
			}
		}

		$('.reference-score-display .history.scfe-modal-trigger').on('click', function(){
			const target = $(this).attr('target');
			const category = $(this).attr('category');

			const history = $(this).attr('update_history');
			console.log(history);
			const historyStr = atob(history);
			console.log(historyStr);
			const historyJson = JSON.parse(historyStr);
			console.log(historyJson);

			let html = '';
			html += '<tr><th>Score</th><th>Updated By</th><th>Timestamp</th></tr>';
			$.each(historyJson, function(i, entry){
				console.log(i, entry)
				if(entry.type == 'contest_score') {
					html += '<tr class="contest-score"><td>' + entry.score + '</td><td colspan="2">Contest Score</td></tr>';
				} else {
					html += '<tr><td>' + entry.score + '</td><td>' + entry.updated_by + '</td><td>' + entry.timestamp + '</td></tr>';
				}
			})

			html = '<table class="reference-score-table ' + category + '">' + html + '</table>';

			$(target + ' .scfe-modal-body').html(html);
		});	

		function updateOverallScore() {
			$('.reference-score-wrapper').each(function(){
				const wrapperDiv = $(this);

				let totalScore = 0;
				let hasAllScores = true;

				const scoreDisplays = $(this).find('.reference-score-display[score]');

				scoreDisplays.each(function(){
					const score = $(this).attr('score');
					if(score || score === '0') {
						totalScore += parseFloat(score);
					} else {
						hasAllScores = false;
						$(this).addClass('editing');
					}
				});

				let avgScore = 'No data';
				if(hasAllScores) {
					avgScore = Math.round(totalScore * 10 / scoreDisplays.length) / 10;
				}

				wrapperDiv.find('.reference-score-display.overall .score-view').html(avgScore);
			});
		}
	}

});