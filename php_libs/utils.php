<?php

include "phpmailer/PHPMailerAutoload.php";

function loginFirstMsg()
{
    echo "<p>Please log in first to see this page.</br>
            You will be redirected in 2 seconds</p>";
    $url = "login.php";
    header("refresh:2; url=$url");
}

function reloadEditProductTypeFields($selectedProductType)
{
    global $editName, $editDescription;
    $ret = selectProductsTypeById($selectedProductType);
    if ($ret != false) {
        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
            $editName = $row["name"];
            $editDescription = $row["description"];
        }
    }
}

function reloadEditProductFields($selectedProduct)
{
    global $editName, $editTypeId, $editDescription, $editRate, $editImageUrl;
    $ret = selectProductById($selectedProduct);
    if ($ret != false) {
        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
            $editName = $row["name"];
            $editTypeId = $row["typeid"];
            $editDescription = $row["description"];
            $editRate = $row["rate"];
            $editImageUrl = $row["imageurl"];
        }
    }
}

function reloadEditProjectFields($selectedGroup)
{
    global $editName, $editMessage, $editHappenDate;
    $ret = selectProjectById($selectedGroup);
    if ($ret != false) {
        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
            $editName = $row["name"];
            $editMessage = $row["message"];
            $editHappenDate = $row["happedDate"];
        }
    }
}

function sendMailWithQuestionnaire($email, $qId)
{
    $subject = "Survey invitation";
    $message = "Here is the linkt to a survey about our products.
                <br> We would like you to take part in it.";

    $pollLink = curPageURLMain();
    $qidmd5 = md5($qId . "BOMBA");
    $pollLink .= "/Ankietour/takeSurvey.php?questionnaireId=$qidmd5";
    $message .= "<br/><br/><br/> Unsubscribe: $pollLink";

    sendMailPhpMailer($email, $subject, $message);
}

function sendMailPhpMailer($p_email, $p_subject, $p_message)
{
    $mail = new PHPMailer(true);
    $mail->CharSet = "UTF-8";

    date_default_timezone_set('Etc/UTC');

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = "myyychal@gmail.com";

    $file = file_get_contents('../../0192837465', true);
    $mail->Password = $file;

    $mail->setFrom('from@example.com', 'First Last');
    $mail->addReplyTo('replyto@example.com', 'First Last');

    $p_emails = explode(",", $p_email);

    if (!empty($p_emails)) {
        foreach ($p_emails as $email) {
            if (strpos($email, '@')) {
                $mail->addBCC($email);
            }
        }
    }

    $mail->Subject = $p_subject;

    if (empty($p_message)) {
        $p_message = " ";
    }

    $order = array("\r\n", "\n", "\r");
    $replace = '<br />';
    $p_message = str_replace($order, $replace, $p_message);

    $mail->msgHTML($p_message);

    if ($mail->send()) {
        return true;
    } else {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }

}

function curPageURL()
{
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function curPageURLMain()
{
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"];
    }
    return $pageURL;
}

function uploadImage($fileToUpload)
{
    global $errMsg;
    $target_dir = "images/";
    $target_file = $target_dir . basename($_FILES[$fileToUpload]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$fileToUpload]["tmp_name"]);
        if ($check !== false) {
            $errMsg = "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            $errMsg = "File is not an image.";
            $uploadOk = 0;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        $errMsg = "Sorry, file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES[$fileToUpload]["size"] > 500000) {
        $errMsg = "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if (strtolower($imageFileType) != "jpg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpeg"
        && $imageFileType != "gif"
    ) {
        $errMsg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $errMsg = "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES[$fileToUpload]["tmp_name"], $target_file)) {
            $errMsg = "The file " . basename($_FILES[$fileToUpload]["name"]) . " has been uploaded.";
        } else {
            $errMsg = "Sorry, there was an error uploading your file.";
        }
    }
    return $target_file;
}

function showImage($url)
{
    if (strpos($url, '.') !== FALSE) {
        echo "<img class=\"pure-img\" id=\"imagePreview\" src=\"$url\" alt=\"Image\" />";
    } else {
        echo "<img class=\"pure-img\" id=\"imagePreview\" src=\"images/no_photo.jpg\" alt=\"Image\" />";
    }
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function generateMenu()
{
    echo "<div id=\"menu\">";
    echo "<div class=\"pure-menu pure-menu-open\">
            <ul>";

    if (!isset($_SESSION['loggedin'])) {
        echo "<li>
                        <a href=\"login.php\">Login</a>
                    </li>";

    } elseif (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
        echo "<li>
                        <a href=\"logout.php\">Logout</a>
                    </li>";

    }

    if (isset($_SESSION["privilege"])) {
        if ($_SESSION["privilege"] < 3) {
            echo "<li>
            <a href=\"addUser.php\">Add user</a>
        </li>";

        }
    }
    if (isset($_SESSION["privilege"])) {
        if ($_SESSION["privilege"] < 3) {
            echo "
        <li>
            <a href=\"createMeeting.php\">Create meeting</a>
        </li>
        <li>
            <a href=\"manageMeetings.php\">Manage meetings</a>
        </li>
";
        }
    }

    if (isset($_SESSION["privilege"])) {
        echo "
    <li>
        <a href=\"drawMeetingLine.php\">Draw meeting line</a>
    </li>";

    }
    echo "
</ul>
</div>
</div>
";
}



?>