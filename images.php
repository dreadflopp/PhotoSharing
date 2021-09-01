<?PHP
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: images.php
 * Desc: Image page for Projekt
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
include 'util.php';

$title = 'Pictures';

session_start();

if (isset($_SESSION['validatedUser']))
    $username = $_SESSION['validatedUser'];
else
    $username = '';

// remove previous (if any) data about current image page in session storage
if(isset($_SESSION['imagePage']))
    unset($_SESSION['imagePage']);

// remove previous (if any) data about current category to show on image page from session storage
if(isset($_SESSION['category']))
    unset($_SESSION['category']);


// default value of $username and $category
$imagePage = 'No User is set!';
$category = 'All categories';

// if user parameter is sent with GET, check if the user exist in the database
// and update $username. Save member object to to session storage. The value in session storage will
// be used when the client requests the images linked to a certain user.
if (isset($_GET['user']) && $_GET['user'] !== '') {
    // try to initialize member
    $member = new Member;
    if (!$member->initialize($_GET['user']))
        $imagePage = "User doesn't exist!"; // username is wrong, no images are loaded
    else {
        $imagePage = $_GET['user']; // user found

        // save member name to session storage
        $_SESSION['imagePage'] = $imagePage;

        // if category parameter is sent with GET, check if the category exist in the database
        // for this user. Save to session storage if it does. The value in session storage will
        // be used when the client requests the images linked to a certain user.
        if (isset($_GET['category'])) {
            // get all categories for members that image page belongs to
            $categories = $member->getCategories();

            // search categories array for the category given as url parameter
            if (!in_array($_GET['category'], $categories)) {
                // category not found
                $category = "Category doesn't exist!";
//                $_SESSION['category'] = false;
            } else {
                // category found
                $category = $_GET['category'];

                // save to session storage
                $_SESSION['category'] = $category;
            }
        }
    }
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
        <!-- description -->
        <section>
            <h2>Description</h2>
            This is the picture page. Every user has a picture page that shows all pictures
            uploaded by that user. You can filter the pictures by category. If you hover the
            mouse pointer over an image, mouse over text will show the date the image was taken,
            if the information is available.
        </section>

        <!-- images -->
        <section id="images">
            <h2>Pictures - <?php echo $category ?></h2>
            <section id="thumbnails"></section>
        </section>
    </div>
</main>

<footer>
</footer>

</body>
</html>
