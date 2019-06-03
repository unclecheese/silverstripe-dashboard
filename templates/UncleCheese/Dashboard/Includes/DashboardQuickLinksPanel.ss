<ul class="dashboard-panel-quick-links-list">
	<% loop Links %>
	<li><a <% if NewWindow %>target="_blank"<% end_if %> class="ss-ui-button dashboard-panel-quick-link" href="$Link">$Text</a></li>
	<% end_loop %>
</ul>