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
					hiddenField.val(altFormat);
					el.change();
				}
			});
		}

		el.datepicker('show');
	});

	$('input.datepicker-frontend').on('change', function(){
		let el = $(this);
		var rawValue = !$(this).val() ? '' : $(this).val().replaceAll('-', '');
		$(this).closest('.input-wrapper').find('.datepicker-value').val(rawValue).change();
		setTimeout(function(){
			el.datepicker('destroy');
		}, 200);
	});

	function zeroPad(num) {
		var zero = 2 - num.toString().length + 1;
		return Array(+(zero > 0 && zero)).join("0") + num;		
	}
});