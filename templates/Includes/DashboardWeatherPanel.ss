<div class="dashboard-panel-weather">
	<% if Weather %>
		$Weather
	<% else %>
		<% if Location %>
			<p><% _t('Dashboard.WEATHERNORESPONSE','The weather server did not respond. Try again in a few minutes.') %></p>
		<% else %>
			<p><% _t('Dashboard.SELECTLOCATION','Please select a location whose weather you want to display.') %></p>
		<% end_if %>
	<% end_if %>

</div>