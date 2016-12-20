jQuery(function($) {

	/**
	 * @source http://jqueryui.com/autocomplete/#combobox
	 * $(el).combobox()
	 * @TODO pagination
	 */
	$.widget("ui.combobox", {
		options: {
			delay: 100,
			minLength: 0,
			pageSize: 20,
			sourceType: 'options' // options | remote
			// sourceRemoteUrl: string
			// setup: function(this) -> options
		},

		_create: function() {
			if (this.options.setup) {
				this.options = this.options.setup.call(this, this);
			}

			// Validace sourceType
			switch (this.options.sourceType) {
				case 'remote':
					this.options.sourceType = "Remote";
					break;
				case 'options':
				default:
					this.options.sourceType = "Options";
			}

			var self = this;
			var wasOpen = false;

			this.wrapper = $( "<span/>" )
				.addClass( "ui-combobox" )
				.insertAfter( this.element );

			this.element.hide();
			this._createFilterInput();
			this._createDropDownButton();
			this._createAutocomplete();

			this.input.data("autocomplete")._renderItem = this._renderItem;

			// Registrace událostí.
			this.button.on( "click", function() {
				// close if already visible
				if (self.input.autocomplete("widget").is(":visible")) {
					self.input.autocomplete("close");
					return;
				}

				// Work around a bug (likely same cause as #5265)
				$(this).blur();

				// Pass empty string as value to search for, displaying all results
				self.input.autocomplete("search", "");
				self.input.focus();
			});

			this.button.on( "mousedown", function() {
				wasOpen = self.input.autocomplete( "widget" ).is( ":visible" );
			});
		},

		// Vykreslení jedného row.
		_renderItem: function(ul, item) {
			return $("<li/>")
				.data("item.autocomplete", item)
				.append("<a>" + item.label + "</a>")
				.appendTo( ul );
		},

		// Filtrovací input
		_createFilterInput: function() {
			var selected = this.element.children( ":selected" ),
				value = selected.val() ? selected.text() : "";

			this.input = $( "<input type='text'/>" )
				.appendTo( this.wrapper )
				.val( value )
				.attr( "title", "" )
				.addClass( "ui-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" );
		},

		// DropDown ikona
		_createDropDownButton: function() {
			this.button = $( "<a>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Show All Items" )
				.appendTo( this.wrapper )
				.button({
					icons: {
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass( "ui-corner-all" )
				.addClass( "ui-combobox-toggle ui-corner-right ui-button-icon" );
		},

		// Takovej ten výpis s výsledkama.
		_createAutocomplete: function() {
			var self = this;
			this.input
				.autocomplete({
					delay: this.options.delay,
					minLength: this.options.minLength,
					source: $.proxy( this, "_sourceFrom" + this.options.sourceType),
					select: function(event, ui) {
						ui.item.option.selected = true;
						$(ui.item.option).attr('selected', true);
						self._trigger("selected", event, {
							item: ui.item.option
						});
					},
					change: function(event, ui) {
						if ( ! ui.item) {
							var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i"),
								valid = false;

							// item must be in option collection
							self.input.children("option").each(function() {
								if ($(this).text().match(matcher)) {
									this.selected = valid = true;
									return false;
								}
							});

							// remove invalid value, as it didn't match anything
							if ( ! valid) {
								$(this).val("");
								self.element.val("");
								self.input.data("autocomplete").term = "";
								return false;
							}
						}
					}
				});
		},

		// Source from <option>s of <select>
		_sourceFromOptions: function( request, response ) {
			var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
			var decorator = new RegExp("(?![^&;]+;)(?!<[^<>]*)("
				+ $.ui.autocomplete.escapeRegex(request.term)
				+ ")(?![^<>]*>)(?![^&;]+;)", "gi");
			response( this.element.children( "option" ).map(function() {
				var text = $( this ).text();
				if ( this.value && ( ! request.term || matcher.test(text) ) )
					return {
						label: text.replace(decorator, "<strong>$1</strong>"),
						value: text,
						option: this
					};
			}).slice(0, this.options.pageSize) );
		},

		// Source from AJAX, and persist to <option>
		_sourceFromRemote: function( request, response ) {
			var decorator = new RegExp("(?![^&;]+;)(?!<[^<>]*)("
				+ $.ui.autocomplete.escapeRegex(request.term)
				+ ")(?![^<>]*>)(?![^&;]+;)", "gi");
			var self = this;

			$.get(this.options.sourceRemoteUrl, {
				term: request.term,
				page: 1,
				pageSize: this.options.pageSize
			}, function (data) {
				self.element.empty();
				// Přidat načtené <option>, aby je bylo možné selectovat.
				for (var _i in data.items) {
					data.items[_i].option = $('<option/>', {
						value: data.items[_i].id,
						text: data.items[_i].label
					})
					self.element.append(data.items[_i].option);
				}

				if (data.term.length > 0) {
					builder = function(x) {
						return {
							label: x.label.replace(decorator, "<strong>$1</strong>"),
							value: x.label,
							option: x.option
						};
					}
				}
				else {
					builder = function(x) {
						return {
							label: x.label,
							value: x.label,
							option: x.option
						};
					}
				}
				response($.map(data.items, builder));
			});

		},

		_destroy: function() {
			this.wrapper.remove();
			this.element.show();
			$.Widget.prototype.destroy.call(this);
		}
	});

});
