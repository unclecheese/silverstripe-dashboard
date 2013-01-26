(function($) {
$('.dashboard-chart').entwine({
  onmatch: function() {    
    var $t = this;
    this.getData().hide();
    var data = new google.visualization.DataTable();    
    data.addColumn('string', this.data('xlabel'));
    data.addColumn('number', this.data('ylabel'));            
    this.getData().each(function() {
      data.addRow([
        $(this).data('x'), $(this).data('y')
      ]);      
    });

    var chart = new google.visualization.AreaChart(document.getElementById(this.getChart().attr('id')));    
    chart.draw(data, {width: $t.innerWidth(), height: $t.data('height'), title: $t.data('title'),
                      colors:['#058dc7','#e6f4fa'],
                      areaOpacity: 0.1,
                      hAxis: {textPosition: $t.data('textposition'), showTextEvery: $t.data('textinterval'), slantedText: false, textStyle: { color: '#058dc7', fontSize: $t.data('fontsize') } },
                      pointSize: $t.data('pointsize'),
                      legend: 'none',
                      chartArea:{left:0,top:30,width:"100%",height:"100%"}
    });
  },


  getChart: function() {
    return this.find('.dashboard-chart-canvas');
  },


  getData: function() {
    return this.find('.dashboard-chart-data');
  }


});



})(jQuery);
