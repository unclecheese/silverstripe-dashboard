<div class="dashboard-panel-google-analytics">
	<% if ReportResults %>
	<div class="dashboard-panel-google-analytics-chart" id="chart-$ID"></div>


	<div id="page-analtyics-$ID" class="dashboard-panel-page-analytics">
	<% loop PageResults %>
	    <div class="metric $FirstLast"><span class="label">Pageviews</span><br /><strong>$FormattedPageViews</strong></div>
	    <div class="metric"><span class="label">Unique pageviews</span><br /><strong>$FormattedUniquePageViews</strong></div>
	    <div class="metric"><span class="label">Avg time on page</span><br /><strong>$AverageMinutesOnPage</strong></div>
	    <div class="metric"><span class="label">Bounce rate</span><br /><strong>$BounceRate</strong></div>
	    <div style="clear: left;"></div>
	<% end_loop %>
	</div>

	<% loop ReportResults %>
	  <div class="dashboard-google-analytics-data" data-pageviews="$PageViews" data-date="$FormattedDate"></div>
	<% end_loop %>      
	  <div class="dashboard-google-analytics-data" data-chart-title="$ChartTitle"></div>
	  <div class="dashboard-google-analytics-data" data-day-label="<% _t('Dashboard.DAY','Day') %>"></div>
	  <div class="dashboard-google-analytics-data" data-pageviews-label="<% _t('Dashboard.PAGEVIEWS','Pageviews') %>"></div>

	<% end_if %>
</div>