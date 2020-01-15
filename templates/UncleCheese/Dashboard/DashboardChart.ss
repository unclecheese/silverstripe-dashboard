<div class="dashboard-chart"
	data-title="$Title"
	data-xlabel="$XAxisLabel"
	data-ylabel="$YAxisLabel"
	data-textinterval="$TextInterval"
	data-height="$Height"
	data-pointsize="$PointSize"
	data-fontsize="$FontSize"
	data-textposition="$TextPosition"
>

	<div id="$ChartID" class="dashboard-chart-canvas"></div>

	<% loop $ChartData %>
		<div class="dashboard-chart-data" data-x="$XValue" data-y="$YValue"></div>
	<% end_loop %>


</div>