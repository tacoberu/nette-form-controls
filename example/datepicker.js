$('[data-type="date"]').datepicker({
	language: 'cs'
});
$('[data-widget="datepicker"]').each(function() {
	var opts = {};
	opts.language = 'cs';
	if ($(this).data('date-format')) {
		opts.format = $(this).data('date-format');
	}
	$(this).datepicker(opts);
});

// http://www.jonthornton.com/jquery-timepicker/
$('[data-widget="timepicker"]').each(function() {
	var opts = {};
	opts.language = 'cs';
	if ($(this).data('time-format')) {
		opts.timeFormat = $(this).data('time-format');
	}
	if ($(this).data('time-step')) {
		opts.step = $(this).data('time-step');
	}
	$(this).timepicker(opts);
});
