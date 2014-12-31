<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>ApPOINTment - Login</title>
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
include 'php_libs/loginFunctions.php';
include 'php_libs/utils.php';
?>

<?php
$errMsg = $loginErr = $passwdErr = "";
$successLogin = "";
$login = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST["login"];
    $password = $_POST["password"];
    if (empty($_POST["login"])) {
        $loginErr = "You have to fill in this field.";
    }
    if (empty($_POST["password"])) {
        $passwdErr = "You have to fill in this field";
    }
    if (checkLoginAndPassword($_POST["login"], $_POST["password"])) {
        $privilege = getUserPrivilege($_POST["login"]);
        loginUser($_POST["login"], $_POST["password"], $privilege);
        $url = "index.php";
        $successLogin = "You'll be redirected in 2 seconds";
        header("refresh:2; url=$url");
    } else {
        $errMsg = "Incorrect login or password";
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
            <h1>Ankietour</h1>

            <h2>Login</h2>
        </div>
        <div class="content">
            <form class="pure-form pure-form-stacked" name="loginForm" method="post"
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <p>
                    Username: <input type="text" name="login"  value="<?php echo $login; ?>"/>
        <span class="error"><?php global $loginErr;
            echo $loginErr; ?></span>
                </p>

                <p>
                    Password: <input type="password" name="password"  value="<?php echo $password; ?>"/>
        <span class="error"><?php global $passwdErr;
            echo $passwdErr; ?></span>
                </p>

                <p>
                    <input class="button-success pure-button" type="submit" name="loginButton" value="Sign in"
                           />
                    <input class="pure-button" type="button" name="cancelButton" value="Cancel" />
                </p>
    <span class="error"><?php global $errMsg;
        echo $errMsg ?></span>
    <span class="success"><?php global $successLogin;
        echo $successLogin ?></span>
            </form>

            <a class="button-secondary pure-button" href="index.php">Back to menu</a>
        </div>
    </div>
</div>
<script src="js/ui.js"></script>
</body>
</html>