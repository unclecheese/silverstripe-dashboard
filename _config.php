<?php

$dir = basename(dirname(__FILE__));
if($dir != "dashboard") {
	user_error('Dashboard: Directory name must be "dashboard" (currently "'.$dir.'")',E_USER_ERROR);
}

Object::add_extension("Member", "DashboardMember");
Object::add_extension("SiteConfig","DashboardSiteConfig");
LeftAndMain::require_css("dashboard/css/dashboard_icon.css");