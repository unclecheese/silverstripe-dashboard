<?php

namespace ilateral\SilverStripe\Dashboard\Extensions;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;

class DashboardItemEditForm extends Extension
{
    /**
     * @param Form $form
     */
    public function updateItemEditForm($form)
    {
        $id = $this->owner->request->getVar('ID');

        if(!empty($id)) {
            Injector::inst()->get(CMSMain::class)->setCurrentPageID($id);
            $form->Fields()->push(HiddenField::create('ID', '', $id));
        }
    }
}
