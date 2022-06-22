<ul class="dashboard-panel-quick-links-list nav flex-column">
	<% loop $Links %>
		<li class="nav-item mb-2">
			<a <% if $NewWindow %>target="_blank"<% end_if %> class="nav-link btn btn-outline-secondary dashboard-panel-quick-link py-2" href="{$Link}">
				$Text
			</a>
		</li>
	<% end_loop %>
</ul>