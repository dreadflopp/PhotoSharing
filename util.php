<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: util.php
 * Desc: Util file for Projekt
 *
 * Mattias Lindell
 * mali16246543
 * mali1624@student.miun.se
 ******************************************************************************/

/*******************************************************************************
 * autoload functions for Classes stored i directory classes
 * All classes must be saved i lower case to work and end whit class.php
 ******************************************************************************/
function my_autoloader($class) {
    $classfilename = strtolower($class);
    include 'classes/' . $classfilename . '.class.php';
}
spl_autoload_register('my_autoloader');

/*******************************************************************************
 * set debug true/false to change php.ini
 * To get more debug information when developing set to true,
 * for production set to false
 ******************************************************************************/
$debug = false;

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
}



