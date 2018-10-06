<?php

namespace UncleCheese\Dashboard;

$dir = basename(dirname(__FILE__));
if($dir != "dashboard") {
	user_error('Dashboard: Directory name must be "dashboard" (currently "'.$dir.'")',E_USER_ERROR);
}