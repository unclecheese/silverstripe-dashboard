<?php

namespace ilateral\SilverStripe\Dashboard;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataList;

/**
 * Defines the {@link RequestHandler} object that handles an item belonging to the editor
 *
 * @package Dashboard
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardHasManyRelationEditorItemRequest extends RequestHandler
{
    
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




    public function __construct($dashboard, $panel, $editor, $item)
    {
        $this->dashboard = $dashboard;
        $this->panel = $panel;
        $this->editor = $editor;
        $this->item = $item;
        parent::__construct();
    }




    /**
     * An action that handles the edit of an object managed by the editor
     *
     * @param  HTTPRequest
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function edit(HTTPRequest $r)
    {
        return $this->renderWith(__NAMESPACE__ . '\DashboardHasManyRelationEditorDetailForm');
    }




    /**
     * An action that handles the deletion of an object managed by the editor
     *
     * @param  HTTPRequest
     * @return HTTPResponse
     */
    public function delete(HTTPRequest $r)
    {
        $this->item->delete();
        return new HTTPResponse("OK");
    }




    /**
     * A link to this item as managed by the editor belonging to a dashboard panel
     *
     * @return string
     */
    public function Link($action = null)
    {
        return Controller::join_links($this->editor->Link(), "item", $this->item->ID ? $this->item->ID : "new", $action);
    }



    /** 
     * A link to refresh the editor
     *
     * @return string
     */
    public function RefreshLink()
    {
        return $this->Link("edit");
    }




    /**
     * Provides a form to edit or create an object managed by the editor
     *
     * @return Form
     */
    public function DetailForm()
    {
        $form = Form::create(
            $this,
            "DetailForm",
            Injector::inst()->get($this->editor->relationClass)->getConfiguration(),
            FieldList::create(
                FormAction::create('saveDetail', _t('Dashboard.SAVE', 'Save'))
                    ->setUseButtonTag(true)
                    ->addExtraClass('ss-ui-action-constructive small'),
                FormAction::create('cancel', _t('Dashboard.CANCEL', 'Cancel'))
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
     * @param  array $data The raw POST data from the form
     * @param  Form  $form The DetailForm object
     * @return HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function saveDetail($data, $form)
    {
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