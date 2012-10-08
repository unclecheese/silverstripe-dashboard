<?php

/**
 * Defines the "Section Editor" dashboard panel type
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardSectionEditorPanel extends DashboardPanel {
	

	static $db = array (
		'ParentID' => 'Int',
		'Subject' => 'Varchar',
		'Count' => 'Int'
	);



	static $defaults = array (
		'Count' => 10
	);



	static $configure_on_create = true;
	
	


	public function getLabel() {
		return _t('Dashboard.SECTIONEDTIORLABEL','Section Editor');
	}



	public function getDescription() {
		return _t('Dashbaord.SECTIONEDITORDESCRIPTION','Pulls pages from a section of the website for viewing and creation');
	}



	/**
	 * Gets the icon for the panel. Use the icon of the subject page when possible, otherwise
	 * fall back on a default icon.
	 *
	 * @return string
	 */
	public function Icon() {
		$s = $this->Subject ? $this->Subject : "SiteTree";
		$file = Config::inst()->get($s, "icon", Config::INHERITED).".png";
		if(!Director::fileExists($file)) {
			$file = Config::inst()->get($s, "icon", Config::INHERITED)."-file.gif";
		}
		if(!Director::fileExists($file)) {
			$file = "dashboard/images/section-editor.png";
		}

		return $file;
	}




	/**
	 * A recursive function get gets the hierarchy of the SiteTree that use usable in a {@link DropdownField}.
	 * {@link TreeDropdownField} cannot be used due to UI constraints.
	 *
	 * @param int The ID of the root node
	 * @param int Level the current depth of the tree
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
				if($child->AllChildren()->exists()) {
					$text = $child->Title;
					$options[$child->ID] = empty($text) ? "<em>$indent Untitled</em>" : $indent.$text;
				}
				$options += $this->getHierarchy($child->ID, $level+1);
			}
		}
		return $options;
	}




	/**
	 * Get a list of all page types available in the CMS
	 *
	 * @return array
	 */
	protected function getPageTypes() {
		$types = array();
		foreach(SS_ClassLoader::instance()->getManifest()->getDescendantsOf("SiteTree") as $class) {
			$types[$class] = $class;
		}
		return $types;
	}




	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(DropdownField::create("ParentID","Section", $this->getHierarchy(0))
			->addExtraClass("no-chzn")
		);
		$fields->push(DropdownField::create("Subject", "Page type", $this->getPageTypes())
			->addExtraClass("no-chzn")
		);
		$fields->push(TextField::create("Count",_t('DashboardRecentEdits.COUNT','Number of pages to display')));		
		return $fields;
	}



	public function getPrimaryActions() {
		if(!$this->Subject || !$this->ParentID) return false;
		$actions = parent::getPrimaryActions();		
		$actions->push(DashboardPanelAction::create(
			$this->CreatePageLink(), 
			sprintf(_t('Dashboard.CREATENEW','Create new %s'),$this->SubjectSingularName()), 
			"good"
		));
		return $actions;
	}



	public function getSecondaryActions() {
		if(!$this->Subject || !$this->ParentID) return false;
		$actions = parent::getSecondaryActions();
		$actions->push(DashboardPanelAction::create(
			$this->ViewAllLink(),
			sprintf(_t('Dashboard.VIEWALL','View all %s'),$this->SubjectPluralName())			
		));
		return $actions;
	
	}


	public function SectionItems() {
		if(!$this->Subject || !$this->ParentID) return false;
		$set = SiteTree::get()
			->filter(array(
				'ParentID' => $this->ParentID,
				'ClassName' => $this->Subject
			))
			->limit($this->Count)
			->sort("LastEdited DESC");	
		$ret = ArrayList::create(array());
		foreach($set as $r) {
			$ret->push(ArrayData::create(array(
				'Title' => $r->Title,
				'EditLink' => Injector::inst()->get("CMSPagesController")->Link("edit/show/{$r->ID}")
			)));
		}
		return $ret;		
	}



	public function SubjectSingularName() {
		return Injector::inst()->get($this->Subject)->i18n_singular_name();
	}


	public function SubjectPluralName() {
		return Injector::inst()->get($this->Subject)->i18n_plural_name();	
	}



	public function CreatePageLink() {
		return Injector::inst()->get("CMSPagesController")->Link("add/AddForm")."?action_doAdd=1&ParentID={$this->ParentID}&PageType={$this->Subject}&SecurityID=".SecurityToken::getSecurityID();
	}



	public function ViewAllLink() {
		return Injector::inst()->get("CMSPagesController")->Link()."?ParentID=$this->ParentID&view=list";
	}




}