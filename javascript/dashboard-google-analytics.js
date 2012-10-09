(function($) {

$('.dashboard-panel-configure-fields [name=PathType]').entwine({
    onmatch: function() {
      this.toggle();
    },
    onclick: function() {
      this.toggle();
    },
    toggle: function() {
      if(this.is(":checked")) {
        if(this.val() == "list") {
          this.closest("form").find('#SubjectPageID').show();
          this.closest("form").find('#CustomPath').hide();
        }
        else if(this.val() == "custom") {
         this.closest("form").find('#CustomPath').show();
         this.closest("form").find('#SubjectPageID').hide(); 
        }
        else {
          this.closest("form").find('#CustomPath').hide();
          this.closest("form").find('#SubjectPageID').hide();
        }
      }
    }

})


})(jQuery);