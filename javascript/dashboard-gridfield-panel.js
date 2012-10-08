
(function($) {

	$('.DashboardGridFieldPanel [name=SubjectPageID]').entwine({


		loadResults: function() {
			var $t = this;
			$t.getPanel().find('[name=GridFieldName]').attr('disabled',true);
			$.ajax({
				url: this.data('lookupurl'),
				dataType: "JSON",
				data: {
					"pageid": this.val()
				},
				success: function(data) {
					html = "";
 					for(value in data){
            			html +="<option value='"+value+"'>"+data[value]+"</option>";
            		}        		
					$t.getPanel().find('[name=GridFieldName]').attr('disabled',false).html(html);					
				}
			});
		},


		onchange: function(e) {
			e.preventDefault();
			this.loadResults();
		}



	})

})(jQuery);