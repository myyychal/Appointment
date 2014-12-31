<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Ankietour - Add user</title>
    <script src="js/checkFields.js"></script>
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
session_start();
?>
<body>
<?php
include 'php_libs/insertFunctions.php';
include 'php_libs/utils.php';
?>
<?php
$newUsernameErr = $newPasswdErr = "";
$newUsername = $newPassword = $newEmail = "";
$newPrivilege = 0;
$errMsg = $successLogin = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST["newUsername"];
    $newPassword = $_POST["newPassword"];
    $newPrivilege = $_POST["privilege"];
    $newEmail = $_POST["newEmail"];
    if (empty($_POST["newUsername"])) {
        $newUsernameErr = "You have to fill in this field.";
    }
    if (empty($_POST["newUsername"])) {
        $newPasswdErr = "You have to fill in this field";
    }
    if (addUser($newUsername, $newPassword, $newPrivilege, $newEmail)) {
        $successLogin = "New user was added";
    }
}
?>
<div id="layout">
    <a href="#menu" id="menuLink" class="menu-link">
        <!-- Hamburger icon -->
        <span></span>
    </a>

    <?php generateMenu(); ?>

    <div id="main">
        <div class="header">
            <h1>ApPOINTment</h1>
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                echo "Hello, " . $_SESSION['username'];
            }
            ?>

            <h2>Add user</h2>
        </div>
        <div class="content">

            <form class="pure-form pure-form-stacked" name="addUserForm" method="post"
                  onsubmit="return checkUserFields()"
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <p>
                    Username: <input type="text" id="newUsername" name="newUsername"
                                     value="<?php global $newUsername;
                                     echo $newUsername; ?>" required>
                    <span class="error"><?php echo $newUsernameErr; ?></span>
                </p>

                <p>
                    Password: <input type="password" id="newPassword" name="newPassword"
                                     value="<?php global $newPassword;
                                     echo $newPassword; ?>" required>
                    <span class="error"><?php echo $newPasswdErr; ?></span>
                </p>

                <p>
                    Email: <input type="text" id="newEmail" name="newEmail"
                                     value="<?php global $newEmail;
                                     echo $newEmail; ?>" required>
                </p>

                <p>
                    <?php
                    if (isset($_SESSION["privilege"])) {
                        if ($_SESSION["privilege"] < 2) {
                            ?>
                            <input type="radio" name="privilege" value=1>Admin<br>
                        <?php
                        }
                    }
                    ?>
                    <?php
                    if (isset($_SESSION["privilege"])) {
                        if ($_SESSION["privilege"] < 3) {
                            ?>
                            <input type="radio" name="privilege" value=2>Meeting owner<br>
                        <?php
                        }
                    }
                    ?>
                    <input checked="checked" type="radio" name="privilege" value=3>Member<br>
                </p>

                <p>
                    <input class="button-success pure-button" type="submit" name="addUserButton" value="Add user"
                        />
                    <input class="pure-button" type="button" name="cancelButton" value="Cancel" />
                </p>

                <p id="errMsg"></p>
            </form>

<span class="error"><?php global $errMsg;
    echo $errMsg ?></span>
<span class="success"><?php global $errMsg;
    echo $successLogin ?></span>

            <p>
                <a class="button-secondary pure-button" href="index.php">Back to menu</a>
            </p>
        </div>
    </div>
</div>
<script src="js/ui.js"></script>
</body>
</html>