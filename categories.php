<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: categories.php
 * Desc: Handles and responds to tasks regarding categories: add, fetch and remove
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
include 'util.php';

// Start session
session_start();

// Get currently logged in user from session storage, if there is one
if (isset($_SESSION['validatedUser'])) {
    // user is logged in, initialize member
    $member = new Member();
    $member->initialize($_SESSION['validatedUser']);
}



// if user is logged in and the task is to fetch categories
if (isset($member) && $_GET['task'] === 'fetch') {
    // Fetch categories
    $response = $member->getCategories();
}



// if user is logged in and the task is to add a new category
else if (isset($member) && $_GET['task'] === 'add') {

    // if category to add is set and not empty
    if (isset($_GET['category']) && $_GET['category'] !== '') {

        // change the category string to lowercase
         $category = strtolower($_GET['category']);

        // Validate category, since it only has been validated client side
        // if category contains whitespace
        if (preg_match('/\s/', $category)) {
            $response= array('success' => false, 'message' => 'Only one word is allowed for categories');
        }
        // if category contains non alphabetic english character
        else if (preg_match('[^a-zA-Z]', $category)) {
            $response= array('success' => false, 'message' => 'Use english alphabetic characters only (a-z, A-Z)');
        }

        // if category string length is over 20 characters
        else if (strlen($category) > 20) {
            $response= array('success' => false, 'message' => 'Category can not be longer than 20 characters');
        }
        // Check if user already has the category
        else if ($member->hasCategory($category)) {
            // set response
            $response = array('success' => false, 'message' => 'The category already exist');
        }

        // user doesn't have category and it validates
        else {
            // and if category is successfully added
            $result = $member->addCategory($category);
            if ($result['success'] == true)
                // set response
                $response = array('success' => true, 'message' => 'Category added');
            else
                // category not added, set response
                $response= array('success' => false, 'message' => $result['message']);
        }
    }
    else {
        $response = array('success' => false, 'message' => 'Category not sent');
    }
}



// if user is logged in and the task is to remove a category
else if (isset($member) && $_GET['task'] === 'remove') {

    // if category to remove is set and not empty
    if (isset($_GET['category']) && $_GET['category'] !== '') {
        // change the category string to lowercase
        $category = strtolower($_GET['category']);

        // and if category is successfully remove
        $result = $member->removeCategory($category);
        if ($result['success']) {
            // response is true
            $response = array('success' => true, 'message' => 'Category removed');
        }
        else {
            // db reported an error
            $response = array('success' => false, 'message' => $result['message']);
        }
    }
    else
        $response = array('success' => false, 'message' => 'Category not sent');
}



// send response array.
header('Content-Type: application/json');
echo json_encode($response);

