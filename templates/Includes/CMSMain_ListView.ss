<div class="cms-content-toolbar">
<div class="cms-actions-row">
	<a class="cms-panel-link ss-ui-button" data-icon="back" href="admin/dashboard">Dashboard</a>
</div>

</div>

<div class="ss-dialog cms-page-add-form-dialog cms-dialog-content" id="cms-page-add-form" title="<% _t('CMSMain.AddNew', 'Add new page') %>">
	$AddForm
</div>

<div class="cms-panel-content center">
	<% if TreeIsFiltered %>
	<div class="cms-tree-filtered">
		<strong><% _t('CMSMain.ListFiltered', 'Filtered list.') %></strong>
		<a href="$LinkPages" class="cms-panel-link">
			<% _t('CMSMain.TreeFilteredClear', 'Clear filter') %>
		</a>
	</div>
	<% end_if %>

	<div class="cms-list" data-url-list="$Link(getListViewHTML)">
		$ListViewForm
	</div>
</div>