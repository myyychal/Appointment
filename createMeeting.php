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

    setTodayDate();

    canvas = document.getElementById("canvas");
    canvas.style.display = 'none';
    context = canvas.getContext("2d");

    context.canvas.width = 0.5 * window.innerWidth;
    context.canvas.height = 0.5 * window.innerHeight;

    canvas.onmousedown = startDrawing;
    canvas.onmouseup = stopDrawing;
    canvas.onmouseout = stopDrawing;
    canvas.onmousemove = draw;
    canvas.onclick = draw;

    var startPoint = drawGrid(context, canvas, cellSize);
    startX = startPoint[0] + 0.5;
    startY = startPoint[1] + 0.5;
    endX = startPoint[2] + 0.5;
    endY = startPoint[3] + 0.5;

    context.strokeStyle = getRandomColor();
    context.lineWidth = thickness;
};

function startDrawing(event) {
    isDrawing = true;
    context.beginPath();
    var pointsLength = continuousPoints.length;
    context.moveTo(continuousPoints[pointsLength - 2], continuousPoints[pointsLength - 1]);
    context.beginPath();
}

function stopDrawing() {
    isDrawing = false;
}

function draw(e) {
    if (isDrawing && isDrawingEnabled) {
        var x = e.pageX - canvas.offsetLeft;
        var y = e.pageY - canvas.offsetTop;

        if ((x > 30 && x < endX) && (y > 30 && y < endY)) {

            x = getClosestPointToGrid(x, cellSize);
            y = getClosestPointToGrid(y, cellSize);

            if (!(isXInContinuousPoints(x, continuousPoints))) {

                var additionalPoints = getPointsBetween(x, y, continuousPoints, cellSize);

                //continuousPoints = sortPointsByX(continuousPoints);

                for (var i = 0; i < additionalPoints.length; i += 2) {
                    continuousPoints.push(additionalPoints[i]);
                    continuousPoints.push(additionalPoints[i + 1]);

                    if (arePointsInOrder(continuousPoints)) {
                        context.lineTo(additionalPoints[i], additionalPoints[i + 1]);
                    } else {
                        context.beginPath();
                    }

                    context.fillStyle = context.strokeStyle;
                    context.globalAlpha = 0.2;
                    for (var j = additionalPoints[i + 1]; j < canvas.height - 40; j += cellSize) {
                        context.fillRect(additionalPoints[i], j, cellSize, cellSize);
                    }

                }

                continuousPoints.push(x);
                continuousPoints.push(y);
                if (arePointsInOrder(continuousPoints)) {
                    context.lineTo(x, y);
                } else {
                    context.beginPath();
                }
                context.stroke();

                if (continuousPoints.length >= 4) {
                    context.fillStyle = context.strokeStyle;
                    context.globalAlpha = 0.2;
                    for (var i = y; i < canvas.height - 40; i += cellSize) {
                        context.fillRect(x, i, cellSize, cellSize);
                    }
                }

                context.globalAlpha = 1.0;
            }
        }
    }
}

function drawNextCurve() {
    context.stroke();
    context.strokeStyle = getRandomColor();
    allPoints.push(sortPointsByX(continuousPoints));
    continuousPoints = [];
    lessPoints = [];
}

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

    unlockOptionsLockDrawing();
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

function sortContinuousPoints() {
    continuousPoints = sortPointsByX(continuousPoints);
}

function updateEpsilonValue(val) {
    document.getElementById('epsilonValue').value = val;
}

function updateCellSizeValue(val) {
    document.getElementById('cellSizeValue').value = val;
    cellSize = parseInt(val);
    clearCanvas();
}

function updateSectionLengthValue() {
    var lengths = document.getElementsByName("sectionLength");
    for (var i = 0; i < lengths.length; i++) {
        lengths[i].disabled = true;
        if (lengths[i].checked == true) {
            sectionLength = parseInt(lengths[i].value);
        }
    }
}

function setTodayDate() {
    Date.prototype.toDateInputValue = (function () {
        var local = new Date(this);
        local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
        return local.toJSON().slice(0, 10);
    });
    document.getElementById('startDate').value = new Date().toDateInputValue();
}

function lockOptionsUnlockDrawing() {
    document.getElementById("canvas").style.display = '';
    isDrawingEnabled = true;
    updateSectionLengthValue();
    startDate = document.getElementById("startDate").value;
    startHour = document.getElementById("startHour").value;
    endHour = document.getElementById("endHour").value;
    document.getElementById("drawSimplifiedCurveDemo2").disabled = false;
    document.getElementById("clearCanvas").disabled = false;
    document.getElementById("startDate").disabled = true;
    document.getElementById("startHour").disabled = true;
    document.getElementById("endHour").disabled = true;
    document.getElementById("createMeeting").disabled = true;
    document.getElementById("lockOptionsUnlockDrawing").disabled = true;

    redrawGrid(context, canvas, cellSize, startDate, startHour, endHour, sectionLength);
}

