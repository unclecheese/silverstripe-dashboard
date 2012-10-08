		<div id="dashboard-panel-$ID" class="dashboard-panel $ClassName $Size" data-refresh-url="$Link" >		
			<div class="dashboard-panel-header">
				<% if PrimaryActions %>
					<div class="dashboard-panel-header-actions">
						<% loop PrimaryActions %>
							$Action
						<% end_loop %>					
					</div>
				<% end_if %>

				<div class="dashboard-panel-icon">
					<img src="$Icon" width="24" height="24" />
				</div>

				<h3>$Title</h3>
			</div>
		
			<div class="dashboard-panel-content">
				$Content
			</div>

			<div class="dashboard-panel-footer">
				<% if SecondaryActions %>
				<div class="dashboard-panel-footer-actions">
					<% loop SecondaryActions %>					
						$Action					
					<% end_loop %>
				</div>
				<% end_if %>
				<div class="dashboard-panel-toolbar">
					<% if Dashboard.CanConfigurePanels %>
					<a class="btn-dashboard-panel-configure" href="$ConfigLink"><img src="dashboard/images/configure.png" /></a>
					<% end_if %>
					<% if Dashboard.CanDeletePanels %>
					<a class="btn-dashboard-panel-delete" href="$DeleteLink"><img src="dashboard/images/trash.png" /></a>
					<% end_if %>
				</div>
			</div>



			<div class="dashboard-panel-configure">			
				<form $Form.FormAttributes>
					<div class="dashboard-panel-configure-fields">
					<% loop Form.Fields %>
						$FieldHolder
					<% end_loop %>
					</div>
					<div class="dashboard-panel-configure-actions">
						<% loop Form.Actions %>
						$Field
						<% end_loop %>
					</div>
				</form>				
			</div>
		</div>
