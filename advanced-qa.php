<?php
/*
Plugin Name: Advanced Q&A
Plugin URI: https://akismet.com/j
Description:  This plugin allows users to add projects
Version: 1.0.0
Author: Ivan Ion
Author URI: https://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: nimp-user-profile
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2018 Ion Ivan
*/



defined('ABSPATH') or exit('muiengat');
define('YWA_AQ_PLUGIN__FILE', __FILE__);
$thisPath = dirname(__FILE__);
require_once($thisPath . "/config.php");


if (file_exists($autoloadFile = $thisPath . '/vendor/autoload.php')) {
    require_once $autoloadFile;
}





function activateYWAQA()
{
    $activate = new YWA\Actions\Activate();
    $activate->run();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'activateYWAQA');


function uninstallYWAQA()
{
    $uninstall = new YWA\Actions\Uninstall();
    $uninstall->run();
}
register_uninstall_hook(__FILE__, 'uninstallYWAQA');

function add_custom_query_var($vars)
{
    $vars[] = "pula";
    return $vars;
}
add_filter('query_vars', 'add_custom_query_var');

$manager = new YWA\Helpers\Core\ActionsLoader();
$manager->loadAllActions();
