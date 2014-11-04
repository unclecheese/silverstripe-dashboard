# The Dashboard Module for SilverStripe 3

The Dashboard module provides a splash page for the CMS in SilverStripe 3 with configurable widgets that display relevant information. Panels can be created and extended easily. The goal of the Dashboard module is to provide users with a launchpad for common CMS actions such as creating specific page types or browsing new content.


## Screenshot & Videos
Images and videos about this module can be found [in this blog post.](http://www.leftandmain.com/silverstripe-screencasts/2012/10/03/dashboard-module-for-silverstripe-3/)


## Included panels
* Recently edited pages
* Recently uploaded files
* RSS Feed
* Quick links
* Section editor
* Google Analytics
* Weather

## Installation

* Install the contents of this repository in the root of your SilverStripe project in a directory named "dashboard".
* Run /dev/build?flush=1


## Creating a Custom Dashboard Panel

Dashboard panels have their own MVC architecture and are easy to create. In this example, we'll create a panel that displays recent orders for an imaginary website. The user will have the option to configure the panel to only show orders that are shipped.

### Creating the model

First, create a class for the panel as a descendant of DashboardPanel. We'll include the database fields that define the configurable properties, and create the configuration fields in the getConfiguration() method.


**mysite/code/RecentOrders.php**
```php
<?php

class DashboardRecentOrdersPanel extends DashboardPanel {

  private static $db = array (
    'Count' => 'Int',
    'OnlyShowShipped' => 'Boolean'
  );
  
  
  private static $icon = "mysite/images/dashboard-recent-orders.png";
  
  
  public function getLabel() {
    return _t('Mysite.RECENTORDERS','Recent Orders');
  }
  
  
  public function getDescription() {
    return _t('Mysite.RECENTORDERSDESCRIPTION','Shows recent orders for this fake website.');
  }
  
  
  public function getConfiguration() {
    $fields = parent::getConfiguration();
    $fields->push(TextField::create("Count", "Number of orders to show"));
    $fields->push(CheckboxField::create("OnlyShowShipped","Only show shipped orders"));
    return $fields;
  }
  
  
  
  public function Orders() {
    $orders = Order::get()->sort("Created DESC")->limit($this->Count);
    return $this->OnlyShowShipped ? $orders->filter(array('Shipped' => true)) : $orders;
  }
}

```

### Creating the Template

The panel object will look for a template that matches its class name.

**mysite/templates/Includes/DashboardRecentOrdersPanel.ss**
```html
<div class="dashboard-recent-orders">
  <ul>
    <% loop Orders %>
      <li><a href="$Link">$OrderNumber ($Customer.Name)</a></li>
    <% end_loop %>
  </ul>
</div>
```

Run /dev/build?flush=1, and you can now create this dashboard panel in the CMS.

### Customizing with CSS

The best place to inject CSS and JavaScript requirements is in the inherited PanelHolder() method of the DashboardPanel subclass.

**mysite/code/DashboardRecentOrdersPanel.php**
```php
<?php
public function PanelHolder() {
  Requirements::css("mysite/css/dashboard-recent-orders.css");
  return parent::PanelHolder();
}
```



### Adding a chart to visualize data

The Dashboard module comes with an API for creating charts using the Google API.

**mysite/code/DashboardRecentOrdersPanel.php**
```php
<?php

  public function Chart() {
		$chart = DashboardChart::create("Order history, last 30 days", "Date", "Number of orders");
		$result = DB::query("SELECT COUNT(*) AS OrderCount, DATE_FORMAT(Date,'%d %b %Y') AS Date FROM \"Order\" GROUP BY Date");
		if($result) {
			while($row = $result->nextRecord()) {
				$chart->addData($row['Date'], $row['OrderCount']);
			}
		}
		return $chart;
	}

```

**mysite/code/DashboardRecentOrdersPanel.ss**
```html
$Chart
```

### Custom templates for ModelAdmin / GridField panels

You can create your own templates for either of these panel types which will override the default templates. Due to the naming structure the custom templates will be specific to that partiular panel, thus you can have a seperate template for each ModelAdmin / GridField panel.

You can access all the properties of your model in the template as normal along with a EditLink method which will contain the CMS edit link for that item.


For model admin panels, create a templated called DashboardModelAdminPanel\_**ModelAdminClass**\_**ModelAdminModel**.ss and place it in your _mysite/templates/Includes folder_. 
eg;
**DashboardModelAdminPanel\_MyAdmin\_Product.ss**

A gridfield panel uses a similar convention, DashboardGridFieldPanel\_**PageClassName**\_**GridFieldName**.ss

eg;
**DashboardGridFieldPanel\_ContactPage\_Submissions.ss**


