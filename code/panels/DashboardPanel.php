<?php

/**
 * Defines the DashboardPanel dataobject. All dashboard panels must descend from this class.
 *
 * @package dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardPanel extends DataObject {


	static $db = array (
		'Title' => 'Varchar(50)',
		'SortOrder' => 'Int'		
	);



	static $has_one = array (
		'Member' => 'Member',
		'SiteConfig' => 'SiteConfig'
	);


	
	static $default_sort = "SortOrder ASC";


	/**
	 * @var string The size of the dashboard panel. Options: "small", "normal", and "large"
	 */
	static $size = "normal";



	/**
	 * @var string The path to the icon image that represents this dashboard panel type
	 */
	static $icon = "dashboard/images/dashboard-panel-default.png";




	/**
	 * @var int The "weight" of the dashboard panel when listed in the available panels.
	 *			Higher is lower in the list.
	 */	
	static $priority = 100;

	


	/**
	 * @var string The name of the template used for the contents of this panel.
	 */
	protected $template;


	
	/**
	 * @var string the name of the template used for the wrapper of this panel
	 */
	protected $holderTemplate = "DashboardPanel";



	/**
	 * Gets the template, falls back on a default value of the class name
	 *
	 * @return string
	 */
	protected function getTemplate() {
		return $this->template ? $this->template : $this->class;
	}



	/**
	 * Gets the holder template
	 *
	 * @return string
	 */
	public function getHolderTemplate() {
		return $this->holderTemplate;
	}



	/**
	 * Essentially an abstract method. Every panel must have this method defined to provide
	 * a title to the panel selection window
	 *
	 * @return string
	 */
	public function getLabel() {

	}



	/**
	 * Essentially an abstract method. Every panel must have this method defined to provide
	 * a description to the panel selection window
	 *
	 * @return string
	 */
	public function getDescription() {
		
	}



	/**
	 * An accessor to the Dashboard controller
	 *
	 * @return Dashboard
	 */
	public function getDashboard() {
		return Injector::inst()->get("Dashboard");
	}



	/**
	 * Renders the panel to its template
	 *
	 * @return SSViewer
	 */
	public function render() {
		return $this->renderWith($this->holderTemplate);
	}



	/**
	 * A template accessor for the icon of this panel
	 *
	 * @return string
	 */
	public function Icon() {
		return Config::inst()->get($this->class, "icon", Config::INHERITED);
	}



	/**
	 * Renders the inner contents of the panel. Similar to $Layout in pages.
	 *
	 * @return SSViewer
	 */
	public function Content() {
		return $this->renderWith($this->getTemplate());
	}




	/**
	 * The link to this panel through the Dashboard controller
	 *	 
	 * @return string
	 */
	public function Link($action = null) {
		return Controller::join_links($this->getDashboard()->Link("panel/{$this->ID}"),$action);
	}




	/**
	 * The link to delete this panel from the dashboard
	 *
	 * @return string
	 */
	public function DeleteLink() {
		return $this->Link("delete");
	}




	/**
	 * The link to create this panel on the dashboard
	 *
	 * @return string
	 */
	public function CreateLink() {
		return Controller::join_links($this->getDashboard()->Link("panel/new"),"?type={$this->class}");
	}




	/**
	 * Gets the {@link FieldList} object that is used to configure the fields on this panel.
	 * Similar to getCMSFields().
	 *
	 * @return FieldList
	 */
	public function getConfiguration() {
		return FieldList::create(
			TextField::create("Title", _t('Dashboard.TITLE','Title'))
		);
	}



	/**
	 * Gets the primary actions, which may appear in the top of the panel
	 *
	 * @return ArrayList
	 */
	public function getPrimaryActions() {
		return ArrayList::create(array());
	}




	/**
	 * Gets the secondary actions, which may appear in the bottom of the panel
	 *
	 * @return ArrayList
	 */
	public function getSecondaryActions() {
		return ArrayList::create(array());
	}



	/**
	 * Renders the entire panel. Similar to {@link FormField::FieldHolder()}
	 *
	 * @return SSViewer
	 */
	public function PanelHolder() {
		return $this->renderWith($this->holderTemplate);
	}



	/**
	 * A template accessor to get the size of this panel
	 *
	 * @return string
	 */
	public function Size() {
		return Config::inst()->get($this->class, "size", Config::INHERITED);
	}



	/**
	 * Gets the configuration form for this panel
	 *
	 * @return Form
	 */
	public function Form() {
		return Dashboard_PanelRequest::create($this->getDashboard(), $this)->ConfigureForm();
	}



	/**
	 * Duplicates this panel. Drills down into the has_many relations
	 *
	 * @return DashboardPanel
	 */
	public function duplicate($dowrite = true) {
		$clone = parent::duplicate(true);		
		foreach($this->has_many() as $relationName => $relationClass) {
			foreach($this->$relationName() as $relObject) {
				$relClone = $relObject->duplicate(false);
				$relClone->DashboardPanelID = $clone->ID;
				$relClone->write();
			}
		}
		return $clone;
	}




	public function canCreate($member = null) {
		$m = $member ? $member : Member::currentUser();
		return Permission::check("CMS_ACCESS_DashboardAddPanels");
	}
	


	public function canDelete($member = null) {
		$m = $member ? $member : Member::currentUser();
		return Permission::check("CMS_ACCESS_DashboardDeletePanels") && $this->MemberID == $m->ID;
	}

	

	public function canEdit($member = null) {
		$m = $member ? $member : Member::currentUser();
		return Permission::check("CMS_ACCESS_DashboardConfigurePanels") && $this->MemberID == $m->ID;
	}

	

	public function canView($member = null) {
		$m = $member ? $member : Member::currentUser();
		return Permission::check("CMS_ACCESS_Dashboard") && $this->MemberID == $m->ID;
	}


}