<% if not IsConfigured %>	
	<% _t('Dashboard.NOGOOGLEACCOUNT','<p>You have not defined a Google Analytics account for this project. You can set this up by setting the config settings <strong>email</strong>, <strong>profile</strong>, and <strong>key_file_path</strong> on <strong>DashboardGoogleAnalyticsPanel</strong>.</p><p><a target="_blank" href="https://github.com/erebusnz/gapi-google-analytics-php-interface#instructions-for-setting-up-a-google-service-account-for-use-with-gapi">More information on key files</a></p>') %>
<% else_if not IsConnected %>
	<% _t('Dashboard.INVALIDGOOGLEACCOUNT','<p>The account information you have entered for Google Analytics appears to be invalid. Please check the email and password combination and try again.</p>') %>
<% else %>
<div class="dashboard-panel-google-analytics">
	<% if Chart %>	
		$Chart
		<div id="page-analtyics-$ID" class="dashboard-panel-page-analytics">
		<% loop PageResults %>
		    <div class="metric $FirstLast"><span class="label">Pageviews</span><br /><strong>$FormattedPageViews</strong></div>
		    <div class="metric"><span class="label">Unique pageviews</span><br /><strong>$FormattedUniquePageViews</strong></div>
		    <div class="metric"><span class="label">Avg time on page</span><br /><strong>$AverageMinutesOnPage</strong></div>
		    <div class="metric"><span class="label">Bounce rate</span><br /><strong>$BounceRate</strong></div>
		    <div style="clear: left;"></div>
		<% end_loop %>
		</div>
	<% end_if %>
</div>
<% end_if %>

<% if $Error %>
	<p>$Error</p>
<% end_if %>