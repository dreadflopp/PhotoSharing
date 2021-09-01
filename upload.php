<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: upload.php
 * Desc: Responds to a request to upload images
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/

include 'util.php';

// Start session
session_start();

// if user is logged in
if (isset($_SESSION['validatedUser'])) {
    // and if category is set and not empty
    if (isset($_POST['category']) && $_POST['category'] != '') {

        // initialize user
        $member = new Member();
        $member->initialize($_SESSION['validatedUser']);

        // Get file name
        $filename = $_FILES['file']['name'];

        // Get file type
        $mime = $_FILES['file']['type'];

        // check that mime type is one of the allowed mime types
        if ($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png' && $mime != 'image/tiff') {
            // mime check failed
            $response = array(
                'success' => false,
                //     'errorMessage' => 'Image type is not supported. Use gif, jpeg, png or tiff.'
                'message' => $mime
            );
        } else {
            // mime check success
            // get EXIF data from image
            $exif = exif_read_data($_FILES['file']['tmp_name']);

            // try to extract a timestamp from exif data
            if (isset($exif['DateTimeOriginal']))
                $timestamp = $exif['DateTimeOriginal'];
            else if (isset($exif['DateTime']))
                $timestamp = $exif['DateTime'];

            // Create an Image-object
            $image = new Image();

            // set image data
            $image->setName($_FILES['file']['name']);
            $image->setFileType($_FILES['file']['type']);
            $image->setCategory($_POST['category']);
            if (isset($timestamp))
                $image->setTime($timestamp); // if timestamp wasn't found in exif, use default time from image constructor
            $image->setContent(file_get_contents($_FILES['file']['tmp_name']));

            // calculate a checksum for the image. Important that this is done after content is set. Otherwise
            // the function fails.
            $image->calculateChecksum($_SESSION['validatedUser']);

            // if the user already has this image, set response
            if (Database::getInstance()->hasImage($member->getUsername(), $image->getChecksum())) {
                // create a response
                $response = array(
                    'success' => false,
                    'message' => 'You have this image in your library'
                );
            }


           else if (Database::getInstance()->getImageContent($image->getChecksum()) != false && $member->addImage($image)) {
                $imageData = 'data:' . $_FILES['file']['type'] . ';base64,' . Database::getInstance()->getImageContent($image->getChecksum());
                $response = array(
                    'success' => true,
                    'message' => 'Image uploaded.',
                    'imageData' => $imageData
                );
            }

            // add image to member
           else if ($member->addImage($image)) // success
                // check if the image already exist, if it does, get the content and attach to a response
                // create a response
                $response = array(
                    'success' => true,
                    'message' => 'Image uploaded.'
                );
            else
                // image not added. create a response
                $response = array(
                    'success' => false,
                    'message' => 'Image already exist.'
                );
        }
    }
    // if category isn't set
    else {
        $response = array(
            'success' => false,
            'message' => 'Category not sent, unable to save image'
        );
    }

} else
    $response = array(
        'success' => false,
        'message' => 'User not logged in, unable to save image'
    );

// send response
header('Content-Type: application/json');
echo json_encode($response);

