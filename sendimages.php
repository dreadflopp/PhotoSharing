<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: sendimages.php
 * Desc: Responds to a request asking for images
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
include 'util.php';

// Start session
session_start();

// Get current category from session storage
if (isset($_SESSION['category']))
    $category = $_SESSION['category'];
else
    $category = false;

// prepare response with images
$response = [];

// Get current imagePage from session storage, if there is one
if (isset($_SESSION['imagePage'])) {

    // initialize member
    $member = new Member();
    $member->initialize($_SESSION['imagePage']);

    // Get images
    $images = $member->getImages();

    foreach ($images as $image) {

        // if category is set and true
        if ($category) {
            // if the image has the correct category
            if ($image->getCategory() == $category) {
                // image data, this add the image content to each image, if it wasn't already added
                $imageData = array('name' => $image->getName(), 'time' => $image->getTime(), 'dataUrl' => $image->getDataUrl());

                // add image data to response
                $response[] = $imageData;
            }
        }
        else if (!isset($_SESSION['category'])) {
            // image data, this add the image content to each image, if it wasn't already added
            $imageData = array('name' => $image->getName(), 'time' => $image->getTime(), 'dataUrl' => $image->getDataUrl());

            // if category isn't set, add all image data to response
            $response[] = $imageData;
        }
    }
}

// send response array. if no imagePage was set, this array will be empty
header('Content-Type: application/json');
echo json_encode($response);


?>