<?php

namespace UncleCheese\Dashboard;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;

/** 
 * A {@link DataObject} subclass that is required for use on a has_many relationship
 * on a DashboardPanel when being managed with a {@link DashboardHasManyRelationEditor}
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardPanelDataObject extends DataObject {

	private static $table_name = 'DashboardPanelDataObject';

	private static $db = [
		'SortOrder' => 'Int'
	];



	private static $has_one = [
		'DashboardPanel' => 'DashboardPanel'
	];


	private static $default_sort = "SortOrder ASC";

	

	/**
	 * @var string Like $summary_fields, but these objects only render one field in list view.
	 */
	private static $label_field = "ID";
	
	
	/**
	 * @return FieldList
	 */
	public function getConfiguration() {
		$fields = FieldList::create();	
		return $fields;
	}




	/**
	 * Gets a form for editing or creating this object
	 *
	 * TODO: Is this used? Seems to be broken but dunno if it affects anything.
	 * @return Form
	 */
	public function getConfigFields() {
		$form = Form::create(Injector::inst()->get(Dashboard::class), "Form", $this->getConfiguration());
	}



}