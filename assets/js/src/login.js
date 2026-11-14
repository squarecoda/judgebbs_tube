jQuery(function($){
	$('.login-form-link').on('click', function(e){
		e.preventDefault();
		
		let parentDiv = $(this).closest('.form-body');
		parentDiv.find('.login-form').show();
		parentDiv.find('.reset-password').hide();
	});

	$('.reset-password-link').on('click', function(e){
		e.preventDefault();

		let parentDiv = $(this).closest('.form-body');
		parentDiv.find('.reset-password').show();
		parentDiv.find('.login-form').hide();
	});
});