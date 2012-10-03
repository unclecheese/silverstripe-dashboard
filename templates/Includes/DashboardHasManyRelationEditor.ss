<div class="field dashboard-has-many-editor" data-refresh-url="$Link">
	<div class="dashboard-has-many-editor-header">
		<label class="left">$Title</label>
		<a class="ss-ui-button ss-ui-action-constructive small" href="$AddLink"><% _t('Dashboard.ADD','Add') %></a>
	</div>
	<% if Items %>
	<ul class="dashboard-has-many-list dashboard-sortable" data-sort-url="$Link(sort)">
		<% loop Items %>
		<li id="item-$ID" class="dashboard-has-many-item $EvenOdd clearfix"><a class="edit-link" href="$EditLink">$Label</a> <a class="delete-link" href="$DeleteLink"><img src="dashboard/images/delete.png" width="16" height="16" /></a> </li>
		<% end_loop %>
	</ul>
	<% else %>
	<div class="dashboard-has-many-norecords"><% _t('Dashboard.NORECORDS','No records') %></div>
	<% end_if %>

	<div class="dashboard-has-many-editor-form" data-url="$Link(item/new)">

	</div>
</div>
