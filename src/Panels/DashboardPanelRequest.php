<?php

namespace ilateral\SilverStripe\Dashboard\Panels;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use ilateral\SilverStripe\Dashboard\Dashboard;

/**
 * Defines the {@link RequestHandler} object that is responsible for rendering dashboard panels
 * and processing their input.
 *
 * @package Dashboard
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardPanelRequest extends RequestHandler
{
    private static $url_handlers = [
        '$Action!' => '$Action',
        '' => 'panel'
    ];

    private static $allowed_actions = [
        "panel",
        "delete",
        "ConfigureForm",
        "saveConfiguration"
    ];

    /**
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * @var DashboardPanel
     */
    protected $panel;

    public function __construct(Dashboard $dashboard, DashboardPanel $panel)
    {
        $this->dashboard = $dashboard;        
        $this->panel = $panel;
        parent::__construct();
    }

    public function Link($action = null)
    {
        return $this->getLink($action);
    }

    /**
     * Gets the link to this request. Useful for rendering the nested Form. Also provides an easy
     * "refresh" link to the panel that is managed by this request
     *
     * @param  null $action Not in use
     * @return string
     */
    public function getLink($action=null)
    {
        return $this->dashboard->Link("panel/{$this->panel->ID}");
    }

    /**
     * Renders the panel in this request
     *
     * @param  HTTPRequest
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function panel(HTTPRequest $r)
    {
        $panel = $this->panel;

        if(!$panel->canView()) {
            return $this->httpError(403);
        }
        
        return $panel->getPanelHolder();
    }

    /**
     * Deletes the panel in this request
     *
     * @param  HTTPRequest
     * @return HTTPResponse
     */
    public function delete(HTTPRequest $r)
    {
        $panel = $this->panel;

        if(!$panel->canDelete()) {
            return $this->httpError(403);
        }

        $panel->delete();
        return new HTTPResponse("OK");
    }

    /**
     * Gets the configuration form for this panel and handles the form input
     *
     * @return Form
     */
    public function ConfigureForm()
    {
        $panel = $this->panel;

        $form = Form::create(
            $this,
            "ConfigureForm",
            $panel->getConfigurationFields(),
            FieldList::create(
                FormAction::create("saveConfiguration", _t('Dashboard.SAVE', 'Save'))
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-primary'),
                FormAction::create("cancel", _t('Dashboard.CANCEL', 'Cancel'))
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-secondary')
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
     * @param  array $data The raw POST data from the form
     * @param  Form  $form The ConfigurationForm
     * @return HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function saveConfiguration($data, $form)
    {
        $panel = $this->panel;
        $form->saveInto($panel);
        $panel->write();
    }
}
