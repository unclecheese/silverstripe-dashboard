<?php


/**
 * Defines the "Quick Links" dashboard panel type
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardQuickLinksPanel extends DashboardPanel {
	
	private static $has_many = array (
		'Links' => 'DashboardQuickLink'
	);


	
	private static $defaults = array (
		'PanelSize' => "small"
	);



	private static $icon = "dashboard/images/quick-links.png";



	private static $configure_on_create = true;



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