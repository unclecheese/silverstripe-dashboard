<?php

namespace UncleCheese\Dashboard;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;

/**
 * A custom FormField object used to manage has_many relations to a DashboardPanel.
 *
 * Note: All has_many relations must be descendants of {@link DashboardPanelDataObject}
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardHasManyRelationEditor extends FormField {

	private static $allowed_actions = [
		"handleItem"
	];

	private static $url_handlers = [
		'item/$ID' => 'handleItem',
		'$Action!' => '$Action',
	];



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
	protected $template = "UncleCheese\Dashboard\DashboardHasManyRelationEditor";



	
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
		if(!is_subclass_of($relationClass, DashboardPanelDataObject::class)) {
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
		$items = ArrayList::create([]);
		$labelField = Config::inst()->get($this->relationClass, "label_field");
		foreach($this->records as $record) {
			$items->push(ArrayData::create([
				'Label' => $record->$labelField,
				'DeleteLink' => Controller::join_links($this->Link("item"),$record->ID,"delete"),
				'EditLink' => $this->Link("item/{$record->ID}"),
				'ID' => $record->ID
			]));
		}
		return $items;
	}




	/**
	 * Renders the form field
	 *
	 * @return \SilverStripe\ORM\FieldType\DBHTMLText
	 */
	public function FieldHolder($attributes = []) {
		return $this->renderWith($this->template);
	}




	/**
	 * Handles a request for a record in the table
	 *
	 * @param HTTPRequest
	 * @return HTTPResponse
	 * @throws \SilverStripe\Control\HTTPResponse_Exception
	 */
	public function handleItem(HTTPRequest $r) {
		if($r->param('ID') == "new") {
			$item = Injector::inst()->create($this->relationClass);
		}
		else {
			$item = DataList::create($this->relationClass)->byID((int) $r->param('ID'));
		}
		if($item) {
			$handler = DashboardHasManyRelationEditorItemRequest::create($this->controller->getDashboard(), $this->controller, $this, $item);
			return $handler->handleRequest($r);
		}		
		return $this->httpError(404);
	}



	
	/**
	 * A default controller action that renders the editor
	 *
	 * @param HTTPRequest
	 * @return \SilverStripe\ORM\FieldType\DBHTMLText
	 */
	public function index(HTTPRequest $r) {
		return $this->FieldHolder();
	}
	
	
	/**
	 * A controller action that handles the reordering of the list
	 *
	 * @param HTTPRequest
	 * @return HTTPResponse
	 * @throws \SilverStripe\ORM\ValidationException
	 */
	public function sort(HTTPRequest $r) {
		if($items = $r->getVar('item')) {
			foreach($items as $position => $id) {
				if($item = DataList::create($this->relationClass)->byID((int) $id)) {
					$item->SortOrder = $position;
					$item->write();
				}
			}
			return new HTTPResponse("OK");
		}
	}



}



/**
 * Defines the {@link RequestHandler} object that handles an item belonging to the editor
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardHasManyRelationEditorItemRequest extends RequestHandler {
	
	private static $allowed_actions = [
		"edit",
		"delete",
		"DetailForm"
	];


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




	private static $url_handlers = [
		'$Action!' => '$Action',
		'' => 'edit'
	];




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
	 * @param HTTPRequest
	 * @return \SilverStripe\ORM\FieldType\DBHTMLText
	 */
	public function edit(HTTPRequest $r) {
		return $this->renderWith('DashboardHasManyRelationEditorDetailForm');
	}




	/**
	 * An action that handles the deletion of an object managed by the editor
	 *
	 * @param HTTPRequest
	 * @return HTTPResponse
	 */
	public function delete(HTTPRequest $r) {
		$this->item->delete();
		return new HTTPResponse("OK");
	}




	/**
	 * A link to this item as managed by the editor belonging to a dashboard panel
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
	 * @param array $data The raw POST data from the form
	 * @param Form $form The DetailForm object
	 * @return HTTPResponse
	 * @throws \SilverStripe\ORM\ValidationException
	 */
	public function saveDetail($data, $form) {
		$item = $this->item;
		if(!$item->exists()) {
			$item->DashboardPanelID = $this->panel->ID;
			$sort = DataList::create($item->ClassName)->max("SortOrder");
			$item->SortOrder = $sort+1;
			$item->write();
		}
		$form->saveInto($item);
		$item->write();
		return new HTTPResponse("OK");
	}


}