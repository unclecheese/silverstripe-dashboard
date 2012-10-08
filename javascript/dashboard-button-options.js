(function($) {
$('.dashboard-button-options-btn-group').entwine({


	onmatch: function() {
		this.setValue();
	},

	setValue: function(val) {
		if(!val) val = this.find('[type=hidden]').val();
		var $t = this;
		this.find('a').removeClass('active').each(function() {
			if($(this).data('value') == val) {
				$(this).addClass('active');
				$t.getInput().val(val);
			}
		})
	},


	getValue: function() {
		return this.getInput().val();
	},

	getInput: function() {
		return this.find('[type=hidden]');
	}

});


$('.dashboard-button-options-btn-group *').entwine({
	getButtonGroup: function() {
		return this.closest(".dashboard-button-options-btn-group");
	}
})


$('.dashboard-button-options-btn-group > a').entwine({
	onclick: function(e) {
		e.preventDefault();
		this.getButtonGroup().setValue(this.data('value'));
	}
})
})(jQuery);