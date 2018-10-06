<form $DetailForm.FormAttributes>
	<% loop $DetailForm.Fields %>
		$FieldHolder
	<% end_loop %>
	<div class="dashboard-has-many-editor-detail-form-actions">
		<% loop $DetailForm.Actions %>
			$Field
		<% end_loop %>
	</div>
</form>