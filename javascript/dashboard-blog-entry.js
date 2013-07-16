(function($) {

$('.BlogEntryPanel a.ss-ui-button').entwine({
    onclick: function() {
	    event.preventDefault();

	    var frm = $('#Form_BlogEntryForm');
		$.ajax({
		    type: frm.attr('method'),
		    url: frm.attr('action'),
		    data: frm.serialize(),
		    success: function (data) {
		        frm.closest('.dashboard-panel-content').html(data);
		    },
		    error: function (data){alert('Failed')}
		});
		this.hide();
	}
});

})(jQuery);