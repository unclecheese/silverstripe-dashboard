<?php


/**
 * Defines a {@link DashboardPanel} object that shows a summary of a ModelAdmin interface.
 * Provides create, and "view all" actions.
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package Dashboard
 */
class DashboardModelAdminPanel extends DashboardPanel {
	


	static $db = array (
		'Count' => 'Int',
		'ModelAdminClass' => 'Varchar',
		'ModelAdminModel' => 'Varchar'
	);



	static $defaults = array (
		'Count' => 10
	);



	static $configure_on_create = true;
	



	/**
	 * @var string Overrides the standard request handler to provide custom controller actions
	 */
	protected $requestHandlerClass = "DashboardModelAdmin_PanelRequest";




	public function getLabel() {
		return _t('Dashboard.MODELADMINPANELTITLE','Model Admin Editor');
	}




	public function getDescription() {
		return _t('Dashboard.MODELADMINPANELDESCRIPTION','Adds a summary view of a Model Admin section of the CMS');
	}




	/**
	 * Gets the actions for the top of the panel
	 *
	 * @return FieldList
	 */
	public function getPrimaryActions() {
		if(!$this->ModelAdminClass || !$this->ModelAdminModel) return false;
		$actions = parent::getPrimaryActions();		
		$actions->push(DashboardPanelAction::create(
			$this->CreateModelLink(), 
			sprintf(_t('Dashboard.CREATENEW','Create new %s'),$this->SingularModelName()), 
			"good"
		));
		return $actions;
	}




	/**
	 * Gets the actions for the bottom of the panel
	 *
	 * @return FieldList
	 */
	public function getSecondaryActions() {
		if(!$this->ModelAdminClass || !$this->ModelAdminModel) return false;
		$actions = parent::getPrimaryActions();		
		$actions->push(DashboardPanelAction::create(
			$this->ViewAllLink(), 
			sprintf(_t('Dashboard.VIEWALL','View all %s'),$this->PluralModelName())		
		));
		return $actions;
	}




	/**
	 * Gets the fields to configure the panel settings
	 *
	 * @return FieldList
	 */
	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$modeladmins = array ();
		$models = $this->getManagedModelsFor($this->ModelAdminClass);
		foreach(SS_ClassLoader::instance()->getManifest()->getDescendantsOf("ModelAdmin") as $class) {			
			$SNG = Injector::inst()->get($class);
			if($SNG instanceof TestOnly) continue;
			$title = Config::inst()->get($class, "menu_title", Config::INHERITED);			
			$modeladmins[$class] = $title ? $title : $class;
		}

		$fields->push(TextField::create("Count", _t('DashbordModelAdmin.COUNT','Number of records to display')));

		$fields->push(DropdownField::create("ModelAdminClass", _t('Dashboard.MODELADMINCLASS','Model admin tab'), $modeladmins)
			->addExtraClass('no-chzn')
			->setAttribute('data-lookupurl', $this->Link("modelsforpanel"))
			->setEmptyString("--- "._t('Dashboard.PLEASESELECT','Please select')." ---")
		);
		$fields->push(DropdownField::create("ModelAdminModel", _t('Dashboard.MODELADMINMODEL','Model'), $models)
			->addExtraClass('no-chzn')
		);

		return $fields;
	}




	/**
	 * Gets the link to view all records in ModelAdmin
	 *
	 * @return string
	 */
	public function ViewAllLink() {
		if($this->ModelAdminClass && $this->ModelAdminModel) {
			$url_segment = Injector::inst()->get($this->ModelAdminClass)->Link();
			return Controller::join_links($url_segment, $this->ModelAdminModel);
		}
	}




	/**
	 * Gets a link to create a new record in ModelAdmin
	 *
	 * @return string
	 */
	public function CreateModelLink() {
		if($this->ModelAdminClass && $this->ModelAdminModel) {
			$url_segment = Injector::inst()->get($this->ModelAdminClass)->Link();
			return Controller::join_links(				
				$url_segment, 
				$this->ModelAdminModel,
				"EditForm",
				"field",
				$this->ModelAdminModel,
				"item",
				"new"
			);
		}		
	}




	/**
	 * Gets the entries in the $managed_models array for the selected ModelAdmin class
	 *
	 * @param string The name of the ModelAdmin class
	 * @return array
	 */
	public function getManagedModelsFor($class = null) {
		$models = array ();
		if(!$class) return $models;

		$m = Config::inst()->get($class, "managed_models", Config::INHERITED);
		if(is_array($m)) {
			foreach($m as $managed_model) {
				$models[$managed_model] = $managed_model;
			}
		}
		return $models;
	}




	/**
	 * Gets the singular name for the chosen model
	 *
	 * @return string
	 */
	public function SingularModelName() {
		if($this->ModelAdminModel) {
			return Injector::inst()->get($this->ModelAdminModel)->i18n_singular_name();
		}
	}



	/**
	 * Gets the plural name for the chosen model
	 *
	 * @return string
	 */
	public function PluralModelName() {
		if($this->ModelAdminModel) {
			return Injector::inst()->get($this->ModelAdminModel)->i18n_plural_name();
		}
	}



	/**
	 * Gets the records in this ModelAdmin summary. Provides edit links and a title label
	 *
	 * @return ArrayList
	 */
	public function ModelAdminItems() {
		if($this->ModelAdminModel) {
			$records = DataList::create($this->ModelAdminModel)
				->limit($this->Count)
				->sort("LastEdited DESC");
			$url_segment = Injector::inst()->get($this->ModelAdminClass)->Link();
			$ret = ArrayList::create(array());
			foreach($records as $rec) {
				$ret->push(ArrayData::create(array(
					'EditLink' => Controller::join_links(
									$url_segment, 
									$this->ModelAdminModel,
									"EditForm",
									"field",
									$this->ModelAdminModel,
									"item",
									$rec->ID,
									"edit"
								),
					'Title' => $rec->getTitle()

				)));
			}
			return $ret;
		}
	}




	/**
	 * Overload the renderer to load requirements
	 *
	 * @return SSViewer
	 */
	public function PanelHolder() {
		Requirements::javascript("dashboard/javascript/dashboard-modeladmin-panel.js");
		return parent::PanelHolder();
	}



}




/**
 * This custom request handler allows controller actions that are unique to this panel type
 *
 * @author UncleCheese <unclecheese@leftandmain.com>
 * @package Dashbaord
 */
class DashboardModelAdmin_PanelRequest extends Dashboard_PanelRequest {



	/**
	 * Given a requested ModelAdmin subclass, get the managed models and provide a JSON response
	 *
	 * @param SS_HTTPRequest The request object
	 * @return string
	 */
	public function modelsforpanel(SS_HTTPRequest $r) {
		$panel = $r->requestVar('modeladminpanel');
		return Convert::array2json($this->panel->getManagedModelsFor($panel));
	}

	

}