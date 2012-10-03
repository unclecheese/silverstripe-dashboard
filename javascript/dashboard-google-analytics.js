(function($) {
$('.dashboard-panel-google-analytics').entwine({
  onmatch: function() {
    if(!this.find('.dashboard-panel-google-analytics-chart').length) return;
    var $t = this;
    var data = new google.visualization.DataTable();    
    data.addColumn('string', this.getChartData('day-label'));
    data.addColumn('number', this.getChartData('pageviews-label'));        
    this.getChartDataNodes('pageviews').each(function() {
      data.addRow([
        $(this).data('date'), $(this).data('pageviews')
      ]);      
    });

    var chart = new google.visualization.AreaChart(document.getElementById(this.find('.dashboard-panel-google-analytics-chart').attr('id')));    
    chart.draw(data, {width: $t.innerWidth(), height: 160, title: this.getChartData('chart-title'),
                      colors:['#058dc7','#e6f4fa'],
                      areaOpacity: 0.1,
                      hAxis: {textPosition: 'in', showTextEvery: 5, slantedText: false, textStyle: { color: '#058dc7', fontSize: 10 } },
                      pointSize: 5,
                      legend: 'none',
                      chartArea:{left:0,top:30,width:"100%",height:"100%"}
    });
  },
  
  getChartData: function(key) {
    return this.find('[data-'+key+']').data(key);
  },

  getChartDataNodes: function(key) {
    return this.find('[data-'+key+']');
  }

  
});

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