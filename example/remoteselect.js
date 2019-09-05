/**
 * Support Select2 for SelectBoxRemoteControl.
 * https://select2.github.io/
 * @node-attr data-data-url Zdroj dat. { items, isMoreResults }
 */
$('[data-type="remoteselect"]').select2({
	ajax: {
		url: function() {
			return $(this).data('data-url');
		},
		dataType: 'json',
		delay: 250,
		data: function (params) {
			return {
				term: params.term, // search term
				page: params.page
			};
		},
		processResults: function (data, params) {
			// parse the results into the format expected by Select2
			// since we are using custom formatting functions we do not need to
			// alter the remote JSON data, except to indicate that infinite
			// scrolling can be used
			params.page = params.page || 1;

			return {
				results: data.items,
				pagination: {
					more: (params.page * 10) < data.total
				}
			};
		},

	},
	minimumInputLength: 1,
});
