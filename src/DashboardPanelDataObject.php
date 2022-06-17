<?php

namespace ilateral\SilverStripe\Dashboard;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use ilateral\SilverStripe\Dashboard\Panels\DashboardPanel;

/** 
 * A {@link DataObject} subclass that is required for use on a has_many relationship
 * on a DashboardPanel when being managed with a {@link DashboardHasManyRelationEditor}
 *
 * @package Dashboard
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardPanelDataObject extends DataObject
{
    private static $table_name = 'DashboardPanelDataObject';

    private static $db = [
        'SortOrder' => 'Int'
    ];

    private static $has_one = [
        'DashboardPanel' => DashboardPanel::class
    ];

    private static $default_sort = "SortOrder ASC";

    /**
     * @var string Like $summary_fields, but these objects only render one field in list view.
     */
    private static $label_field = "ID";

    /**
     * @return FieldList
     */
    public function getConfigurationFields(): FieldList
    {
        return FieldList::create();
    }
}
