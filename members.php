<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: members.php
 * Desc: Handles and responds to tasks regarding members: add and remove
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
include 'util.php';

// Start session
session_start();

// Get currently logged in user from session storage, if there is one.
// and check that the logged in user is an admin
if (isset($_SESSION['validatedUser'])) {
    // user is logged in, initialize member
    $member = new Member();
    $member->initialize($_SESSION['validatedUser']);

    // user is an admin
    if ($member->hasRole('admin')) {
        // if task is 'add' and all parameters set
        if (isset($_GET['task']) && $_GET['task'] == 'add' && isset($_GET['username']) && isset($_GET['password']) && isset($_GET['roles'])) {

            // change username string to lowercase
            $username = strtolower($_GET['username']);

            // Get roles array
            $roles = json_decode($_GET['roles']);

            // validate parameters
            // roles array is empty
            if (count($roles) == 0) {
                $response = array('success' => false, 'message' => 'You must choose at least one role for the member');
            } else {
                // if username doesn't validate (= it is empty or has non english or non alphanumeric characters)
                if ($username == '' || preg_match('[^a-z0-9]', $username)) {
                    // username didn't validate, set response
                    $response = array('success' => false, 'message' => 'username did not validate');
                } else {

                    //if password doesn't validate (=it is empty or has whitespace)
                    if ($_GET['password'] == '' || preg_match('/\s/', $_GET['password'])) {
                        // password didn't validate, set response
                        $response = array('success' => false, 'message' => 'password did not validate');
                    } else {
                        // Username and password has been validated
                        // try to add user to the database
                        $result = Database::getInstance()->addMember($username, $_GET['password'], $roles);
                        if ($result['success']) {
                            // Success, set response
                            $response = array('success' => true, 'message' => 'Member added');
                        } else {
                            // failed to add user to database, set response
                            $response = array('success' => false, 'message' => $result['message']);
                        }
                    }
                }
            }
        }


        // if task is 'remove' and username parameter is set
        else if (isset($_GET['task']) && $_GET['task'] == 'remove' && isset($_GET['username'])) {
            // change username string to lowercase
            $username = strtolower($_GET['username']);

            // delete member
            if ($username == $_SESSION['validatedUser']) {
                // member tried to delete itself, not allowed!. Set response
                $response = array('success' => false, 'message' => 'You can not delete yourself');
            }
            else if (Database::getInstance()->deleteMember($username))
                // member deleted. set response
                $response = array('success' => true, 'message' => 'Member deleted');
            else
                // member not deleted. set response.
                $response = array('success' => false, 'message' => 'Member not deleted');

        }



        // if task is 'fetch'
        else if (isset($_GET['task']) && $_GET['task'] == 'fetch') {
            // get all usernames
            $usernames = Database::getInstance()->getAllUsernames();

            // For each username, instantiate a member to get its roles.
            // Add member and role to an array and set as response
            foreach ($usernames as $username) {
                $member = new Member();
                $member->initialize($username);
                $roles = $member->getRoles();

                $response[$username] = $roles;
            }
        }



        // if task is 'roles'
        else if (isset($_GET['task']) && $_GET['task'] == 'roles') {
            // get all roles
            $response = Database::getInstance()->getAllRoles();
        }


        // unrecognized task or parameters
        else {
            $temp = '';
            foreach($_GET as $key => $value)
            {
                $temp = $temp . $key . ':' . $value . '.  ';
            }
            //$response = array('success' => false, 'message' => 'unrecognized parameters');
            $response = array('success' => false, 'message' => $temp);
        }

    } else // not admin
        $response = array('success' => false, 'message' => 'failed, you must be an admin to do this task');

} else // not logged in, handle
    $response = array('success' => false, 'message' => 'failed, you must be logged in and admin to do this task');

// send response
header('Content-Type: application/json');
echo json_encode($response);

