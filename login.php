<?PHP
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: login.php
 * Desc: Login page for Projekt
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
include 'util.php';

// main
session_start();

// boolean variables. Will be true if username/password is correct
$userCorrect = false;
$passwordCorrect = false;

// username, password
$username = $_GET['uname'];
$password = $_GET['psw'];

// check if username is correct
// try to initialize member
$member = new Member();
if ($member->initialize($username))
    $userCorrect = true;

// Check if password is correct
if($userCorrect && $member->validatePassword($password)) {
    $passwordCorrect = true;
}

// prepare response
// first add two boolean variables to an array, showing if given
// username and password is in the user_array
$response = array(
    'userCorrect' => $userCorrect,
    'passwordCorrect' => $passwordCorrect
);

// if username and password are correct, add username to session variable
if ($userCorrect && $passwordCorrect) {
    // set session variable
    $_SESSION['validatedUser'] = $username;

    // append an array with links to the response array. these link/links are only shown to logged in users.
    $response['memberLinks'] = Config::getInstance()->getMemberLinkArray();
}

// send response array
header('Content-Type: application/json');
echo json_encode($response);

?>
