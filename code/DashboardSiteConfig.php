<?php


/**
 * Decorates the {@link SiteConfig} object to work with the Dashboard CMS interface
 * SiteConfig holds the default configuration of a dashboard.
 * 
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardSiteConfig extends DataExtension {
	

	static $has_many = array (
		'DashboardPanels' => 'DashboardPanel'
	);


}