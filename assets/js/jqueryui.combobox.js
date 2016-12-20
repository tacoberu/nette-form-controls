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
			this.input.data("autocomplete")._renderMenu = function (ul, items) {
				return self._renderMenu(this, ul, items);
			}

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

		// Vykreslení menu
		_renderMenu: function (menu, ul, items) {
			function isScrollbarBottom(container) {
				var height = container.outerHeight();
				var scrollHeight = container[0].scrollHeight;
				var scrollTop = container.scrollTop();
				if (scrollTop >= scrollHeight - (height * 2)) {
					return true;
				}
				return false;
			};

			//remove scroll event to prevent attaching multiple scroll events to one container element
			$(ul).unbind("scroll");

			var self = this;
			var pages = Math.ceil(self.total / self.options.pageSize);

			if (pages > 1 && pages >= (self.page || 1)) {
				$(ul).scroll(function () {
					if (isScrollbarBottom($(ul)) && ! self.lock) {
						self.page = (self.page || 1) + 1;
						menu._search(self.term);
					}
				});
			}

			$.each(items, function (index, item) {
				// Originální <option> nechceme vykreslovat protože by nám kazil řadu.
				if ($(item.option).attr('rel') != 'original') {
					menu._renderItem(ul, item);
				}
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

			// Nastavení výšky pro AJAXem načítané.
			var widget = this.input.autocomplete('widget');
			widget.css({
				'max-height': (this.options.pageSize - 1) * 24,
				'overflow-y': 'auto'
			});
		},

		// Source from <option>s of <select>
		_modelIsOptions: function( request ) {
			var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
			var decorator = new RegExp("(?![^&;]+;)(?!<[^<>]*)("
				+ $.ui.autocomplete.escapeRegex(request.term)
				+ ")(?![^<>]*>)(?![^&;]+;)", "gi");
			return this.element.children( "option" ).map(function() {
				var text = $( this ).text();
				if ( this.value && ( ! request.term || matcher.test(text) ) )
					return {
						label: text.replace(decorator, "<strong>$1</strong>"),
						value: text,
						option: this
					};
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

			// Reset vyhledávání, protože změna termu.
			if (self.term != request.term) {
				self.page = 1;
			}
			self.term = request.term;

			self.lock = true;
			$.get(this.options.sourceRemoteUrl, {
				term: request.term,
				page: this.page || 1,
				pageSize: this.options.pageSize
			}, function (data) {

				// Vyprázdnit <select>, protože první stránka. Zvolená hodnota nemuí být mezi
				// načtenými optiony. To by nám to resetovalo na první. Takže jej uchováme.
				// Ale protože by nám tem překážel tak poznačíme, aby se nevykresloval. že nebude vidět
				// nám nevadí, protože tam může být pak dvakrát, to když se donačte.
				if ( ! self.page || self.page <= 1) {
					var orig = self.element.val();
					self.element.empty();
					self.element.append($('<option/>', {
						value: orig,
						rel: "original"
					}));
				}

				self.total = data.total;

				// Přidat načtené <option>, aby je bylo možné selectovat.
				for (var _i in data.items) {
					data.items[_i].option = $('<option/>', {
						value: data.items[_i].id,
						text: data.items[_i].label
					})
					self.element.append(data.items[_i].option);
				}

				self.lock = false;

				response(self._modelIsOptions(request));
			});

		},

		_destroy: function() {
			this.wrapper.remove();
			this.element.show();
			$.Widget.prototype.destroy.call(this);
		}
	});

});
