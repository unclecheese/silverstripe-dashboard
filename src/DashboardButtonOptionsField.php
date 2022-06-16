<?php

namespace ilateral\SilverStripe\Dashboard;

use SilverStripe\Forms\OptionsetField;
use SilverStripe\View\Requirements;

class DashboardButtonOptionsField extends OptionsetField {


	protected $Size;


	public function FieldHolder($attributes = []) {
//		Requirements::css("unclecheese/dashboard:css/dashboard-button-options.css");
		Requirements::javascript("unclecheese/dashboard:javascript/dashboard-button-options.js");
		return parent::FieldHolder($attributes);
	}




	public function setSize($size) {
		$this->Size = $size;
		return $this;
	}



	
}