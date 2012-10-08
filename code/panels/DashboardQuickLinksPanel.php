<?php


/**
 * Defines the "Quick Links" dashboard panel type
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardQuickLinksPanel extends DashboardPanel {
	
	static $has_many = array (
		'Links' => 'DashboardQuickLink'
	);


	
	static $defaults = array (
		'PanelSize' => "small"
	);



	static $icon = "dashboard/images/quick-links.png";



	static $configure_on_create = true;



	public function getLabel() {
		return _t('Dashboard.QUICKLINKSLABEL','Quick Links');
	}



	public function getDescription() {
		return _t('Dashbaord.QUICKLINKSDESCRIPTION','Allows management of arbitrary links from the dashboard');
	}


	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(DashboardHasManyRelationEditor::create($this, "Links", "DashboardQuickLink"));
		return $fields;
	}

}