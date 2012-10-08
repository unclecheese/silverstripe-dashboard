(function($) {



	$('.dashboard-panel').entwine({
		refresh: function() {
			var $t = this;
			this.addClass('loading');
			$.ajax({
				url: this.attr('data-refresh-url'),
				success: function(data) {
					$t.replaceWith(data);					
				}
			})
		},
		
		showConfigure: function() {
			console.log("show");
			var $t = this;
			this.flip({
				direction: "rl",
				content: $(this).find('.dashboard-panel-configure').html(),
				color: "#dfdfdf",
				onEnd: function() {
					if($t.hasClass("refreshable")) {
						$t.refresh();						
					}
					$t.find('.ui-button').each(function() {
						if($(this).find('.ui-button-text').length > 1) {
							var text = $(this).text();
							$(this).html("<span class='ui-button-text'>"+text+"</span>");
						}
					});

				}
			});
			console.log("done");
		},

		hideConfigure: function() {
			this.revertFlip();
		},


	});

	

	$('.dashboard-panel *').entwine({
		getPanel: function() {
			return this.parents(".dashboard-panel:first");
		},
		getConfigurationPanel: function() {		
			return this.getPanel().find('.dashboard-panel-configure:first');
		},
		getConfigurationForm: function() {
			return this.getPanel().find('.configure-form:first');
		},
		getHasManyEditors: function() {
			return this.getPanel().find('.dashboard-has-many-editor');
		},
		getHasManyFormWrapper: function() {
			return this.getPanel().find('.dashboard-has-many-editor-form:first');
		},
		getHasManyForm: function() {
			return this.getPanel().find('.dashboard-has-many-editor-detail-form-form:first');
		},
		getConfigurationActions: function() {
			return this.getPanel().find('.dashboard-panel-configure-actions:first');
		}
	})


	$('.ss-fancy-dropdown').entwine({
		Open: false,
		toggle: function() {
			if(this.getOpen()) {
				this.obscure();
			}
			else {				
				this.reveal();
			}
		},
		reveal: function() {
			this.find('.ss-fancy-dropdown-options').css({'display':'block'});
			this.setOpen(true);			
		},
		obscure: function () {
			this.find('.ss-fancy-dropdown-options').css({'display':'none'})
			this.setOpen(false);							
		}
	});

	$('.ss-fancy-dropdown *').entwine({		
		getDropdown: function() {
			return this.closest(".ss-fancy-dropdown");
		}

	});

	$('.ss-fancy-dropdown-btn').entwine({
		onclick: function(e) {
			e.preventDefault();
			e.stopPropagation();
			this.getDropdown().toggle();
		},

	});

	$('.ss-fancy-dropdown-options a').entwine({
		onclick: function(e) {
			this._super(e);
			this.getDropdown().obscure();
		}
	})

	$('body').entwine({
		onclick: function() {
			this._super();
			$('.ss-fancy-dropdown').obscure();
		}
	});


	$('.manage-dashboard').entwine({
		onclick: function(e) {
			e.preventDefault();
			$('.dashboard').createPanel();
		}
	});


	$('.dashboard-message-link').entwine({
		onclick: function(e) {
			e.preventDefault();
			$.ajax({
				url: this.attr('href'),
				success: function(data) {
					$('#dashboard-message').html(data).slideDown();
					setTimeout(function() {
						$('#dashboard-message').slideUp(function() {$(this).html('');});
					},5000);
				}
			})
		}
	})
	
	$('.btn-dashboard-panel-delete').entwine({
		onclick: function(e) {
			e.preventDefault();
			var $panel = this.getPanel();
			$.ajax({
				url: this.attr('href'),
				success: function() {
					$panel.fadeOut(function() {
						$panel.remove();
					})
				}
			})
		}
	});




	$('.btn-dashboard-panel-configure').entwine({
		onclick: function(e) {
			e.preventDefault();
			this.getPanel().showConfigure();
		}
	});

	$('.dashboard-panel-configure-actions [name=action_cancel]').entwine({
		onclick: function(e) {
			e.preventDefault();
			this.getPanel().addClass("refreshable");
			this.getPanel().hideConfigure();
		}
	});


	$('.dashboard-panel-configure-actions [name=action_saveConfiguration]').entwine({
		onclick: function(e) {
			e.preventDefault();
			var $form = this.getConfigurationForm();
			$.ajax({
				url: $form.attr('action'),
				data: $form.serialize(),
				type: "POST",
				success: function(data) {
					$form.getPanel().addClass("refreshable");
					$form.getPanel().revertFlip();
				}
			})
		}
	})



	$('.available-panel').entwine({
		onclick: function(e) {
			e.preventDefault();
			configure = this.data('configure');
			var $this = this;
			$.ajax({
				url: this.data('create-url'),
				success: function(data) {
					$this.getPanel().replaceWith(data);					
					$('.dashboard').setSort($('.dashboard').sortable("serialize"));
					if(configure) {
						$('.dashboard-panel:first').showConfigure();
					}
				}
			})
		}
	})




	$('.dashboard-sortable').entwine({
		setSort: function(serial) {			
			$.ajax({
				url: this.attr('data-sort-url'),
				data: serial
			});
		}		
	})

	$( ".dashboard").entwine({
		onmatch: function() {
			this.sortable({
				items: ".dashboard-panel",
				handle: ".dashboard-panel-header",
				update: function() {					
					$(".dashboard").setSort($(this).sortable("serialize"));
				}
			});			
		},
		createPanel: function() {			
			var $newpanel = $('.dashboard-panel-selection:first').clone();
			$newpanel.show().css('width',0);
			$('.dashboard-panel-list').prepend($newpanel);
			$newpanel.animate({'width':'45%'},function() {
				$(this).find('.dashboard-panel-selection-inner').fadeIn();
			})
		}
	});


	$('.dashboard-create-cancel').entwine({
		onclick: function(e) {			
			this.getPanel().find('.dashboard-panel-selection-inner').fadeOut(function() {
					$(this).getPanel().animate({'width':0}, function() {
						$(this).remove();
					})
			});
		}
	})


	$('.dashboard-has-many-editor *').entwine({
		getFormHolder: function() {
			return this.closest(".dashboard-has-many-editor").find('.dashboard-has-many-editor-form:first');
		}
	});


	$('.dashboard-has-many-editor-header a').entwine({
		onclick: function(e) {
			e.preventDefault();
			this.getHasManyFormWrapper().toggle();
			this.getHasManyFormWrapper().loadForm();
		}
	});


	$('.dashboard-has-many-editor').entwine({
		refresh: function() {
			var $t = this;
			$.ajax({
				url: this.data('refresh-url'),
				success: function(data) {
					$t.replaceWith(data);
				}
			})
		}
	});
	


	$('.dashboard-has-many-editor-form').entwine({

		Open: false,

		onmatch: function () {
			this.css({'width': this.getPanel().innerWidth()-40});
		},
		reveal: function(callback) {			
			this.animate({height: '248px' }, callback);
			this.getPanel().find('.dashboard-panel-configure-actions').hide();

		},
		obscure: function(callback) {
			this.animate({height: 0 }, callback);
			this.getPanel().find('.dashboard-panel-configure-actions').show();

		},
		toggle: function(callback) {
			if(this.getOpen()) {
				this.obscure(callback);
			}
			else {
				this.reveal(callback);
			}
		},
		loadForm: function(link) {
			var $t = this;
			if(!link) {
				link = this.data('url');
			}
			$.ajax({
				url: link,
				success: function(data) {					
					$t.reveal(function() {
						$t.getPanel().append(data);
					})
				}
			});
		}

	});

	$('.dashboard-has-many-editor-detail-form-form').entwine({
		onmatch: function() {
			this.css({'width': this.getPanel().innerWidth()-50});
		},
		onsubmit: function(e) {
			e.preventDefault();
			var $form = this;
			$.ajax({
				url: $form.attr('action'),
				type: "POST",
				data: $form.serialize(),
				success: function(data) {
					$form.fadeOut(function() {
						$(this).getHasManyFormWrapper().obscure();
					});	
					$form.getEditor().refresh();
				}
			})
		},
		getEditor: function() {
			return this.getPanel().find('.dashboard-has-many-editor:first');
		}
	});

	$('.dashboard-has-many-editor-detail-form-actions [name=action_cancel]').entwine({
		onclick: function(e) {
			e.preventDefault();
			this.closest("form").fadeOut(function() {
				$(this).getEditor().obscure();
			});
			
		}
	});




	$('.dashboard-has-many-editor-detail-form-actions [name=action_cancel]').entwine({
		onclick: function(e) {
			e.preventDefault();
			this.closest("form").fadeOut(function() {
				$(this).getPanel().find('.dashboard-has-many-editor-form').obscure();
			});
			
		}
	});


	$('.dashboard-has-many-list .delete-link').entwine({
		onclick: function(e) {
			e.preventDefault();
			var $t = this;
			$.ajax({
				url: this.attr('href'),
				success: function() {
					$t.closest("li").fadeOut(function() {
						$(this).remove();
					})
				}
			})
		}
	});



	$('.dashboard-has-many-list .edit-link').entwine({
		onclick: function(e) {
			e.preventDefault();
			this.getFormHolder().toggle();
			this.getFormHolder().loadForm(this.attr('href'));
		}
	});

	$('.dashboard-has-many-list').entwine({
		onmatch: function() {
			var $t = this;
			this.sortable({
				items: "li",				
				update: function() {
					$t.setSort($(this).sortable("serialize"));
				}
			});
		}
	});



	$('.configure-form .dashboard-button-options-btn-group > a').entwine({
		onclick: function(e) {			
			console.log(this.getButtonGroup().getValue());
			this.closest(".dashboard-panel")
				.removeClass(this.getButtonGroup().getValue())
				.addClass(this.data('value'));
			this._super(e);
		}
	});

})(jQuery);