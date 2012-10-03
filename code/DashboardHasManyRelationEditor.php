<?php


/** 
 * A custom FormField object used to manage has_many relations to a DashboardPanel.
 *
 * Note: All has_many relations must be descendants of {@link DashboardPanelDataObject}
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardHasManyRelationEditor extends FormField {



	static $url_handlers = array (
		'item/$ID' => 'handleItem',
		'$Action!' => '$Action',
	);



	/**
	 * @var DashboardPanel The {@link DashboardPanel} that owns this editor	 
	 */
	protected $controller;



	/** 
	 * @var string The name of the relationship that is managed by this editor
	 */
	protected $relationName;



	/**
	 * @var string The class of the related object
	 */
	protected $relationClass;	



	/**
	 * @var DataList The current list of records in the relation	 
	 */
	protected $records;


	
	/**
	 * @var string The template that renders the editor
	 */
	protected $template = "DashboardHasManyRelationEditor";



	
	/**
	 * The contructor for the editor. Sets member properties and checks for major errors.
	 *
	 * @param DashboardPanel The owner of the editor
	 * @param string The name of the relation managed by the editor
	 * @param string The class of the related object managed by the editor
	 * @param string The title (label) of the editor
	 */
	public function __construct($controller, $relationName, $relationClass, $title = null) {
		$this->controller = $controller;
		$this->relationName = $relationName;
		$this->relationClass = $relationClass;
		$this->title = $title;

		if(!$this->controller instanceof DashboardPanel) {
			user_error("DashboardHasManyRelationEditor must be passed an instance of DashboardPanel", E_USER_ERROR);
		}
		if(!$this->controller->has_many($this->relationName)) {
			user_error("DashboardHasManyRelationEditor must be passed a valid has_many relation for the panel. $relationName is not in the has_many array.", E_USER_ERROR);
		}
		if(!is_subclass_of($relationClass, "DashboardPanelDataObject")) {
			user_error("DashbordHasManyRelationEditor can only manage subclasses of DashboardPanelDataObject", E_USER_ERROR);
		}

		$this->records = $this->controller->$relationName();

		parent::__construct($relationName, $title);	

	}




	/**
	 * Sets the template of the editor
	 * 
	 * @param string The name of the template
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}



	/**
	 * Gets all of the items in the relation and provides edit/delete links for the table
	 *
	 * @return ArrayList
	 */
	public function Items() {
		$items = ArrayList::create(array());
		$labelField = Config::inst()->get($this->relationClass, "label_field", Config::INHERITED);
		foreach($this->records as $record) {
			$items->push(ArrayData::create(array(
				'Label' => $record->$labelField,
				'DeleteLink' => Controller::join_links($this->Link("item"),$record->ID,"delete"),
				'EditLink' => $this->Link("item/{$record->ID}"),
				'ID' => $record->ID
			)));
		}
		return $items;
	}




	/**
	 * Renders the form field
	 *
	 * @return SSViewer
	 */
	public function FieldHolder($attributes = array ()) {		
		return $this->renderWith($this->template);
	}




	/**
	 * Handles a request for a record in the table
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function handleItem(SS_HTTPRequest $r) {
		if($r->param('ID') == "new") {
			$item = Object::create($this->relationClass);
		}
		else {
			$item = DataList::create($this->relationClass)->byID((int) $r->param('ID'));
		}
		if($item) {
			$handler = DashboardHasManyRelationEditor_ItemRequest::create($this->controller->getDashboard(), $this->controller, $this, $item);
			return $handler->handleRequest($r, DataModel::inst());
		}		
		return $this->httpError(404);
	}



	
	/**
	 * A default controller action that renders the editor
	 *
	 * @param SS_HTTPRequest
	 * @return SSViewer
	 */
	public function index(SS_HTTPRequest $r) {
		return $this->FieldHolder();
	}




	/** 
	 * A controller action that handles the reordering of the list
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function sort(SS_HTTPRequest $r) {
		if($items = $r->getVar('item')) {
			foreach($items as $position => $id) {
				if($item = DataList::create($this->relationClass)->byID((int) $id)) {
					$item->SortOrder = $position;
					$item->write();
				}
			}
			return new SS_HTTPResponse("OK");
		}
	}



}



/**
 * Defines the {@link RequestHandler} object that handles an item belonging to the editor
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardHasManyRelationEditor_ItemRequest extends RequestHandler {



	/**
	 * @var Dashboard The Dashboard controller in the CMS
	 */
	protected $dashboard;



	/** 
	 * @var DashboardPanel The dashboard panel that owns the editor that is running the request
	 */
	protected $panel;



	/**
	 * @var DashboardHasManyRelationEditor The editor that is running the request
	 */
	protected $editor;



	/**
	 * @var DashboardPanelDataObject The object that was requested for edit/create/delete
	 */
	protected $item;




	static $url_handlers = array (
		'$Action!' => '$Action',
		'' => 'edit'
	);




	public function __construct($dashboard, $panel, $editor, $item) {
		$this->dashboard = $dashboard;
		$this->panel = $panel;
		$this->editor = $editor;
		$this->item = $item;
		parent::__construct();
	}




	/**
	 * An action that handles the edit of an object managed by the editor
	 *
	 * @param SS_HTTPRequest
	 * @return SSViewer
	 */
	public function edit(SS_HTTPRequest $r) {
		return $this->renderWith('DashboardHasManyRelationEditorDetailForm');
	}




	/**
	 * An action that handles the deletion of an object managed by the editor
	 *
	 * @param SS_HTTPRequest
	 * @return SSViewer
	 */
	public function delete(SS_HTTPRequest $r) {
		$this->item->delete();
		return new SS_HTTPResponse("OK");
	}




	/**
	 * A link to this item as managed by the editor belonging to a dashbaord panel
	 *
	 * @return string
	 */
	public function Link($action = null) {
		return Controller::join_links($this->editor->Link(),"item",$this->item->ID ? $this->item->ID : "new",$action);
	}



	/** 
	 * A link to refresh the editor
	 *
	 * @return string
	 */
	public function RefreshLink() {
		return $this->Link("edit");
	}




	/**
	 * Provides a form to edit or create an object managed by the editor
	 *
	 * @return Form
	 */
	public function DetailForm() {
		$form = Form::create(
			$this,
			"DetailForm",
			Injector::inst()->get($this->editor->relationClass)->getConfiguration(),
			FieldList::create(
				FormAction::create('saveDetail',_t('Dashboard.SAVE','Save'))
					->setUseButtonTag(true)
					->addExtraClass('ss-ui-action-constructive small'),
				FormAction::create('cancel',_t('Dashboard.CANCEL','Cancel'))
					->setUseButtonTag(true)
					->addExtraClass('small')
			)		
		);
		$form->setHTMLID("Form_DetailForm_".$this->panel->ID."_".$this->item->ID);
		$form->loadDataFrom($this->item);
		$form->addExtraClass('dashboard-has-many-editor-detail-form-form');
		return $form;
	}




	/**
	 * Saves the DetailForm and writes or creates a new object managed by the editor
	 *
	 * @param array The raw POST data from the form
	 * @param Form The DetailForm object
	 */
	public function saveDetail($data, $form) {
		$item = $this->item;
		if(!$item->exists()) {
			$item->DashboardPanelID = $this->panel->ID;
			$sort = DataList::create($item->class)->max("SortOrder");
			$item->SortOrder = $sort+1;
			$item->write();
		}
		$form->saveInto($item);
		$item->write();
		return new SS_HTTPResponse("OK");
	}


}