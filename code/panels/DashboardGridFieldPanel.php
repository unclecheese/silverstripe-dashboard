<?php


/**
 * Defines a {@link DashboardPanel} object that shows a summary of a GridField instance
 * that manages a relationship on a SiteTree object.
 * Provides create, and "view all" actions.
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package Dashboard
 */
class DashboardGridFieldPanel extends DashboardPanel {
	


	static $db = array (
		'Count' => 'Int',		
		'GridFieldName' => 'Varchar'
	);



	static $has_one = array (
		'SubjectPage' => 'SiteTree'
	);



	static $defaults = array (
		'Count' => 10
	);



	static $configure_on_create = true;
	



	/**
	 * @var string Overrides the standard request handler to provide custom controller actions
	 */
	protected $requestHandlerClass = "DashboardGridField_PanelRequest";




	public function getLabel() {
		return _t('Dashboard.GRIDFIELDPANELTITLE','Grid Field Editor');
	}




	public function getDescription() {
		return _t('Dashboard.GRIDFIELDPANELDESCRIPTION','Adds a summary view of a GridField instance on a given page.');
	}




	/**
	 * Gets the actions for the top of the panel
	 *
	 * @return FieldList
	 */
	public function getPrimaryActions() {
		if(!$this->SubjectPageID || !$this->GridFieldName) return false;
		$actions = parent::getPrimaryActions();		
		$actions->push(DashboardPanelAction::create(
			$this->CreateModelLink(), 
			_t('Dashboard.CREATENEWGRIDFIELD','Create new'),
			"good"
		));
		return $actions;
	}




	/**
	 * Gets the actions for the bottom of the panel
	 *
	 * @return FieldList
	 */
	public function getSecondaryActions() {
		if(!$this->SubjectPageID || !$this->GridFieldName) return false;
		$actions = parent::getPrimaryActions();		
		$actions->push(DashboardPanelAction::create(
			$this->ViewAllLink(), 
			_t('Dashboard.VIEWALLGRIDFIELD','View all')
		));
		return $actions;
	}




	/**
	 * Gets the fields to configure the panel settings
	 *
	 * @return FieldList
	 */
	public function getConfiguration() {
		$fields = parent::getConfiguration();

		$grids = array ();
		if($this->SubjectPage()->exists()) {
			$grids = $this->getGridFieldsFor($this->SubjectPage());
		}
		$fields->push(TextField::create("Count", _t('DashbordModelAdmin.COUNT','Number of records to display')));

		$fields->push(DropdownField::create("SubjectPageID", _t('Dashboard.PAGE','Page'), $this->getHierarchy(0))
			->addExtraClass('no-chzn')
			->setAttribute('data-lookupurl', $this->Link("gridsforpage"))
			->setEmptyString("--- "._t('Dashboard.PLEASESELECT','Please select')." ---")
		);
		$fields->push(DropdownField::create("GridFieldName", _t('Dashboard.GRIDFIELDNAME','GridField name'), $grids)
			->addExtraClass('no-chzn')
		);

		return $fields;
	}




	/**
	 * A recursive function that gets a hierarchy of the site tree for a dropdown. Cannot use
	 * the {@link TreeDropdownField} here due to UI constraints.
	 *
	 * @param int The ID of the root node of the tree
	 * @param int The level of depth in the hierarchy. Used to create a visial hierarchy.
	 * @return array
	 */
	protected function getHierarchy($parentID, $level = 0) {
		$options = array();
		$class = "SiteTree";
		$filter = array('ParentID' => $parentID);
		$children = DataList::create($class)->filter($filter);
		if($children->exists()) {
			foreach($children as $child) {
				$indent="";
				for($i=0;$i<$level;$i++) $indent .= "&nbsp;&nbsp;";				
				$text = $child->Title;
				$options[$child->ID] = empty($text) ? "<em>$indent Untitled</em>" : $indent.$text;
				$options += $this->getHierarchy($child->ID, $level+1);
			}
		}
		return $options;
	}





