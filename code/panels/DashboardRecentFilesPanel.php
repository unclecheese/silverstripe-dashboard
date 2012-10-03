<?php

/**
 * Defines the "Recent Files" dashboard panel type
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */

class DashboardRecentFilesPanel extends DashboardPanel {

	
	static $db = array (
		'Count' => 'Int'
	);



	static $defaults = array (
		'Count' => 10
	);


	static $icon = "dashboard/images/recent-files.png";


	static $priority = 20;


	public function getLabel() {
		return _t('RecentFiles.LABEL','Recent Files');
	}


	public function getDescription() {
		return _t('RecentFiles.DESCRIPTION','Shows a linked list of recently edited files');
	}


	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(TextField::create("Count",_t('DashboardRecentFile.COUNT','Number of files to display')));
		return $fields;
	}


	

	/**
	 * Gets a list of the recently uploaded files to the CMS
	 *
	 * @return ArrayList
	 */
	public function RecentFiles() {
		$records = File::get()
			->filter(array(
				'ClassName:Negation' => 'Folder'
			))
			->sort("LastEdited DESC")
			->limit($this->Count);
		$set = ArrayList::create(array());
		foreach($records as $r) {
			$set->push(ArrayData::create(array(
				'EditLink' => Injector::inst()->get("AssetAdmin")->Link("EditForm/field/File/item/{$r->ID}/edit"),
				'Title' => $r->Title
			)));
		}
		return $set;
	}

	
}