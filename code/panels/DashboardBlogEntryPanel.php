<?php


/**
 * Defines the "BlogEntry" dashboard panel type
 *
 * @package Dashboard
 * @author Howard Grigg <howard@gri.gg>
 */
class DashboardBlogEntryPanel extends DashboardPanel {
	
	private static $has_one = array (
		'BlogHolder' => 'BlogHolder'
	);

	private static $defaults = array (
		'PanelSize' => "normal"
	);
	
	private static $icon = "dashboard/images/panel-blog.png";
	
	private static $configure_on_create = true;

	protected $requestHandlerClass = "BlogEntryPanel_RequestHandler";

	public function registered(){
		if(class_exists('BlogEntry')){
			return true;
		}else{
			return false;
		}
	}
	
	
	
	public function getLabel() {
		return _t('Dashboard.BLOGENTRYLABEL','Blog Entry');
	}

	public function getDescription() {
		return _t('Dashbaord.BLOGENTRYDESCRIPTION','Add a blog post from the Dashboard');
	}

	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(DropdownField::create("BlogHolderID", "Choose a page to link to:", DataList::create("BlogHolder")->map("ID", "Title", "Please Select")));
		return $fields;
	}

	public function BlogEntryForm(){
		$fields = FieldList::create(
			TextField::create('Title'),
			TextareaField::create('Content'),
			TextField::create("Tags", _t("BlogEntry.TS", "Tags (comma sep.)")),
			DateField::create("Date", _t("BlogEntry.DT", "Date")),
			HiddenField::create('ParentID', 'ParentID', $this->BlogHolderID)
		);
		$actions = new FieldList();
		
		return Form::create($this, 'BlogEntryForm', $fields, $actions);
	}

	public function getSecondaryActions() {
		$actions = parent::getSecondaryActions();
		$actions->push(DashboardPanelAction::create(
				$this->Link('processform'),
				"Post Blog"
			));
			
		return $actions;
	}

	public function PanelHolder() {
		Requirements::javascript("dashboard/javascript/dashboard-blog-entry.js");
		return parent::PanelHolder();
	}

}

class BlogEntryPanel_RequestHandler extends Dashboard_PanelRequest {

	public function BlogEntryForm(){
		$fields = FieldList::create(
			TextField::create('Title'),
			TextareaField::create('Content'),
			TextField::create("Tags", _t("BlogEntry.TS", "Tags (comma sep.)")),
			DateField::create("Date", _t("BlogEntry.DT", "Date"))
		);
		$actions = new FieldList( new FormAction('processform', 'Submit'));
		return Form::create($this, 'BlogEntryForm', $fields, $actions);
	}

	public function processform(SS_HTTPRequest $r) {
		$entry = BlogEntry::create();

		if($this->request->postVar('Title') != null){
			$entry->Title = $this->request->postVar('Title');
			$entry->Content = $this->request->postVar('Content');
			$entry->Tags = $this->request->postVar('Tags');
			$entry->Date = $this->request->postVar('Date');
			$entry->ParentID = $this->request->postVar('ParentID');
			$entry->write();
			$entry->publish('Stage', 'Live');
			$response = new SS_HTTPResponse(_t('Dashboard.Success','Successfully Published'), '200');
			$response->setStatusCode(200, _t('Dashboard.Posted','Blog Post Published'));
			return $response;
		}else{
			user_error('Blog Title and Content must be present', E_USER_ERROR);
		}
	}
}
