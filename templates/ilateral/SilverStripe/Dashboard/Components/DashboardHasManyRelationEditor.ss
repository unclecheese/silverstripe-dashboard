<div class="field dashboard-has-many-editor" data-refresh-url="$Link">
	<div class="dashboard-has-many-editor-header">
		$Title
		<a class="btn btn-primary btn-sm mx-0" href="{$AddLink}">
			<%t ilateral\SilverStripe\Dashboard\Dashboard.ADD 'Add' %>
		</a>
	</div>
	<% if $Items %>
		<ul class="dashboard-has-many-list dashboard-sortable nav flex-column" data-sort-url="$Link('sort')">
			<% loop $Items %>
				<li id="item-$ID" class="dashboard-has-many-item {$EvenOdd} clearfix nav-item row mx-0 mb-2">
					<a class="edit-link col-10 px-2" href="$EditLink">$Label</a>
					<a class="delete-link col-2 px-2 text-center" href="$DeleteLink">
						<span class="font-icon-trash"></span>
					</a>
				</li>
			<% end_loop %>
		</ul>
	<% else %>
		<div class="dashboard-has-many-norecords"><%t ilateral\SilverStripe\Dashboard\Dashboard.NORECORDS 'No records' %></div>
	<% end_if %>

	<div class="dashboard-has-many-editor-form" data-url="$Link('item/new')">
	</div>
</div>
