<?php
/**
 * QSF Portal
 * Copyright (c) 2006-2012 The QSF Portal Development Team
 * http://code.google.com/p/qsfportal/
 *
 * Based on:
 *
 * Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 * http://code.google.com/p/quicksilverforums/
 * 
 * MercuryBoard
 * Copyright (c) 2001-2006 The Mercury Development Team
 * https://github.com/markelliot/MercuryBoard
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 **/

define('QUICKSILVERFORUMS', true);
define('QSF_INSTALLER', 1); // Used in query files

error_reporting(E_ALL);

require_once( "../settings.php" );
$set['include_path'] = '..';
require_once $set['include_path'] . '/defaultutils.php';
require_once $set['include_path'] . '/global.php';

function execute_queries($queries, $db)
{
	foreach ($queries as $query)
	{
		$db->query($query);
	}
}

function check_writeable_files()
{
	// Need to check to see if the necessary directories are writeable.
	$writeable = true;
	$fixme = '';

	if(!is_writeable('../attachments')) {
		$fixme .= "../attachments/<br />";
		$writeable = false;
	}
	if(!is_writeable('../avatars/uploaded')) {
		$fixme .= "../avatars/uploaded/<br />";
		$writeable = false;
	}
	if(!is_writeable('../emoticons')) {
		$fixme .= "../emoticons/<br />";
		$writeable = false;
	}
	if(!is_writeable('../packages')) {
		$fixme .= "../packages/<br />";
		$writeable = false;
	}
	if(!is_writeable('../skins')) {
		$fixme .= "../skins/<br />";
		$writeable = false;
	}
	if(!is_writeable('../rss')) {
		$fixme .= "../rss/<br />";
		$writeable = false;
	}
	if(!is_writeable('../downloads')) {
		$fixme .= "../downloads/<br />";
		$writeable = false;
	}
	if(!is_writeable('../updates')) {
		$fixme .= "../updates/<br />";
		$writeable = false;
	}

	if( !$writeable ) {
		echo "The following files and directories are missing or not writeable. Some functions will be impaired unless these are changed to 0777 permission.<br /><br />";
                echo "<span style='color:red'>" . $fixme . "</span>";
	} else {
		echo "<span style='color:green'>Directory permissions are all OK.</span>";
	}
}

if (!isset($_GET['step'])) {
	$step = 1;
} else {
	$step = $_GET['step'];
}

$mode = '';
if (!isset($_GET['mode'])) {
	$mode = '';
} else {
	$mode = $_GET['mode'];
}

if ($mode) {
	require $set['include_path'] . '/install/' . $mode . '.php';
	$qsf = new $mode;
} else {
	$qsf = new qsfglobal;
}
	$qsf->sets = $set;
	$qsf->modules = $modules;
	$qsf->self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'index.php';

	$failed = false;

	$php_version = PHP_VERSION;
	$os = defined('PHP_OS') ? PHP_OS : 'unknown';
	$safe_mode = get_cfg_var('safe_mode') ? 'on' : 'off';
	$register_globals = get_cfg_var('register_globals') ? 'on' : 'off';
	$server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown';

	if( version_compare( PHP_VERSION, "5.3.0", "<" ) ) {
		echo 'Your PHP version is ' . PHP_VERSION . '.<br />Currently only PHP 5.3.0 and higher are supported.';
		$failed = true;
	}

	$db_fail = 0;
	$mysql = false;
	$mysqli = false;

	if (!extension_loaded('mysql'))
		$db_fail++;
	else
		$mysql = true;

	if (!extension_loaded('mysqli')) {
		$db_fail++;
	} else {
		if( mysqli_get_client_version() >= 40103 )
			$mysql = false;
			$mysqli = true;
	}

	if ( $db_fail > 1 )
	{
		if ($failed) { // If we have already shown a message, show the next one two lines down
			echo '<br /><br />';
		}

		echo 'Your PHP installation does not support MySQL or MySQLi.';
		$failed = true;
	}

	if ($failed) {
		echo "<br /><br /><b>To run {$qsf->name} and other advanced PHP software, the above error(s) must be fixed by you or your web host.</b>";
		exit;
	}

	if ($mysql) {
		$mysql_client = '<li>MySQL Client: (' . mysql_get_client_info() . ')</li><hr />';
	} else {
		$mysql_client = '';
	}

	if ($mysqli) {
		$mysqli_client = '<li>MySQLi Client: (' . mysqli_get_client_info() . ')</li><hr />';
	} else {
		$mysqli_client = '';
	}

	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">
<head>
 <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
 <title>{$qsf->name} Installer</title>
 <link rel=\"stylesheet\" type=\"text/css\" href=\"../skins/default/styles.css\" />
 <style type=\"text/css\">
  h1.title {background: #292929; text-align:center; font: 18px Arial, sans-serif; color: #c0c0c0; margin: 0px -7px 4px -2px; padding: 3px 4px 2px 7px; border: 1px solid #3e3e3e; border-left: 3px double #3e3e3e;}
  #blocks ul {margin:0;	padding:0;}
  #blocks li {list-style-type: none;}
 </style>
</head>

<body>
 <div id='container'>
  <div id='header'>
   <div id='company'>
    <div class='title'></div>
   </div>
  </div>

  <h1 class='title'>{$qsf->name} Installer {$qsf->version}</h1>

  <div id='blocks'>
   <div class='block'>
    <ul>
     <li>PHP Version: $php_version</li><hr />
     <li>Operating System: $os</li><hr />
     <li>Safe mode: $safe_mode</li><hr />
     <li>Register globals: $register_globals</li><hr />
     <li>Server Software: $server</li><hr />
     $mysql_client
     $mysqli_client
    </ul>
   </div>
  </div>

  <div id='main'>";

	switch( $mode )
	{
		default:
			include "choose_install.php";
			break;
		case 'new_install':
			$qsf->install_board($step, $mysqli);
			break;
		case 'upgrade':
			$qsf->upgrade_board($step);
			break;
		case 'convert':
			$qsf->convert_board($step, $mysqli);
			break;
	}

	echo "   </div>
   <div id='bottom'>&nbsp;</div>
  </div>
  <div id='footer'>
   <a href='http://www.qsfportal.com'>{$qsf->name}</a> {$qsf->version} &copy; 2005-2011 The {$qsf->name} Development Team<br />
  </div>
 </body>
</html>";
?>