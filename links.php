<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: links.php
 * Desc: Responds to a request asking for links
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/

include 'util.php';

// Start session
session_start();

// Set response to the default links.
$response[] = Config::getInstance()->getDefaultLinkArray();

// If user is logged in
if (isset($_SESSION['validatedUser'])) {
    // instantiate member
    $member = new Member();
    $member->initialize($_SESSION['validatedUser']);

    // add links that should only be shown to members with member role
    if ($member->hasRole('member'))
        $response[] = Config::getInstance()->getMemberLinkArray();

    // add links that should only be shown to members with admin role
    if ($member->hasRole('admin'))
        $response[] = Config::getInstance()->getAdminLinkArray();
}

// Add links to all users image page
// get all usernames from the database
$usernames = Database::getInstance()->getAllMemberUsernames();

// sort usernames
sort($usernames);

// request links from the config class
$response[] = Config::getInstance()->getImagePagesLinks($usernames);

// Parameter for image page.
if ($_GET['links'] == 'images.php') {

    // if session variables for imagePage (=user to show category links for),
    // add category links
    if(isset($_SESSION['imagePage'])) {
        $username = $_SESSION['imagePage'];
        $member = new Member();
        $member->initialize($username);
        $categories = $member->getCategories();

        // get links from config class
        $response[] = Config::getInstance()->getCategoryLinks($username, $categories);
    }
}

// send links array as response
header('Content-Type: application/json');
echo json_encode($response);

