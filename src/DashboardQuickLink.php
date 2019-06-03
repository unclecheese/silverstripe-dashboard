<?php

namespace UncleCheese\Dashboard;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;

/**
 * Defines the "quick link" dataobject that is used in {@link DashboardQuickLinksPanel}
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardQuickLink extends DashboardPanelDataObject {
	
	private static $table_name = 'DashboardQuickLink';

	private static $db = [
		'Link' => 'Varchar(255)',
		'Text' => 'Varchar(50)',
		'NewWindow' => 'Boolean'
	];

	private static $has_one = [
		'Panel' => DashboardQuickLinksPanel::class,
	];


	
	private static $label_field = "Text";


	

	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(TextField::create("Link",_t('UncleCheese\Dashboard\DashboardQuickLink.LINK','Link (include http://)')));
		$fields->push(TextField::create("Text",_t('UncleCheese\Dashboard\DashboardQuickLink.LINKTEXT','Link text')));
		$fields->push(CheckboxField::create("NewWindow",_t('UncleCheese\Dashboard\DashboardQuickLink.NEWWINDOW','Open link in new window')));
		return $fields;
	}
}