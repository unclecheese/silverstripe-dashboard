<?php

namespace ilateral\SilverStripe\Dashboard;

use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Security\Security;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Permission;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ClassLoader;
use SilverStripe\Security\PermissionProvider;
use ilateral\SilverStripe\Dashboard\Panels\DashboardPanel;

/**
 * Defines the Dashboard interface for the CMS
 *
 * @package Dashboard
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class Dashboard extends LeftAndMain implements PermissionProvider
{
    private static $menu_title = "Dashboard";

    private static $url_segment = "dashboard";

    private static $menu_priority = 100;

    private static $url_priority = 30;

    private static $menu_icon_class = 'font-icon-dashboard';

    private static $tree_class = 'DashboardPanel';

    private static $url_handlers = [
		'panel/$ID' => 'handlePanel',
		'$Action!' => '$Action',
		'' => 'index'
    ];

    private static $allowed_actions = [
		"handlePanel",
		"sort",
		"setdefault",
		"applytoall"
    ];

    /**
     * Provides custom permissions to the Security section
     *
     * @return array
     */
    public function providePermissions()
    {
        $title = _t("Dashboard.MENUTITLE", LeftAndMain::menu_title('Dashboard'));
        return [
			"CMS_ACCESS_Dashboard" => [
				'name' => _t('Dashboard.ACCESS', "Access to '{title}' section", ['title' => $title]),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow use of the CMS Dashboard'
				)
			],
			"CMS_ACCESS_DashboardAddPanels" => [
				'name' => _t('Dashboard.ADDPANELS', "Add dashboard panels"),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow user to add panels to his/her dashboard'
				)
			],
			"CMS_ACCESS_DashboardConfigurePanels" => [
				'name' => _t('Dashboard.CONFIGUREANELS', "Configure dashboard panels"),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow user to configure his/her dashboard panels'
				),
			],
			"CMS_ACCESS_DashboardDeletePanels" => [
				'name' => _t('Dashboard.DELETEPANELS', "Remove dashboard panels"),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow user to remove panels from his/her dashboard'
				)
			]
        ];
    }

    /**
     * Handles a request for a {@link DashboardPanel} object.
     * Can be a new record or existing
     *
     * @param  HTTPRequest $r
     * @return HTTPResponse
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function handlePanel(HTTPRequest $r)
    {
        if ($r->param('ID') == "new") {
            $class = $r->getVar('type');
            if($class && class_exists($class) && is_subclass_of($class, DashboardPanel::class)) {
                /** @var DashboardPanel $panel */
                $panel = new $class();
				$member = Security::getCurrentUser();

                if($panel->canCreate($member)) {
                    $panel->MemberID = $member->ID;
                    $panel->Title = $panel->getLabel();
                    $panel->write();
                } else {
                    $panel = null;
                }
            }
        } else {
            $panel = DashboardPanel::get()->byID((int) $r->param('ID'));
        }

        if (isset($panel) && ($panel->canEdit() || $panel->canView())) {
            $requestClass = $panel->getRequestHandlerClass();

            /** @var RequestHandler $handler */
            $handler = Injector::inst()->create($requestClass, $this, $panel);
            return $handler->handleRequest($r);
        }

        return $this->httpError(404);
    }

    /**
     * A controller action that handles the reordering of the panels
     *
     * @param  HTTPRequest $r
     * @return void
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function sort(HTTPRequest $r)
    {
		$member = Security::getCurrentUser();
		$sort = $r->requestVar('dashboard-panel');

        if (!empty($member) && !empty($sort)) {
            foreach ($sort as $index => $id) {
                if ($panel = DashboardPanel::get()->byID((int) $id)) {
                    if ($panel->MemberID == $member->ID) {
                        $panel->SortOrder = $index;
                        $panel->write();
                    }
                }
            }
        }
    }

    /**
     * A controller action that handles setting the default dashboard configuration
     *
     * @param  HTTPRequest The current request
     * @return HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function setdefault(HTTPRequest $r)
    {
        /** @var DashboardPanel $panel */
        foreach (SiteConfig::current_site_config()->DashboardPanels() as $panel) {
            $panel->delete();
        }

        foreach (Security::getCurrentUser()->DashboardPanels() as $panel) {
            $clone = $panel->duplicate();
            $clone->MemberID = 0;
            $clone->SiteConfigID = SiteConfig::current_site_config()->ID;
            $clone->write();
        }

        return new HTTPResponse(_t(
			'Dashboard.SETASDEFAULTSUCCESS',
			'Success! This dashboard configuration has been set as the default for all new members.'
		));
    }

    /**
     * A controller action that handles the application of a dashboard configuration to all members
     *
     * @param  HTTPRequest The current request
     * @return HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function applytoall(HTTPRequest $r)
    {
		$curr_member = Security::getCurrentUser();
        $members = Permission::get_members_by_permission(["CMS_ACCESS_Dashboard", "ADMIN"]);

		foreach ($members as $member) {
            if ($member->ID == $curr_member->ID) {
				continue;
            }
            
            $member->DashboardPanels()->removeAll();

            /** @var DashboardPanel $panel */
            foreach (Security::getCurrentUser()->DashboardPanels() as $panel) {
                $clone = $panel->duplicate();                    
                $clone->MemberID = $member->ID;
                $clone->write();
            }            
        }

        return new HTTPResponse(_t(
			'Dashboard.APPLYTOALLSUCCESS',
			'Success! This dashboard configuration has been applied to all members who have dashboard access.'
		));
    }

    /**
     * Gets the current user's dashboard configuration
     *
     * @return DataList
     */
    public function BasePanels(): DataList
    {
        return Security::getCurrentUser()->DashboardPanels();
    }
    
    /**
     * Gets the current user's dashboard configuration
     *
     * @return DataList
     */
    public function Panels(): DataList
    {
        return Security::getCurrentUser()->DashboardPanels();
    }

    /**
     * Gets all the available panels that can be installed on the dashboard. All subclasses of
     * {@link DashboardPanel} are included
     *
     * @return ArrayList
     */
    public function AllPanels(): ArrayList
    {
        $set = ArrayList::create([]);
        $panels = ClassLoader::inst()
            ->getManifest()
            ->getDescendantsOf(DashboardPanel::class);

		if ($this->config()->excluded_panels) {
            $panels = array_diff($panels, $this->config()->excluded_panels);
        }

        foreach ($panels as $class) {
            $SNG = Injector::inst()->get($class);
            $SNG->Priority = Config::inst()->get($class, "priority");

            if($SNG->registered() == true) {
                $set->push($SNG);
            }
        }

        return $set->sort("Priority");
    }

    /**
     * A template accessor to check the ADMIN permission
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return Permission::check("ADMIN");
    }

    /**
     * Check the permission to make sure the current user has a dashboard
     *
     * @return bool
     */
    public function canView($member = null)
    {
        return Permission::check("CMS_ACCESS_Dashboard");
    }

    /** 
     * Check if the current user can add panels to the dashboard
     *
     * @return bool
     */
    public function canAddPanels()
    {
        return Permission::check("CMS_ACCESS_DashboardAddPanels");
    }

    /** 
     * Check if the current user can delete panels from the dashboard
     *
     * @return bool
     */
    public function canDeletePanels()
    {
        return Permission::check("CMS_ACCESS_DashboardDeletePanels");
    }

    /** 
     * Check if the current user can configure panels on the dashboard
     *
     * @return bool
     */
    public function canConfigurePanels()
    {
        return Permission::check("CMS_ACCESS_DashboardConfigurePanels");
    }
}