function unlockOptionsLockDrawing() {
    document.getElementById("canvas").style.display = 'none';
    isDrawingEnabled = false;
    var lengths = document.getElementsByName("sectionLength");
    for (var i = 0; i < lengths.length; i++) {
        lengths[i].disabled = false;
    }
    startDate = document.getElementById("startDate").value;
    startHour = document.getElementById("startHour").value;
    endHour = document.getElementById("endHour").value;
    document.getElementById("drawSimplifiedCurveDemo2").disabled = true;
    document.getElementById("clearCanvas").disabled = true;
    document.getElementById("startDate").disabled = false;
    document.getElementById("startHour").disabled = false;
    document.getElementById("endHour").disabled = false;
    document.getElementById("createMeeting").disabled = false;
    document.getElementById("lockOptionsUnlockDrawing").disabled = false;
}
</script>
</head>
<?php
include 'php_libs/insertFunctions.php';
include 'php_libs/utils.php';
include 'php_libs/selectFunctions.php'
?>
<body>
<?php
global $errMsg;
$description = $name = "";
$users = array();
$startDay = $startHour = $endHour ="";
$sectionLength = 0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["createMeeting"])) {
        $name = $_POST["meetingName"];
        $description = $_POST["newDescription"];
        $users = $_POST["newPersonId"];
        $startDay = $_POST["startDate"];
        $startHour = $_POST["startHour"];
        $endHour = $_POST["endHour"];
        $sectionLength = $_POST["sectionLength"];
        addMeeting($name,$startDay,$startHour,$endHour,$sectionLength,$description);
        foreach ($users as $user){
            addUserToMeeting($user,$name);
        }
        echo "<script> alertify.alert(\"New meeting was added.\")</script>";
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
            <h2>Create meeting</h2>
        </div>
        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
            if (isset($_SESSION["privilege"])) {
                if ($_SESSION["privilege"] < 3) {
                    ?>
                    <div class="content">
                        <p>
                            <canvas id="canvas" style="background: #fff; margin:20px;"></canvas>
                        </p>
                        <form class="pure-form pure-form-stacked" name="addMeetingForm"
                              method="post"
                              action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <p>Enter meeting name: <input type="text" id="meetingName" name="meetingName" value="<?php echo $name; ?>" required></p>
                        <p>Enter meeting description: <textarea class="full_width" name="newDescription"><?php echo $description; ?></textarea></p>
                        Choose users:
                        <select id="newPersonId" name="newPersonId[]" multiple="multiple" required>
                            <?php
                            $ret = selectUsersWithPrivilege(3);
                            if ($ret != false) {
                                while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                                    $rowValue = $row["login"];
                                    if (in_array($rowValue, $users)) {
                                        echo "<option value=\"$rowValue\" selected=\"selected\">$rowValue</option>";
                                    } else {
                                        echo "<option value=\"$rowValue\">$rowValue</option>";
                                    }
                                }
                            }
                            ?>
                        </select>

                        <p>Choose start day: <input type="date" id="startDate" name="startDate" value=""></p>

                        <p>Choose start hour: <input type="time" id="startHour" name="startHour" step="900"
                                                     value="08:00">
                        </p>

                        <p>Choose end hour: <input type="time" id="endHour" name="endHour" step="900" value="20:00"></p>

                        <p>
                            Choose duration of one section (minutes):
                            <input type="radio" name="sectionLength" value="15"
                                   checked>15</input>
                            <input type="radio" name="sectionLength" value="30">30</input>
                            <input type="radio" name="sectionLength" value="45">45</input>
                            <input type="radio" name="sectionLength" value="60">60</input>
                        </p>

                        </p>
                            <p><input type="hidden" id="jsonString" name="jsonString" value=""></p>

                            <p><input type="submit" id="createMeeting" name="createMeeting" value="Create meeting">
                            </p>
                        </form>
                        <p><?php echo $errMsg;?></p>
                        <p>
                            <input type="button" id="lockOptionsUnlockDrawing" onclick="lockOptionsUnlockDrawing()"
                                   value="Preview canvas">
                        </p>

                        <p>
                            <input type="button" id="drawSimplifiedCurveDemo2" onclick="drawSimplifiedCurveDemo2(false)"
                                   value="Draw simplified curve" disabled></p>

                        <p><input type="button" id="clearCanvas" onclick="clearCanvas()" value="Clear canvas" disabled>
                    </div>
                <?php
                }
            }
        }
        ?>
    </div>
</div>

</body>
</html>