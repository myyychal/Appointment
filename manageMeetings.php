<!DOCTYPE html>
<html>
<head lang="en">
<meta charset="UTF-8">
<title>ApPOINTment</title>
<script src="alertify/lib/alertify.min.js"></script>
<link rel="stylesheet" href="css/pure-min.css">
<!--[if lte IE 8]>
<link rel="stylesheet" href="css/layouts/side-menu-old-ie.css">
<![endif]-->
<!--[if gt IE 8]><!-->
<link rel="stylesheet" href="css/layouts/side-menu.css">
<link rel="stylesheet" href="alertify/themes/alertify.core.css"/>
<link rel="stylesheet" href="alertify/themes/alertify.default.css"/>
<!--<![endif]-->
<script type="text/javascript" language="javascript" src="js/simplifyPath.js"></script>
<script type="text/javascript" language="javascript" src="js/drawing.js"></script>
<script type="text/javascript" language="javascript" src="js/utils.js"></script>
<?php
session_start();
?>
<script type="text/javascript" language="javascript">
    var canvas;
    var context;

    var thickness = 3;

    var green = 'rgb(131,190,61)';
    var blue = 'rgb(0,0,255)';
    var red = 'rgb(255,0,0)';

    var allPoints = [];

    var continuousPoints = [];

    var lessPoints = [];

    var isDrawing = false;
    var isDrawingEnabled = false;

    var cellSize = 10;
    var sectionLength = 15;
    var startDate;
    var startHour;
    var endHour;

    var meetingLength = 30;

    var startX = 0;
    var startY = 0;
    var endX = 0;
    var endY = 0;

    window.onload = function () {

        canvas = document.getElementById("canvas");
        context = canvas.getContext("2d");

        context.canvas.width = 0.5 * window.innerWidth;
        context.canvas.height = 0.5 * window.innerHeight;

        var startPoint = drawGrid(context, canvas, cellSize);
        startX = startPoint[0] + 0.5;
        startY = startPoint[1] + 0.5;
        endX = startPoint[2] + 0.5;
        endY = startPoint[3] + 0.5;

        context.strokeStyle = getRandomColor();
        context.lineWidth = thickness;
    };

    function clearCanvas() {
        context.clearRect(0, 0, canvas.width, canvas.height);
        continuousPoints = [];
        lessPoints = [];
        allPoints = [];

        var startPoint = drawGrid(context, canvas, cellSize);
        startX = startPoint[0] + 0.5;
        startY = startPoint[1] + 0.5;
        endX = startPoint[2] + 0.5;
        endY = startPoint[3] + 0.5;

        context.lineWidth = thickness;
        context.strokeStyle = getRandomColor();
    }

    function savePointsToJson() {
        setJSONStringToField(pointsToJSON(sortPointsByX(continuousPoints)));
    }

    function drawSimplifiedCurveDemo2(anotherColor) {
        if (anotherColor) {
            context.strokeStyle = getRandomColor();
        }
        continuousPoints = sortPointsByX(continuousPoints);
        var epsilon = 5;
        var simplifiedPoints = simplifyPath(createArrayOfPoints(continuousPoints), epsilon);
        setJSONStringToField(pointsToJSON(simplifiedPoints));
        lessPoints = createArrayOfNumbers(simplifiedPoints);
        drawCurve(context, lessPoints);
        context.stroke();
    }

    function getBestSection() {
        clearCanvas();
        drawAllCurves();

        meetingLength = parseInt(document.getElementById("meetingLength").value);
        sectionLength = parseInt(document.getElementById("sectionLength").value);
        context.beginPath();
        var startChosenX = getBestSelection(allPoints, meetingLength, startX, startY, endX, endY, cellSize);
        var endChosenX = getClosestPointToGrid(startChosenX + (meetingLength/sectionLength)*cellSize,cellSize);
        context.strokeStyle = getRandomColor();
        context.moveTo(startChosenX, 30);
        context.lineTo(startChosenX, canvas.height - 30);
        context.moveTo(endChosenX, 30);
        context.lineTo(endChosenX, canvas.height - 30);
        context.stroke();

        var startMeetingDate = getXDate(canvas,startChosenX,cellSize,startDate,startHour,endHour,sectionLength);
        var endMeetingDate = getXDate(canvas,endChosenX,cellSize,startDate,startHour,endHour,sectionLength);

        document.getElementById("startMeeting").value = startMeetingDate;
        document.getElementById("stopMeeting").value = endMeetingDate;

        document.getElementById("sendNotifications").disabled = false;
    }

    function sortContinuousPoints() {
        continuousPoints = sortPointsByX(continuousPoints);
    }

    function drawAllCurves() {
        startDate = document.getElementById("startDay").value;
        startHour = document.getElementById("startHour").value;
        endHour = document.getElementById("endHour").value;
        sectionLength = parseInt(document.getElementById("sectionLength").value);
        redrawGrid(context,canvas,cellSize,startDate,startHour,endHour,sectionLength);
        var allPointsString = document.getElementById("jsonString").value;
        var allPointsArray = allPointsString.split(":");
        for (var i = 1; i < allPointsArray.length; i++) {
            var jsonPoints = JSON.parse(allPointsArray[i]);
            allPoints.push(jsonPoints);
            context.strokeStyle = getRandomColor();
            context.beginPath();
            drawCurve(context,jsonPoints);
            context.stroke();
        }
    }

