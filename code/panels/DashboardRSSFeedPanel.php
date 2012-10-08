<?php

/**
 * Defines the "RSS Feed" dashboard panel type
 *
 * @package Dashboard
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */

class DashboardRSSFeedPanel extends DashboardPanel {
	
	static $db = array (
		'FeedURL' => 'Text',
		'Count' => 'Int'
	);


	static $defaults = array (
		'Count' => 10,
		'DateFormat' => '%B %e, %Y'
	);


	static $icon = "dashboard/images/rss-feed.png";


	static $priority = 30;


	static $configure_on_create = true;
	



	public function getLabel() {
		return _t('Dashboard.RSSFEED','RSS Feed');
	}



	public function getDescription() {
		return _t('Dashboard.RSSFEEDDESCRIPTION','Adds an RSS feed from any public URL');
	}



	public function getConfiguration() {
		$fields = parent::getConfiguration();
		$fields->push(TextField::create("FeedURL", "Link to feed (include http://)"));
		$fields->push(DropdownField::create("DateFormat","Date format", array(
				'%B %e, %Y' => strftime('%B %e, %Y'),
				'%e %B, %Y' => strftime('%e %B, %Y')
			)));

		$fields->push(TextField::create("Count", _t('DashboardRecentEdits.COUNT','Number of pages to display')));
		return $fields;
	}



	/**
	 * Gets a list of all the items in the RSS feed given a user-provided URL, limit, and date format
	 *
	 * @return ArrayList
	 */
	public function RSSItems() {
		if(!$this->FeedURL) return false;
		$doc = new DOMDocument();
		$doc->load($this->FeedURL);
		$feeds = array();
		foreach ($doc->getElementsByTagName('item') as $node) {
			$itemRSS = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue
			);
			$feeds[] = $itemRSS;
		}
		$output = ArrayList::create(array());
		$count = 0;
		foreach($feeds as $item) {					
			if($count >= $this->Count) break;
			// Cast the Date
			$date = new Date('Date');
			$date->setValue($item['date']);

			// Cast the Title
			$title = new Text('Title');
			$title->setValue($item['title']);
			
			$output->push(new ArrayData(array(
				'Title'			=> $title,
				'Date'			=> $date->Format($this->DateFormat),
				'Link'			=> $item['link']
			)));
			$count++;
		}
		
				
		return $output;

	}
}