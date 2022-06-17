<div id="$ID" class="dashboard-button-options-btn-group $Size" data-toggle="buttons-radio">
	<% loop Options %><a class="<% if isChecked %>active<% end_if %> $FirstLast <% if Middle %>middle<% end_if %>" data-value="$Value" data-name="$Name">$Title</a><% end_loop %>
	<input type="hidden" name="$Name" value="$Value" />
</div>