<?php


/**
 * Decorates the Member object to work with the Dashboard interface
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardMember extends DataExtension {



	static $db = array (
		'HasConfiguredDashboard' => 'Boolean'
	);



	static $has_many = array (
		'DashboardPanels' => 'DashboardPanel'
	);



	/**
	 * Removes the DashboardPanels tab from the Security section. Panels should not be managed there.
	 */
	public function updateCMSFields(FieldList $fields) {
		$fields->removeByName("DashboardPanels");
	}



	/**
	 * Ensures that new members get the default dashboard configuration. Once it has been applied,
	 * make sure this doesn't happen again, if for some reason a user insists on having an empty
	 * dashboard.
	 */
	public function onAfterWrite() {
		if(!$this->owner->HasConfiguredDashboard && !$this->owner->DashboardPanels()->exists()) {
			foreach(SiteConfig::current_site_config()->DashboardPanels() as $p) {
				$clone = $p->duplicate();
				$clone->SiteConfigID = 0;
				$clone->MemberID = $this->owner->ID;
				$clone->write();
			}
			$this->owner->HasConfiguredDashboard = 1;
			$this->owner->write();
		}
	}
}