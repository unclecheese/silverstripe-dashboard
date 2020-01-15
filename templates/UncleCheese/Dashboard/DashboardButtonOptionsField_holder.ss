<div id="$Name" class="field<% if $extraClass %> $extraClass<% end_if %>">

	<div id="$ID" class="dashboard-button-options-btn-group $Size" data-toggle="buttons-radio">
		<a class="<% if $isChecked %>active<% end_if %> first" data-value="small" data-name="$Name" title="Small"></a>
		<a class="<% if $isChecked %>active<% end_if %> middle" data-value="normal" data-name="$Name" title="Medium"></a>
		<a class="<% if $isChecked %>active<% end_if %> last" data-value="large" data-name="$Name" title="Large"></a>
		<input type="hidden" name="$Name" value="$Value"/>
	</div>
	<% if $RightTitle %><label class="right">$RightTitle</label><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<% if $Description %><span class="description">$Description</span><% end_if %>
</div>
