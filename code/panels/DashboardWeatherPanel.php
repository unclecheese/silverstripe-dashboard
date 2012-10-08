<?php


class DashboardWeatherPanel extends DashboardPanel {



	static $db = array (
		'Location' => 'Varchar',
		'Units' => "Enum('c,f','c')",
		'WeatherHTML' => 'HTMLText'
	);



	static $icon = "dashboard/images/weather.png";



	static $configure_on_create = true;



	public function getLabel() {
		return _t('Dashboard.WEATHER','Weather');
	}



	public function getDescription() {
		return _t('Dashboard.WEATHERDESCRIPTION','Shows the weather for a given location.');
	}



	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(TextField::create("Location", _t('Dashboard.LOCATION','Location')));
		$fields->push(DropdownField::create("Units",_t('Dashboard.UNITS','Units'), array(
				'c' => _t('Dashboard.CELCIUS','Celcius'),
				'f' => _t('Dashboard.FARENHEIT','Farenheit')
			))
			->addExtraClass("no-chzn")
		);
		return $fields;
	}



	public function Weather() {
		if(!$this->Location) return false;
		$rnd = time();
		$url = "http://query.yahooapis.com/v1/public/yql?format=json&rnd={$rnd}&diagnostics=true&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&q=";
		$query = urlencode("select * from weather.forecast where location in (select id from weather.search where query=\"{$this->Location}\") and u=\"{$this->Units}\"");
		$response = file_get_contents($url.$query);
		if($response) {
			$result = Convert::json2array($response);			
			if(!$result["query"]["results"]) {
				return false;
			}

			$days = ArrayList::create(array());	
			$channel = isset($result["query"]["results"]["channel"][0]) ? $result["query"]["results"]["channel"][0] : $result["query"]["results"]["channel"];
			$label = $channel["title"];
			$link = $channel["link"];

			$forecast = $channel["item"]["forecast"];
			for($i=0; $i<2; $i++) {
				$item = $forecast[$i];
				$days->push(ArrayData::create(array(
					'High' => $item["high"],
					'Low' => $item["low"],
					'ImageURL' => "http://l.yimg.com/a/i/us/we/52/".$item["code"].".gif",
					'Label' => $i == 0 ? _t('Dashboard.TODAY','Today') : _t('Dashboard.TOMORROW','Tomorrow')
				)));
			}

			$html = $this->customise(array(
				'Location' => str_replace("Yahoo! Weather - ","", $label),
				'Link' => $link,
				'Days' => $days
			))->renderWith('DashboardWeatherContent');					
			$this->WeatherHTML = $html;
			$this->write();			
			return $html;
		}			
		return $this->WeatherHTML;
	}




	public function PanelHolder() {
		Requirements::css("dashboard/css/dashboard-weather.css");
		return parent::PanelHolder();
	}

}