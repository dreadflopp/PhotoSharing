<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: logout.php
 * Desc: Logout page for Projekt
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
// Initialize the session.
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// send true to tell that the logout process is finished on the server side
header('Content-Type: application/json');
echo json_encode(true);
?>

