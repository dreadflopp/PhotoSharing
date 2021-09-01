<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: userpage.php
 * Desc: Userpage page for Projekt
 *
 * Anders Student
 * ansu6543
 * ansu6543@student.miun.se
 ******************************************************************************/

include 'util.php';

$title = "User page";

// default value of $username
$username = "No User is logged in!";

// Check that a user has logged in and has member role, if not redirect to start page
session_start();
if (isset($_SESSION['validatedUser'])) {


    // instantiate member and check if it has member role
    $member = new Member();
    $member->initialize($_SESSION['validatedUser']);

    if ($member->hasRole('member'))
        // set username
        $username = $_SESSION['validatedUser'];

}
if (!isset($_SESSION['validatedUser']) || !$member->hasRole('member')){
    header("Location: index.php"); /* Redirect browser */
    exit;
}

/*******************************************************************************
 * HTML section starts here
 ******************************************************************************/
?>
<!DOCTYPE html>
<html lang="sv-SE">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?></title>
    <link rel="stylesheet" href="css/style.css"/>
    <script src="js/main.js"></script>
</head>
<body>
<!-- header -->
<header>
    <!-- div to style content and background of header with different widths -->
    <div>
        <!-- logo and title -->
        <img src="img/logotype.png" alt="logotype" class="logo"/>
        <h1><?php echo $title ?></h1>

        <!-- login form -->
        <form id="login" <?php if(isset($_SESSION['validatedUser'])): ?>style="display: none"<?php endif ?>>
            <span id="msgLogin" class="message"></span>
            <input type="text" placeholder="Enter username" name="uname" id="uname"
                   required maxlength="10" autocomplete="off">
            <input type="password" placeholder="Enter Password" name="psw" id="psw"
                   required>
            <input type="button" id="loginButton" value="Login">
        </form>

        <!-- logout form -->
        <form id="logout" <?php if(isset($_SESSION['validatedUser'])): ?>style="display: inline"<?php endif ?>>
            <span id="username"><?php echo $username ?></span>
            <input type="button" id="logoutButton" value="Logout">
        </form>
    </div>
</header>


<main>
    <!-- menu -->
    <aside>
        <h2>Menu</h2>
    </aside>
    <!-- wrapper to style sections -->
    <div class="wrapper">
        <section>
            <h2>Description</h2>
            This is the user page. Here you can upload images to your page. All images are public and can be seen by everyone.
            You must choose a category for your image. The available categories are created by you. You can easily create new ones using the
            form below. If you haven't created any categories, create one to show the upload form.
        </section>
        <section>
            <h2>Image uploader</h2>
            <form id="createCategory">
                <label for="ctgField">1. (optional) Create a category.</label>
                <input type="text" placeholder="new category" name="ctgField" id="ctgField">
                <input type="button" value="Create category" name="createCtgButton" id="createCtgButton">
                <p id="msgAdd" class="message"></p>
            </form>
            <form id="category">
                <label for="categorySelector">2. Select a category.</label>
                <select id="categorySelector" name="categorySelector"></select>
                <input type="button" value="Delete category" name="deleteCtg" id="deleteCtg">
                <span id="msgRemove" class="message"></span>
            </form>
            <form id="uploadImages">
                <p>3. Select an image to upload.</p>
                <label for="imageToUpload" id="fileLabel">Choose image</label>
                <input type="file" name="imageToUpload" id="imageToUpload">
                <span id="fileName"></span>
                <input type="button" value="Upload Image" name="submit" id="uploadButton">
                <p id="msgUpload" class="message"></p>
            </form>
        </section>
    </div>
</main>

<footer>
</footer>

</body>
</html>



