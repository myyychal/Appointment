<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>ApPOINTment - Logout</title>
    <script src="alertify/lib/alertify.min.js"></script>
    <link rel="stylesheet" href="css/pure-min.css">
    <!--[if lte IE 8]>
    <link rel="stylesheet" href="css/layouts/side-menu-old-ie.css">
    <![endif]-->
    <!--[if gt IE 8]><!-->
    <link rel="stylesheet" href="css/layouts/side-menu.css">
    <link rel="stylesheet" href="alertify/themes/alertify.core.css" />
    <link rel="stylesheet" href="alertify/themes/alertify.default.css" />
    <!--<![endif]-->
</head>
<?php
include 'php_libs/utils.php';
session_start();
?>
<body>
<div id="layout">
    <a href="#menu" id="menuLink" class="menu-link">
        <!-- Hamburger icon -->
        <span></span>
    </a>

    <?php generateMenu(); ?>
    <div id="main">
        <div class="header">
            <h1>Ankietour</h1>

            <h2>Logout</h2>
        </div>
        <div class="content">
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                session_unset();
                session_destroy();
                echo "<p>You were logged out.</p>";
            } else {
                echo "<p>You weren't logged in.</p>";
            }
            ?>

            <a class="button-secondary pure-button" href="index.php">Back to menu</a>
        </div>
    </div>
</div>
<script src="js/ui.js"></script>
</body>
</html>