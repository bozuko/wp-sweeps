jQuery(function($){
    
    loadChart();
    
    function loadChart()
    {
        $charts = $('.entry-chart');
        $charts.css({height: 400});
        
		$charts.each(function(){
			
			var $chart = $(this)
			  , data = []
			  , chart_data = $chart.attr('data-counts')
			  ;
			  
			if( window.JSON ) chart_data = JSON.parse( chart_data );
			else eval("chart_data = "+chart_data);
			  
			for(var i=0; i<chart_data.length; i++){
				var date = chart_data[i].date.split('-');
				data[i] = [
					+(new Date(date[0],date[1]-1,date[2])), parseInt(chart_data[i].entries)
				];
			}
			
			var entryChart = new Highcharts.StockChart({
				chart : {
					renderTo : $chart[0]
				},
				
				yAxis : {
					min: 0
				},
	
				rangeSelector : {
					selected : 1
				},
	
				title : {
					text : 'Entries'
				},
				
				series : [{
					name : 'Entries',
					data : data,
					tooltip: {
						valueDecimals: 2
					}
				}]
			});
			
			function getDateString(d)
			{
				return [d.getFullYear(), p(d.getMonth()+1), p(d.getDate())].join('-')+' '+[p(d.getHours()), p(d.getMinutes()), p(d.getSeconds())].join(':');
			}
			
			function p( num, pad )
			{
				pad = pad || 2;
				var s = String(num);
				while( s.length < pad ) s=('0'+s);
				return s;
			}
		});
	}
});

