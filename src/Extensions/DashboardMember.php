<?php

namespace ilateral\SilverStripe\Dashboard\Extensions;

use SilverStripe\ORM\DB;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;
use ilateral\SilverStripe\Dashboard\Panels\DashboardPanel;

/**
 * Decorates the Member object to work with the Dashboard interface
 *
 * @package Dashboard
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardMember extends DataExtension
{
    private static $db = [
       'HasConfiguredDashboard' => 'Boolean'
    ];

    private static $has_many = [
        'DashboardPanels' => DashboardPanel::class,
    ];

    /**
     * Removes the DashboardPanels tab from the Security section. Panels should not be managed there.
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName("DashboardPanels");
    }

    /**
     * Ensures that new members get the default dashboard configuration. Once it has been applied,
     * make sure this doesn't happen again, if for some reason a user insists on having an empty
     * dashboard.
     *
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function onAfterWrite()
    {
        /** @var Member */
        $owner = $this->getOwner();
        $config = SiteConfig::current_site_config();

        if (!$owner->HasConfiguredDashboard && !$owner->DashboardPanels()->exists()) {
            /** @var DashboardPanel $p */
            foreach ($config->DashboardPanels() as $p) {
                $clone = $p->duplicate();
                $clone->SiteConfigID = 0;
                $clone->MemberID = $owner->ID;
                $clone->write();
            }

            DB::query("UPDATE \"Member\" SET \"HasConfiguredDashboard\" = 1 WHERE \"ID\" = {$owner->ID}");
            $owner->flushCache();
        }
    }
}
