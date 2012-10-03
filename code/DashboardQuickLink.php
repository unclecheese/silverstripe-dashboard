<?php


/**
 * Defines the "quick link" dataobject that is used in {@link DashboardQuickLinksPanel}
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardQuickLink extends DashboardPanelDataObject {
	


	static $db = array (
		'Link' => 'Varchar(255)',
		'Text' => 'Varchar(50)',
		'NewWindow' => 'Boolean'
	);



	
	static $label_field = "Text";


	

	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(TextField::create("Link",_t('DashboardQuickLink.LINK','Link (include http://)')));
		$fields->push(TextField::create("Text",_t('DashboardQuickLink.LINKTEXT','Link text')));
		$fields->push(CheckboxField::create("NewWindow",_t('DashboardQuickLink.NEWWINDOW','Open link in new window')));
		return $fields;
	}
}