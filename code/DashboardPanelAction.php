<?php


/**
 * Defines the object that renders as a button in a dashboard panel
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardPanelAction extends ViewableData {
	

	/**
	 * @var string The link for this action button
	 */
	protected $Link;



	/**
	 * @var string The title (label) of the button
	 */
	protected $Title;



	/**
	 * @var string The type of action. Default is the plain button color. 
	 *				A value of "good" will provide a green "constructive" button
	 *
	 * @todo More button types?
	 */
	protected $Type;




	public function __construct($link, $title, $type = null) {
		$this->Link = $link;
		$this->Title = $title;
		$this->Type = $type;
	}




	/**
	 * Converts the simple type name into a real SS CSS class.
	 *
	 * @return string
	 */
	public function getUIClass() {
		switch($this->Type) {
			case "good":
				return "ss-ui-action-constructive";

			return "";
		}
	}



	/**
	 * Gets the HTML link
	 *
	 * @return string
	 */
	public function forTemplate() {
		return "<a href='$this->Link' class='dashboard-panel-action ss-ui-button {$this->getUIClass()}'>$this->Title</a>";
	}




	/**
	 * A template accessor used to render this object
	 *
	 * @return string
	 */
	public function Action() {
		return $this->forTemplate();
	}


}