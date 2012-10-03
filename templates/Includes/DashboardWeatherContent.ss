
			<h3>$Location</h3>
			<% loop Days %>
				<div class="dashboard-weather-day $FirstLast">
					<h4>$Label</h4>
					<span class="dashboard-weather-image"><img src="$ImageURL" /></span>
					<span class="dashboard-weather-temperature">$Low&deg;/$High&deg;</span>
				</div>
			<% end_loop %>
			<p><a href="$Link" target="_blank"><% _t('Dashboard.VIEWFORECAST','View full forecast') %></a></p>