	/**
	 * Gets the link to view all records in a GridField
	 *
	 * @return string
	 */
	public function ViewAllLink() {
		if($grid = $this->getGrid()) {
			return Controller::join_links(
				Injector::inst()->get("CMSMain")->Link("edit"),
				"show",
				$this->SubjectPageID
			)."#Root_".$this->getTabForGrid();
		}
	}




	/**
	 * Gets a link to create a new record in a GridField
	 *
	 * @return string
	 */
	public function CreateModelLink() {
		if($grid = $this->getGrid()) {
			return Controller::join_links(
				Injector::inst()->get("CMSMain")->Link("edit"),
				"EditForm",
				"field",
				$this->GridFieldName,
				"item",
				"new",
				"?ID={$this->SubjectPageID}"
			);
		}
	}




	/**
	 * Gets the GridFields for a given page
	 *
	 * @param SiteTree A given page
	 * @return array
	 */
	public function getGridFieldsFor(SiteTree $page) {
		$grids = array ();
		if(!$page || !$page->exists()) return $grids;

		foreach($page->getCMSFields()->dataFields() as $field) {
			if($field instanceof GridField) {
				$grids[$field->getName()] = $field->Title();
			}
		}
		if(empty($grids)) {
			return array(
				'' => _t('Dashboard.NOGRIDFIELDS','There are no GridFields defined for that page.')
			);
		}
		return $grids;
	}




	/**
	 * Gets the grid field instance given the selected page and GridField name
	 *
	 * @return GridField
	 */
	protected function getGrid() {
		if($this->SubjectPage()->exists() && $this->GridFieldName) {
			if($grid = $this->SubjectPage()->getCMSFields()->dataFieldByName($this->GridFieldName)) {				
				$grid->setForm($this->Form());
				return $grid;
			}
		}
	}




	/**
	 * Gets the name of the tab for the selected grid field name. Allows direct linking
	 * to the edit form to expose the grid.
	 *
	 * @return string
	 */
	protected function getTabForGrid() {
		if(!$this->SubjectPage()->exists() || !$this->GridFieldName) return false;
		$fields = $this->SubjectPage()->getCMSFields();
		if($fields->hasTabSet()) {
			foreach($fields->fieldByName("Root")->Tabs() as $tab) {
				if($tab->fieldByName($this->GridFieldName)) {
					return $tab->getName();
				}
			}
		}
		return false;
	}







	/**
	 * Gets the records in this GridField summary. Provides edit links and a title label
	 *
	 * @return ArrayList
	 */
	public function GridFieldItems() {
		if($grid = $this->getGrid()) {				
			$list = $grid->getList()
				->limit($this->Count)
				->sort("LastEdited DESC");			
			$ret = ArrayList::create(array());
			foreach($list as $record) {
				$ret->push(ArrayData::create(array(
					'EditLink' => Controller::join_links(
										Injector::inst()->get("CMSMain")->Link("edit"),
										"EditForm",
										"field",
										$this->GridFieldName,
										"item",
										$record->ID,
										"edit",
										"?ID={$this->SubjectPageID}"
									),
					'Title' => $record->getTitle()
				)));
			}
			return $ret;	
		}
	}




	/**
	 * Overload the renderer to load requirements
	 *
	 * @return SSViewer
	 */
	public function PanelHolder() {
		Requirements::javascript("dashboard/javascript/dashboard-gridfield-panel.js");
		return parent::PanelHolder();
	}



}




/**
 * This custom request handler allows controller actions that are unique to this panel type
 *
 * @author UncleCheese <unclecheese@leftandmain.com>
 * @package Dashbaord
 */
class DashboardGridField_PanelRequest extends Dashboard_PanelRequest {



	/**
	 * Given a requested page, get the GridField names and provide a JSON response
	 *
	 * @param SS_HTTPRequest The request object
	 * @return string
	 */
	public function gridsforpage(SS_HTTPRequest $r) {
		$pageid = (int) $r->requestVar('pageid');
		return Convert::array2json($this->panel->getGridFieldsFor(SiteTree::get()->byID($pageid)));
	}

	

}