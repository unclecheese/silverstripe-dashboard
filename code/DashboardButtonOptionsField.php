<?php


class DashboardButtonOptionsField extends OptionsetField {


	protected $Size;



	public function FieldHolder($attributes = array ()) {		
//		Requirements::css("dashboard/css/dashboard-button-options.css");
		Requirements::javascript("dashboard/javascript/dashboard-button-options.js");
		return parent::FieldHolder($attributes);
	}




	public function setSize($size) {
		$this->Size = $size;
		return $this;
	}



	
}