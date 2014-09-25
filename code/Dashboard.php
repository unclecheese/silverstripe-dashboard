<?php


/** 
 * Defines the Dashboard interface for the CMS
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class Dashboard extends LeftAndMain implements PermissionProvider {

	

	private static $menu_title = "Dashboard";


	
	private static $url_segment = "dashboard";


	
	private static $menu_priority = 100;


	
	private static $url_priority = 30;

	
	
	private static $menu_icon = "dashboard/images/dashboard.png";
	
	
	
	private static $tree_class = 'DashboardPanel';


	
	private static $url_handlers = array (
		
		'panel/$ID' => 'handlePanel',
		'$Action!' => '$Action',
		'' => 'index'
	);

	public function init() {
		parent::init();
		Requirements::css("dashboard/css/dashboard.css");
		Requirements::javascript("dashboard/javascript/jquery.flip.js");
		Requirements::javascript("dashboard/javascript/dashboard.js");
	}

	private static $allowed_actions = array(
		"handlePanel",
		"sort",
		"setdefault",
		"applytoall"
	);

	
	/**
	 * Provides custom permissions to the Security section
	 *
	 * @return array
	 */
	public function providePermissions() {
		$title = _t("Dashboard.MENUTITLE", LeftAndMain::menu_title_for_class('Dashboard'));
		return array(
			"CMS_ACCESS_Dashboard" => array(
				'name' => _t('Dashboard.ACCESS', "Access to '{title}' section", array('title' => $title)),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow use of the CMS Dashboard'
				)				
			),
			"CMS_ACCESS_DashboardAddPanels" => array(
				'name' => _t('Dashboard.ADDPANELS', "Add dashboard panels"),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow user to add panels to his/her dashboard'
				)
			),
			"CMS_ACCESS_DashboardConfigurePanels" => array(
				'name' => _t('Dashboard.CONFIGUREANELS', "Configure dashboard panels"),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow user to configure his/her dashbaord panels'
				),
			),
			"CMS_ACCESS_DashboardDeletePanels" => array(
				'name' => _t('Dashboard.DELETEPANELS', "Remove dashboard panels"),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
				'help' => _t(
					'Dashboard.ACCESS_HELP',
					'Allow user to remove panels from his/her dashbaord'
				)
			)
		);
	}


	

	/** 
	 * Handles a request for a {@link DashboardPanel} object. Can be a new record or existing
	 *
	 * @param SS_HTTPRequest The current request
	 * @return SS_HTTPResponse
	 */
	public function handlePanel(SS_HTTPRequest $r) {
		if($r->param('ID') == "new") {
			$class = $r->getVar('type');
			if($class && class_exists($class) && is_subclass_of($class, "DashboardPanel")) {
				$panel = new $class();
				if($panel->canCreate()) {
					$panel->MemberID = Member::currentUserID();
					$panel->Title = $panel->getLabel();
					$panel->write();
				}
				else {
					$panel = null;
				}
			}
		}
		else {
			$panel = DashboardPanel::get()->byID((int) $r->param('ID'));
		}
		if($panel && ($panel->canEdit() || $panel->canView())) {
			$requestClass = $panel->getRequestHandlerClass();
			$handler = Object::create($requestClass, $this, $panel);				
			return $handler->handleRequest($r, DataModel::inst());

		}
		return $this->httpError(404);
	}




	/**
	 * A controller action that handles the reordering of the panels
	 *
	 * @param SS_HTTPRequest The current request
	 * @return SS_HTTPResponse
	 */
	public function sort(SS_HTTPRequest $r) {
		if($sort = $r->requestVar('dashboard-panel')) {
			foreach($sort as $index => $id) {
				if($panel = DashboardPanel::get()->byID((int) $id)) {
					if($panel->MemberID == Member::currentUserID()) {
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
	 * @param SS_HTTPRequest The current request
	 * @return SS_HTTPResponse
	 */
	public function setdefault(SS_HTTPRequest $r) {
		foreach(SiteConfig::current_site_config()->DashboardPanels() as $panel) {
			$panel->delete();
		}
		foreach(Member::currentUser()->DashboardPanels() as $panel) {
			$clone = $panel->duplicate();
			$clone->MemberID = 0;
			$clone->SiteConfigID = SiteConfig::current_site_config()->ID;
			$clone->write();
		}
		return new SS_HTTPResponse(_t('Dashboard.SETASDEFAULTSUCCESS','Success! This dashboard configuration has been set as the default for all new members.'));
	}




	/**
	 * A controller action that handles the application of a dashboard configuration to all members
	 *
	 * @param SS_HTTPRequest The current request
	 * @return SS_HTTPResponse
	 */
	public function applytoall(SS_HTTPRequest $r) {
		$members = Permission::get_members_by_permission("CMS_ACCESS_Dashboard");
		foreach($members as $member) {
			if($member->ID == Member::currentUserID()) continue;
			
			$member->DashboardPanels()->removeAll();
			foreach(Member::currentUser()->DashboardPanels() as $panel) {
				$clone = $panel->duplicate();					
				$clone->MemberID = $member->ID;
				$clone->write();
			}			
		}
		return new SS_HTTPResponse(_t('Dashboard.APPLYTOALLSUCCESS','Success! This dashboard configuration has been applied to all members who have dashboard access.'));
	}




	/**
	 * Gets the current user's dashboard configuration
	 *
	 * @return DataList
	 */
	public function BasePanels() {
		return Member::currentUser()->DashboardPanels();
	}
	
	/**
	 * Gets the current user's dashboard configuration
	 *
	 * @return DataList
	 */
	public function Panels() {
		return Member::currentUser()->DashboardPanels();
	}




	/**
	 * Gets all the available panels that can be installed on the dashboard. All subclasses of
	 * {@link DashboardPanel} are included
	 *
	 * @return ArrayList
	 */
	public function AllPanels() {
		$set = ArrayList::create(array());
		$panels = SS_ClassLoader::instance()->getManifest()->getDescendantsOf("DashboardPanel");
		if($this->config()->excluded_panels) {
			$panels = array_diff($panels,$this->config()->excluded_panels);
		}
		foreach($panels as $class) {
			$SNG = Injector::inst()->get($class);
			$SNG->Priority = Config::inst()->get($class, "priority", Config::INHERITED);
			if($SNG->registered() == true){
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
	public function IsAdmin() {
		return Permission::check("ADMIN");
	}



	/**
	 * Check the permission to make sure the current user has a dashboard
	 *
	 * @return bool
	 */
	public function canView($member = null) {
		return Permission::check("CMS_ACCESS_Dashboard");
	}



	/** 
	 * Check if the current user can add panels to the dashboard
	 *
	 * @return bool
	 */
	public function CanAddPanels() {
		return Permission::check("CMS_ACCESS_DashboardAddPanels");
	}



	/** 
	 * Check if the current user can delete panels from the dashboard
	 *
	 * @return bool
	 */
	public function CanDeletePanels() {
		return Permission::check("CMS_ACCESS_DashboardDeletePanels");
	}



	/** 
	 * Check if the current user can configure panels on the dashboard
	 *
	 * @return bool
	 */
	public function CanConfigurePanels() {
		return Permission::check("CMS_ACCESS_DashboardConfigurePanels");
	}




}



/**
 * Defines the {@link RequestHandler} object that is responsible for rendering dashboard panels
 * and processing their input.
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class Dashboard_PanelRequest extends RequestHandler {



	private static $url_handlers = array (
		'$Action!' => '$Action',
		'' => 'panel'

	);
	
	private static $allowed_actions = array(
		"panel",
		"delete",
		"ConfigureForm",
		"saveConfiguration"
	);	



	protected $dashboard;



	protected $panel;

	

	public function __construct(Dashboard $dashboard, DashboardPanel $panel) {
		$this->dashboard = $dashboard;		
		$this->panel = $panel;
		parent::__construct();
	}



	/**
	 * Gets the link to this request. Useful for rendering the nested Form. Also provides an easy
	 * "refresh" link to the panel that is managed by this request
	 *
	 * @return string
	 */
	public function Link() {
		return $this->dashboard->Link("panel/{$this->panel->ID}");
	}



	/** 
	 * Renders the panel in this request
	 *
	 * @param SS_HTTPRequest
	 * @return SSViewer
	 */
	public function panel(SS_HTTPRequest $r) {
		if($this->panel->canView()) {
			return $this->panel->PanelHolder();
		}
		return $this->httpError(403);
	}



	/**
	 * Delets the panel in this request
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function delete(SS_HTTPRequest $r) {
		if($this->panel->canDelete()) {
			$this->panel->delete();
			return new SS_HTTPResponse("OK");
		}
	}



	/**
	 * Gets the configuration form for this panel and handles the form input
	 *
	 * @return Form
	 */
	public function ConfigureForm() {
		$form = Form::create(
			$this,
			"ConfigureForm",
			$this->panel->getConfiguration(),
			FieldList::create(
				FormAction::create("saveConfiguration",_t('Dashboard.SAVE','Save'))
					->setUseButtonTag(true)
					->addExtraClass('ss-ui-action-constructive'),
				FormAction::create("cancel",_t('Dashboard.CANCEL','Cancel'))
					->setUseButtonTag(true)
			)
		);
		$form->loadDataFrom($this->panel);
		$form->setHTMLID("Form_ConfigureForm_".$this->panel->ID);
		$form->addExtraClass("configure-form");
		return $form;
	}



	
	/** 
	 * Processes the form input and writes the panel
	 *
	 * @param array The raw POST data from the form
	 * @param Form The ConfigurationForm
	 * @return SS_HTTPResponse
	 */
	public function saveConfiguration($data, $form) {
		$panel = $this->panel;
		$form->saveInto($panel);
		$panel->write();
	}






}
