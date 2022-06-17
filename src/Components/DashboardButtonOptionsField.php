<?php

namespace ilateral\SilverStripe\Dashboard\Components;

use SilverStripe\Forms\OptionsetField;

class DashboardButtonOptionsField extends OptionsetField
{
    protected $size;

    public function FieldHolder($attributes = [])
    {
        return parent::FieldHolder($attributes);
    }

    public function setSize($size): self
    {
        $this->size = $size;
        return $this;
    }
}
