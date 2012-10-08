<div id="pages-controller-cms-content" class="cms-content center " data-layout-type="border" data-pjax-fragment="Content">
	<div class="cms-content-header north">
		<div class="cms-content-header-info">
			<h2>
				<% include CMSBreadcrumbs %>
			</h2>
		</div>	
		<div class="dashboard-top-buttons">
		<% if CanAddPanels %>
			<a class="ss-ui-button ss-ui-action-constructive manage-dashboard" href="javascript:void(0);"><% _t('Dashboard.ADDPANEL','New Panel') %></a>
		<% end_if %>
		<% if IsAdmin %>
			<span class="ss-fancy-dropdown right">
				<a class="ss-ui-button ss-fancy-dropdown-btn" href="javascript:void(0)"><% _t('Dashboard_Content.ADMINISTRATION','Administration') %></a>
				<span class="ss-fancy-dropdown-options">
					<a class="set-as-default dashboard-message-link" href="$Link(setdefault)"><% _t('Dashboard.SETASDEFAULT','Make this the default dashboard') %></a>	
					<a class="apply-to-all dashboard-message-link" href="$Link(applytoall)"><% _t('Dashboard.APPLYTOALL','Apply this dashboard to all members') %></a>
				</span>
			</span>
		<% end_if %>

		</div>
	</div>
	<div class="dashboard dashboard-sortable" data-sort-url="$Link(sort)">
		<div id="dashboard-message"></div>		
		<div class="dashboard-panel-list">
		<% loop Panels %>
			$PanelHolder
		<% end_loop %>
		</div>
		<div class="dashboard-panel-selection dashboard-panel normal" id="dashboard-panel-0">
			<div class="dashboard-panel-selection-inner">
				<div class="dashboard-panel-header">
					<div class="dashboard-panel-icon">
						<img src="dashboard/images/dashboard-panel-default.png" width="24" height="24" />
					</div>

					<h3><% _t('Dashboard.CHOOSEPANELTYPE','Choose a panel type') %></h3>
				</div>		
				<div class="dashboard-panel-content">
					<% loop AllPanels %>
						<div class="available-panel $EvenOdd" data-type="$Class" data-create-url="$CreateLink" <% if ShowConfigure %>data-configure="true"<% end_if %>>
							<div class="available-panel-icon">
								<img src="$Icon" />
							</div>
							<div class="available-panel-content">
								<h4>$Label</h4>
								<p>$Description</p>
							</div>
						</div>
					<% end_loop %>				
				</div>
				<div class="dashboard-panel-footer">				
					<div class="dashboard-panel-footer-actions">			
						<button class="ss-ui-button dashboard-create-cancel"><% _t('Dashboard.CANCEL','Cancel') %></button>				
					</div>				
				</div>
			</div>
		</div>
	</div>

</div>