</script>
</head>
<?php
include 'php_libs/utils.php';
include 'php_libs/updateFunctions.php';
?>
<body>
<?php
$currentlyLoggedInUser = "";
$selectedMeetingName = "";
$startDay = "";
$startHour = "";
$endHour = "";
$sectionLength = 0;
$jsonStringWithAllPoints = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["selectMeetingName"])) {
        $selectedMeetingName = $_POST["selectMeetingName"];
        if ($selectedMeetingName != "") {
            $ret = selectMeetingByName($selectedMeetingName);
            while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                $startDay = $row["startday"];
                $startHour = $row["starthour"];
                $endHour = $row["endhour"];
                $sectionLength = $row["sectionlength"];
            }
            $jsonStringWithAllPoints = selectAllPointsFromMeeting($selectedMeetingName);
        }
    } else if (isset($_POST["sendNotifications"])){
        if (isset($_POST["selectedMeetingName"])){
            $selectedMeetingName = $_POST["selectedMeetingName"];
            $emails = selectAllUsersEmailsRelatedToMeetingName($selectedMeetingName);
            $subject = "Date of the meeting: " . $selectedMeetingName;
            $message = "The meeting will start: " . $_POST["startMeeting"] . "\n";
            $message .= "The meeting will end: " . $_POST["stopMeeting"] . "\n";
            sendMailPhpMailer($emails,$subject,$message);
            if ($selectedMeetingName != "") {
                $ret = selectMeetingByName($selectedMeetingName);
                while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                    $startDay = $row["startday"];
                    $startHour = $row["starthour"];
                    $endHour = $row["endhour"];
                    $sectionLength = $row["sectionlength"];
                }
                $jsonStringWithAllPoints = selectAllPointsFromMeeting($selectedMeetingName);
            }
        }
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
            <h2>Manage meetings</h2>
        </div>
        <div class="content">
            <form class="pure-form pure-form-stacked" id="selectQuestionnaireForm" enctype="multipart/form-data"
                  name="selectPersonForm" onsubmit="setJSONStringToField(pointsToJSON(continuousPoints))" method="post"
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                Choose any meeting:
                <select id="selectMeetingName" name="selectMeetingName" onChange="this.form.submit()">
                    <?php
                    $ret = selectAllMeetings();
                    if ($ret != false) {
                        echo "<option value=0></option>";
                        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                            $rowValue = $row["name"];
                            if ($rowValue == $selectedMeetingName) {
                                echo "<option value=\"$rowValue\" selected=\"selected\">$rowValue</option>";
                            } else {
                                echo "<option value=\"$rowValue\">$rowValue</option>";
                            }
                        }
                    }
                    ?>
                </select>
                <input type="hidden" name="startDay" id="startDay" value="<?php echo $startDay ?>">
                <input type="hidden" name="startHour" id="startHour" value="<?php echo $startHour ?>">
                <input type="hidden" name="endHour" id="endHour" value="<?php echo $endHour ?>">
                <input type="hidden" name="sectionLength" id="sectionLength" value="<?php echo $sectionLength ?>">
            </form>
            <p>
                <canvas id="canvas" style="background: #fff; margin:20px;"></canvas>
            </p>

            <p><input type="button" id="loadAllMeetingPoints" name="loadAllMeetingPoints" onclick="drawAllCurves()"
                      value="Load all curves"></p>

            <p><input type="hidden" id="jsonString" name="jsonString" value="<?php echo $jsonStringWithAllPoints ?>">
            </p>

            <p>Enter meeting length (minutes): <input class="pure-form pure-input-rounded" type="text" id="meetingLength" name="meetingLength" value="30">
            </p>

            <p><input type="button" id="getBestSection" onclick="getBestSection()" value="Get best section"></p>

            <form class="pure-form pure-form-stacked" id="selectQuestionnaireForm" enctype="multipart/form-data"
                  name="updateMeeting"  method="post"
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <p>Start meeting:<input type="text" value="" name="startMeeting" id="startMeeting"></p>
                <p>End meeting:<input type="text" value="" name="stopMeeting" id="stopMeeting"></p>
                <input type="submit" value="Send notifications via email" id="sendNotifications" name="sendNotifications" disabled>
                <input type="hidden" value="<?php echo $selectedMeetingName; ?>" name="selectedMeetingName" id="selectedMeetingName">
                </form>

            <p><input type="button" id="clearCanvas" onclick="clearCanvas()" value="Clear canvas"></p>


        </div>
    </div>
</div>

</body>
</html>