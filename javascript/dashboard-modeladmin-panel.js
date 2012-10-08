
(function($) {

	$('.DashboardModelAdminPanel [name=ModelAdminClass]').entwine({


		loadResults: function() {
			var $t = this;
			this.attr('disabled',true);
			$.ajax({
				url: this.data('lookupurl'),
				dataType: "JSON",
				data: {
					"modeladminpanel": this.val()
				},
				success: function(data) {
					html = "";
 					for(value in data){
            			html +="<option value='"+value+"'>"+data[value]+"</option>";
            		}        		
					$t.getPanel().find('[name=ModelAdminModel]').html(html);
					$t.attr('disabled',false);
				}
			});
		},


		onchange: function(e) {
			e.preventDefault();
			this.loadResults();
		}



	})

})(jQuery);