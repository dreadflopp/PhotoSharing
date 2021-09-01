<?PHP
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: index.php
 * Desc: Start page for Projekt
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/
$title = "Image Portal";

// Check that a user has logged in, if so set username
session_start();
if (isset($_SESSION['validatedUser']))
    $username = $_SESSION['validatedUser'];
else
    $username = '';

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

    <!-- description -->
    <section>
        <h2>Welcome</h2>
        <p>This image portal allows users to upload images and share them with others. To upload images you need to have
            an account and you need to be logged in. On your user page, that is only visible to you, you may create
            categories and upload images to that category. All your uploaded images are shown on your image page. Your
        image page is visible to all users. Images here may be sorted by category.</p>
    </section>
</main>

<footer>
</footer>

</body>
</html>
