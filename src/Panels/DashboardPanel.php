<?php

namespace ilateral\SilverStripe\Dashboard\Panels;

use SilverStripe\Forms\Form;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Permission;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Injector\Injector;
use ilateral\SilverStripe\Dashboard\Dashboard;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use ilateral\SilverStripe\Dashboard\Components\DashboardButtonOptionsField;
use SilverStripe\Core\Config\Config;

/**
 * Defines the DashboardPanel dataobject. All dashboard panels must descend from this class.
 *
 * @package dashboard
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardPanel extends DataObject
{
    private static $table_name = 'DashboardPanel';

    private static $db = [
        'Title'     => 'Varchar(50)',
        'PanelSize' => "Enum(array('small','normal','large'),'normal')",
        'SortOrder' => 'Int'
    ];

    private static $has_one = [
        'Member'    => Member::class,
        'SiteConfig'=> SiteConfig::class
    ];

    private static $casting = [
        'Content'       => 'HTMLText',
        'ShowConfigure' => 'Boolean',
        'IsConfigured'  => 'Boolean',
        'PanelHolder'   => 'HTMLText',
        'Link'          => 'Varchar',
        'CreateLink'    => 'Varchar',
        'DeleteLink'    => 'Varchar'
    ];

    private static $default_sort = "SortOrder ASC";

    /**
     * @var string The size of the dashboard panel. Options: "small", "normal", and "large"
     */
    private static $size = "normal";

    /**
     * @var string Classname of font icon to use for the current panel
     */
    private static $font_icon = "dashboard";

    /**
     * @var int The "weight" of the dashboard panel when listed in the available panels.
     *            Higher is lower in the list.
     */
    private static $priority = 100;

    /**
     * @var bool Show the configure form after creating. Used for panels that require
     * configuration in order to show data
     */
    private static $configure_on_create = false;

    /**
     * @var string The name of the request handler class that the Dashboard controller
     * will use to communicate with a given panel
     */
    protected $requestHandlerClass = DashboardPanelRequest::class;

    /**
     * Allows the panel to be added
     *
     * @return string
     */
    public function registered()
    {
        if (is_bool(self::config()->enabled)) {
            return self::config()->enabled;
        }

        return true;
    }

    /**
     * Gets the request handler class
     *
     * @return string
     */
    public function getRequestHandlerClass()
    {
        return $this->requestHandlerClass;
    }

    /**
     * Essentially an abstract method. Every panel must have this method defined to provide
     * a title to the panel selection window
     *
     * @return string
     */
    public function getLabel(): string
    {
        return "";
    }

    /**
     * Essentially an abstract method. Every panel must have this method defined to provide
     * a description to the panel selection window
     *
     * @return string
     */
    public function getDescription(): string
    {
        return "";
    }

    /**
     * An accessor to the Dashboard controller
     *
     * @return Dashboard
     */
    public function getDashboard()
    {
        return Injector::inst()->get(Dashboard::class);
    }

    /**
     * A template accessor for the icon of this panel
     *
     * @return string
     */
    public function getFontIconClass(): string
    {
        $icon_class = Config::inst()
            ->get(static::class, 'font_icon');
        
        if (empty($icon_class)) {
            return "";
        }

        return "font-icon-" . $icon_class;
    }

    /**
     * Renders the inner contents of the panel. Similar to $Layout in pages.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->renderWith($this->getViewerTemplates());
    }

    /**
     * The link to this panel through the Dashboard controller
     *
     * @return string
     */
    public function getLink($action = null)
    {
        return Controller::join_links(
            $this->getDashboard()->Link("panel/{$this->ID}"),
            $action
        );
    }

    /**
     * The link to delete this panel from the dashboard
     *
     * @return string
     */
    public function getDeleteLink(): string
    {
        return $this->getLink("delete");
    }

    /**
     * The link to create this panel on the dashboard
     *
     * @return string
     */
    public function getCreateLink(): string
    {
        // TODO: Should the class name be escaped? At least Convert::raw2url()
        // is not suitable because it removes backslashes completely, not escaping them.
        return Controller::join_links(
            $this->getDashboard()->Link("panel/new"),
            "?type=" . static::class
        );
    }

    /**
     * Template accessor for the $configure_on_create boolean
     *
     * @return bool
     */
    public function getShowConfigure(): bool
    {
        return $this->config()->get('configure_on_create');
    }

    public function getIsConfigured(): bool
    {
        return true;
    }

    /**
     * Gets the {@link FieldList} object that is used to configure the fields on this panel.
     * Similar to getCMSFields().
     *
     * @return FieldList
     */
    public function getConfigurationFields(): FieldList
    {
        // Cannot be an empty string because SilverStripe\i18n\i18n::_t()
        // would yell that a default should be defined. So use a space as a workaround.
        $default_size_title = ' ';
        
        return FieldList::create(
            DashboardButtonOptionsField::create(
                "PanelSize",
                _t(Dashboard::class . '.PANELSIZE', $default_size_title),
                [
                'small' => '',
                'normal' => '',
                'large' => ''
                ]
            )->setSize("small"),
            TextField::create(
                "Title",
                _t(Dashboard::class . '.TITLE', 'Title')
            )
        );
    }

    /**
     * Gets the primary actions, which may appear in the top of the panel
     *
     * @return ArrayList
     */
    public function getPrimaryActions(): ArrayList
    {
        return ArrayList::create();
    }

    /**
     * Gets the secondary actions, which may appear in the bottom of the panel
     *
     * @return ArrayList
     */
    public function getSecondaryActions(): ArrayList
    {
        return ArrayList::create();
    }

    /**
     * Renders the entire panel. Similar to {@link FormField::FieldHolder()}
     *
     * @return string
     */
    public function getPanelHolder(): string
    {
        return $this->renderWith($this->getViewerTemplates('_Holder'));
    }

    /**
     * For backward compatibility to the old static $size property.
     *
     * @return string
     */
    public function getSize(): string
    {
        return $this->PanelSize;
    }

    /**
     * Gets the configuration form for this panel
     *
     * @return Form
     */
    public function Form()
    {
        return DashboardPanelRequest::create(
            $this->getDashboard(),
            $this
        )->ConfigureForm();
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check("CMS_ACCESS_DashboardAddPanels");
    }

    public function canDelete($member = null)
    {
        $m = $member ? $member : Security::getCurrentUser();
        return Permission::check("CMS_ACCESS_DashboardDeletePanels") && $this->MemberID == $m->ID;
    }

    public function canEdit($member = null)
    {
        $m = $member ? $member : Security::getCurrentUser();
        return Permission::check("CMS_ACCESS_DashboardConfigurePanels") && $this->MemberID == $m->ID;
    }

    public function canView($member = null)
    {
        $m = $member ? $member : Security::getCurrentUser();
        return Permission::check("CMS_ACCESS_Dashboard") && $this->MemberID == $m->ID;
    }
}
