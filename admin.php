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

$title = "Admin page";

// default value of $username
$username = "No User is logged in!";

// Check that a user has logged in and has admin role, if not redirect to start page
session_start();
if (isset($_SESSION['validatedUser'])) {


    // instantiate member and check if it has admin role
    $member = new Member();
    $member->initialize($_SESSION['validatedUser']);

    if ($member->hasRole('admin'))
        // set username
        $username = $_SESSION['validatedUser'];

}

if (!isset($_SESSION['validatedUser']) || !$member->hasRole('admin')){
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
            This is the administrator page. This page lets administrators add and delete users. If a user is deleted, all images
            associated with that user will be deleted as well.
        </section>
        <section>
            <h2>Delete member</h2>
            <form id="memberDel">
                <label for="memberSelector">Members</label>
                <select id="memberSelector" name="memberSelector"></select>
                <input type="button" value="Delete member" name="deleteMbr" id="deleteMbr">
                <span id="msgRemove" class="message"></span>
            </form>
        </section>
        <section>
            <h2>Add member</h2>
            <form id="memberAdd">
                <label for="name">Name</label>
                <input type="text" required maxlength="10" placeholder="username" name="name" id="name">

                <label for="password">Password</label>
                <input type="text" required placeholder="password" name="password" id="password">

                <p id="roles">Choose roles:</p>

                <p id="msgAdd" class="message"></p>
                <input type="button" value="Add member" name="addMbr" id="addMbr">
            </form>
        </section>
    </div>
</main>

<footer>
</footer>

</body>
</html>



