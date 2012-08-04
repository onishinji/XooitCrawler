<?php

// Autoload core class
spl_autoload_register(function($class) {
      if (strrpos($class, 'Oni') !== FALSE) {
        include "lib/" . str_replace(
                array('_', "\\"), '/', $class
            ) . '.php';
      }
});

// CONFIG YOUR FRESH INSTALL OF PHPBB
$phpbb_root_path = __DIR__ . '/phpBB3/';

$phpEx = 'php';
define('IN_PHPBB', true);
require($phpbb_root_path . 'common.php');
require($phpbb_root_path . 'includes/functions_user.php');
require($phpbb_root_path . 'includes/functions_posting.php');

// GOUTE
require 'vendor/goutte.phar'; 

// French forum :)
date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'fr_FR', 'fra');

// no comment.
set_time_limit(0);

use Oni\Forum;

// Associate your new id of forum with old url
$forums = array(
  new Forum(11, 'http://cubesoflegend.xooit.fr/f15-Informations.htm')
);

// Url login
$loginUrl = 'http://cubesoflegend.xooit.fr/login.php';

// Must be filled
$phpAdmin = array('username' => 'XXX', 'password' => 'XXX', 'userId' => 2);


?>