/**
 * Support jQueryUI.combobox for SelectBoxRemoteControl.
 * http://jqueryui.com/autocomplete/#combobox
 * @node-attr data-data-url Zdroj dat. { items, isMoreResults }
 */
// select[data-classes~="filterable"] data-min-input="1" data-page-size="15"
$('select[data-classes~="filterable"][data-type!="remoteselect"]').combobox({
	'setup': function() {
		if ($(this.element).data('min-input')) {
			this.options.minLength = $(this.element).data('min-input');
		}
		if ($(this.element).data('page-size')) {
			this.options.pageSize = $(this.element).data('page-size');
		}
		return this.options;
	}
});

// data-data-url="url" data-type="remoteselect" data-classes="filterable" data-min-input="1" data-page-size="15"
$('select[data-type="remoteselect"]').combobox0({
	'sourceType': 'remote',
	'setup': function() {
		this.options.sourceRemoteUrl = $(this.element).data('data-url');
		if ($(this.element).data('min-input')) {
			this.options.minLength = $(this.element).data('min-input');
		}
		if ($(this.element).data('page-size')) {
			this.options.pageSize = $(this.element).data('page-size');
		}
		return this.options;
	}
});
