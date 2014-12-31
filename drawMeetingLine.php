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

            canvas.onmousedown = startDrawing;
            canvas.onmouseup = stopDrawing;
            canvas.onmouseout = stopDrawing;
            canvas.onmousemove = draw;

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
            if (!isDrawingEnabled){
                isDrawingEnabled = true;
                startDate = document.getElementById("startDay").value;
                startHour = document.getElementById("startHour").value;
                endHour = document.getElementById("endHour").value;
                sectionLength = parseInt(document.getElementById("sectionLength").value);
                redrawGrid(context,canvas,cellSize,startDate,startHour,endHour,sectionLength);
            }
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

                        context.fillStyle = context.strokeStyle;
                        context.globalAlpha = 0.2;
                        for (var i = y; i < canvas.height - 40; i += cellSize) {
                            context.fillRect(x, i, cellSize, cellSize);
                        }

                        context.globalAlpha = 1.0;
                    }
                }
            }
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

            isDrawingEnabled = false;
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

        function enableDrawing() {
            isDrawingEnabled = true;
            startDate = document.getElementById("startDay").value;
            startHour = document.getElementById("startHour").value;
            endHour = document.getElementById("endHour").value;
            sectionLength = parseInt(document.getElementById("sectionLength").value);
            redrawGrid(context,canvas,cellSize,startDate,startHour,endHour,sectionLength);
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
        }
    } else if (isset($_POST["submitPoints"])){
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
            $currentlyLoggedInUser = $_SESSION['username'];
            $points = $_POST["jsonString"];
            $selectedMeetingName = $_POST["selectedMeetingName"];
            updateUserMeetingWithPoints($currentlyLoggedInUser,$selectedMeetingName,$points);
            echo "<script> alertify.alert(\"Your time preferences were sent.\")</script>";
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
                $currentlyLoggedInUser = $_SESSION['username'];
            }
            ?>
            <h2>Draw meeting line</h2>
        </div>
        <div class="content">
            <form class="pure-form pure-form-stacked" id="selectQuestionnaireForm" enctype="multipart/form-data"
                  name="selectPersonForm" onsubmit="setJSONStringToField(pointsToJSON(createArrayOfPoints(continuousPoints)))" method="post"
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                Choose your meeting:
                <select id="selectMeetingName" name="selectMeetingName" onChange="this.form.submit()">
                    <?php
                    $ret = selectMeetingsByUser($currentlyLoggedInUser);
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
            <form class="pure-form pure-form-stacked" name="addProductForm"
                  method="post" onsubmit="return savePointsToJson()"
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <p><input type="hidden" id="jsonString" name="jsonString" value=""></p>
                <p><input type="hidden" id="selectedMeetingName" name="selectedMeetingName" value="<?php echo $selectedMeetingName ?>"></p>
                <p><input type="submit" id="submitPoints" name="submitPoints" value="Submit curve"></p>
            </form>
            <p><input type="button" id="clearCanvas" onclick="clearCanvas()" value="Clear canvas"></p>
        </div>
    </div>
</div>

</body>
</html>