<?php

/**
 * Defines the DashboardPanel type that shows Google Analytics data
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class DashboardGoogleAnalyticsPanel extends DashboardPanel {
	

	static $db = array (
		'AccountEmail' => 'Varchar(50)',
		'AccountPassword' => 'Varchar(50)',
		'ProfileID' => 'Varchar(20)',
		'DateFormat' => "Enum('mdy,dmy','dmy')",
		'DateRange' => "Enum('day,week,month,year','month')",
		'PathType' => "Enum('none,list,custom','none')",
		'CustomPath' => 'Varchar'
	);


	static $has_one = array (
		'SubjectPage' => 'SiteTree'
	);



	static $icon = "dashboard/images/google-analytics.png";



	static $configure_on_create = true;



	/**
	 * @var gapi A stored instantiation of the Google Analytics API object
	 */
	protected $gapi;




	/**
	 * A factory method that converts seconds to minutes. GA works in seconds.
	 *
	 * @param int The number of seconds
	 * @return string A human readable time value of minutes:seconds
	 */
	public static function seconds_to_minutes($seconds) {
	    $minResult = floor($seconds/60);
	    if($minResult < 10){$minResult = 0 . $minResult;}
	    $secResult = ($seconds/60 - $minResult)*60;
	    if($secResult < 10){$secResult = 0 . round($secResult);}
	    else { $secResult = round($secResult); }
	    return $minResult.":".$secResult;
	}



	/**
	 * Gets the label for this panel
	 *
	 * @return string
	 */
	public function getLabel() {
		return "Google Analytics";
	}



	/**
	 * Gets the title for this panel
	 *
	 * @return string
	 */
	public function getDescription() {
		return _t('Dashboard.GOOGLEDESCRIPTION','Displays a Google Analytics chart for a given page');
	}
	
	


	/**
	 * Gets the GAPI object. Creates if necessary.
	 *
	 * @return gapi
	 */
	public function api() {
		if(!$this->gapi) {
			$this->gapi = new gapi($this->AccountEmail, $this->AccountPassword);
		}
		return $this->gapi;
	}



	/**
	 * Gets the configuration FieldList for this panel
	 *
	 * @return FieldList
	 */
	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$pages = $this->getHierarchy(0);
		$fields->push(EmailField::create("AccountEmail",_t('Dashboard.GAACCOUNTEMAIL','Google account email')));
		$fields->push(PasswordField::create("AccountPassword",_t('Dashboard.GAACCOUNTPASSWORD','Google account password')));
		$fields->push(TextField::create("ProfileID",_t('Dashboard.GAACCOUNTPROFILE','Profile ID (located in the "Profile Settings" of Google Analytics)')));
		$fields->push(OptionsetField::create("PathType",_t('Dashboard.FILTERBYPAGE','Filter'),array(
				'none' => _t('Dashboard.NONESHOWALL','No filter. Show analytics for the entire site'),
				'list' => _t('Dashboard.PAGEINLIST','Filter by a specific page in the tree'),
				'custom' => _t('Dashboard.ACUSTOMPATH','Filter by a specific path')
			)));
		$fields->push(DropdownField::create("SubjectPageID",'',$pages)
			->addExtraClass('no-chzn')
			->setEmptyString("-- " . _t('Dashboard.PLEASESELECT','Please select')." --")
		);
		$fields->push(TextField::create("CustomPath", '')
			->setAttribute('placeholder','e.g. /about-us/contact')
		);
		$fields->push(DropdownField::create("DateFormat",_t('Dashboard.DATEFORMAT','Date format'),array(
				'dmy' => date('j M, Y'),
				'mdy' => date('M j, Y')
			))
			->addExtraClass('no-chzn')
		);
		$fields->push(DropdownField::create("DateRange", _t('Dashboard.DATERANGE','Date range'),array(
				'day' => _t('Dashboard.TODAY','Today'),
				'week' => _t('Dashboard.PREVIOUSSEVENDAYS','7 days'),
				'month' => _t('Dashboard.PREVIOUSTHIRTYDAYS','30 days'),
				'year' => _t('Dashboard.PREVIOUSYEAR','365 days')
			))
			->addExtraClass('no-chzn')
		);
		return $fields;

	}




	/**
	 * A recursive function that gets a hierarchy of the site tree for a dropdown. Cannot use
	 * the {@link TreeDropdownField} here due to UI constraints.
	 *
	 * @param int The ID of the root node of the tree
	 * @param int The level of depth in the hierarchy. Used to create a visial hierarchy.
	 * @return array
	 */
	protected function getHierarchy($parentID, $level = 0) {
		$options = array();
		$class = "SiteTree";
		$filter = array('ParentID' => $parentID);
		$children = DataList::create($class)->filter($filter);
		if($children->exists()) {
			foreach($children as $child) {
				$indent="";
				for($i=0;$i<$level;$i++) $indent .= "&nbsp;&nbsp;";				
				$text = $child->Title;
				$options[$child->ID] = empty($text) ? "<em>$indent Untitled</em>" : $indent.$text;
				$options += $this->getHierarchy($child->ID, $level+1);
			}
		}
		return $options;
	}




	/**
	 * Prevents the chart from rendering if critical values are not set
	 *
	 * @return bool
	 */
	public function isValid() {
		return $this->AccountEmail && $this->AccountPassword && $this->ProfileID;
	}




	/** 
	 * Gets a timestamp for the start date of reporting, based on user provided data
	 *
	 * @return int
	 */
	public function getStartDateStamp() {
		switch($this->DateRange) {
			case "day":
				return time();
			case "week":
				return strtotime("-7 days");
			case "month":
				return strtotime("-30 days");
			case "year":
				return strtotime("-1 year");
			default:
				return strtotime("-30 days");

		}
	}




	/** 
	 * Gets the path to the subject page of the analytics. Can be a native SiteTree object or a custom path
	 *
	 * @return string
	 */
	public function getPath() {
		if($this->PathType == "list") {
			return $this->SubjectPage()->exists() ? $this->SubjectPage()->Link() : "/";
		}
		elseif($this->PathType == "custom") {
			return $this->CustomPath;
		}
	}




	/**
	 * Loads the requirements before rendering the panel.
	 *
	 * @return SSViewer
	 */
	public function PanelHolder() {
		Requirements::javascript("https://www.google.com/jsapi");
		Requirements::javascript("https://www.google.com/uds/api/visualization/1.0/31f9974bf1146091ae320c1219fdf695/format+en,default,corechart.I.js");
		Requirements::javascript("dashboard/javascript/dashboard-google-analytics.js");
		Requirements::css("dashboard/css/dashboard-google-analytics.css");
		return parent::PanelHolder();
	}




	/** 
	 * Gets the title of the chart. A composite of date range and path
	 *
	 * @return string
	 */
	public function ChartTitle() {
		$stamp = $this->getStartDateStamp();
		$key = $this->DateFormat == "dmy" ? "j M, Y" : "M j, Y";
		$title = date($key, $stamp) . " - " . date($key);
		if($this->getPath()) {
			$title .= " ("._t('Dashboard.PATH','Path').": {$this->getPath()})";
		}
		else {
			$title .= " ("._t('Dashboard.ENTIRESITE','Entire site').")";
		}
		return $title;

	}



	/**
	 * Gets the result set for date/pageview pairs
	 *
	 * @return ArrayList
	 */
	public function ReportResults() {		
		if(!$this->isValid()) return false;
		$this->api()->requestReportData(
			$this->ProfileID, 
			array('date'),
			array('pageviews'), 
			'date', 
			$this->getPath() ? "pagePath == {$this->getPath()}" : null,			
			date('Y-m-d',$this->getStartDateStamp())
		);    

		$results = $this->api()->getResults();

		$set = ArrayList::create(array());
		if($results) {
			$datekey = $this->DateFormat == "mdy" ? "M j" : "j M";
			if($this->PathType == "none") {				
				$map = array ();
				foreach($results as $result) {
					$date = date($datekey, strtotime($result->getDate()));
					if(!isset($map[$date])) $map[$date] = 0;
					$map[$date] += $result->getPageViews();
				}
				foreach($map as $date => $views) {
					$set->push(ArrayData::create(array(
						'FormattedDate' => $date,
						'PageViews' => $views
					)));					
				}
			}
			else {
				foreach($results as $result) {				
					$set->push(ArrayData::create(array(
						'FormattedDate' => date($datekey, strtotime($result->getDate())),
						'PageViews' => $result->getPageViews()
					)));
				}
			}
		}		
		return $set;
	}



	/**
	 * Gets a result set of aggregate values for the given date range, e.g. total pageviews.
	 *
	 * @return ArrayList
	 */
	public function PageResults() {
		if(!$this->isValid()) return false;
		$this->api()->requestReportData(
			$this->ProfileID, 
			'pagePath', 
			array('pageviews', 'uniquePageviews', 'exitRate', 'avgTimeOnPage', 'entranceBounceRate'), 
			null, 
			$this->getPath() ? "pagePath == {$this->getPath()}" : null,
			date('Y-m-d',$this->getStartDateStamp())
		);
		$set = ArrayList::create(array());
		if(!$this->getPath()) {
			$metrics = $this->api()->getMetrics();
			if($metrics) {
				$set->push(ArrayData::create(array(
					'FormattedPageViews' => number_format($metrics['pageviews']),
					'FormattedUniquePageViews' => number_format($metrics['uniquePageviews']),
					'AverageMinutesOnPage' => self::seconds_to_minutes($metrics['avgTimeOnPage']),
					'BounceRate' => round($metrics['entranceBounceRate'],2)."%",
					'ExitRate' => round($metrics['exitRate'],2)."%"
				)));
			}
			return $set;			
		}
		$results = $this->api()->getResults();
		if($results) {			
			foreach($results as $result) {
				$set->push(ArrayData::create(array(
					'FormattedPageViews' => number_format($result->getPageViews()),
					'FormattedUniquePageViews' => number_format($result->getUniquePageViews()),
					'AverageMinutesOnPage' => self::seconds_to_minutes($result->getAvgtimeonpage()),
					'BounceRate' => round($result->getEntrancebouncerate(), 2).'%',
					'ExitRate' => round($result->getExitrate(), 2).'%'
				)));
			}

		}		
		return $set;

	}

}
