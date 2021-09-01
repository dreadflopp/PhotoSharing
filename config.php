<?PHP
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: config.php
 * Desc: Config file for Projekt
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
// This array holds settings used in the config class
$db_settings = [
    // The address of the PostgreSQL-server
    'host' => 'studentpsql.miun.se',

    // Username to login to the server
    'user' => 'mali1624',

    // Password to login to the server
    'password' => 's5qNHvOK5',

    // port to use
    'port' => '5432',

    // sslmode. Valid values are allow, prefer, require, disable, verify-ca, verify-full
    'sslmode' => 'require',

    // database name
    'dbname' => 'mali1624'
];

// This array holds the default links that should always be shown
$default_link_array = [
    'Start' => 'index.php'
];

// This variable holds the link to the image page
$image_page = 'images.php';

// This array holds the links to be displayed when a member with user role has logged in
$member_link_array = [
    'User page' => 'userpage.php'
];

// This array holds the links to be displayed when a member with admin role has logged in
$admin_link_array = [
    'Admin page' => 'admin.php'
];

