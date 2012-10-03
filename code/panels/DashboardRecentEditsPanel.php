<?php


/**
 * Defines the "Recent Edits" dashboard panel type
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardRecentEditsPanel extends DashboardPanel {

	
	static $db = array (
		'Count' => 'Int'
	);



	static $defaults = array (
		'Count' => 10
	);


	static $icon = "dashboard/images/recent-edits.png";


	static $priority = 10;


	public function getLabel() {
		return _t('RecentEdits.LABEL','Recent Edits');
	}



	public function getDescription() {
		return _t('RecentEdits.DESCRIPTION','Shows a linked list of recently edited pages');
	}



	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(TextField::create("Count",_t('DashboardRecentEdits.COUNT','Number of pages to display')));
		return $fields;
	}



	/**
	 * Gets the recent edited pages, limited to a user provided number of records
	 *
	 * @return ArrayList
	 */
	public function RecentEdits() {
		$records = SiteTree::get()->sort("LastEdited DESC")->limit($this->Count);
		$set = ArrayList::create(array());
		foreach($records as $r) {
			$set->push(ArrayData::create(array(
				'EditLink' => Injector::inst()->get("CMSPagesController")->Link("edit/show/{$r->ID}"),
				'Title' => $r->Title
			)));
		}
		return $set;
	}


}