# The Dashboard Module for SilverStripe 3

The Dashbaord module provides a splash page for the CMS in SilverStripe 3 with configurable widgets that display relevant information. Panels can be created and extended easily. The goal of the Dashboard module is to provide users with a launchpad for common CMS actions such as creating specific page types or browsing new content.

## Screenshot
![Screenshot](http://dashboard.unclecheeseproductions.com/mysite/images/screenshot.png)


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

  static $db = array (
    'Count' => 'Int',
    'OnlyShowShipped' => 'Boolean'
  );
  
  
  static $icon = "mysite/images/dashboard-recent-orders.png";
  
  
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

## Try a demo

http://dashboard.unclecheeseproductions.com/admin

## Known issues

Has not been tested in Internet Explorer.
