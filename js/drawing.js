function drawGrid(context,canvas, cellSize) {
    var width = canvas.width;
    var height = canvas.height;

    var strokeStyle = context.strokeStyle;
    var lineWidth = context.lineWidth;

    context.lineWidth = 1;
    context.strokeStyle = "#ddd";

    for (var x = 0.5; x < width; x += cellSize) {
        context.moveTo(x, 0);
        context.lineTo(x, height);
    }

    for (var y = 0.5; y < height; y += cellSize) {
        context.moveTo(0, y);
        context.lineTo(width, y);
    }

    context.stroke();

    context.beginPath();
    context.lineWidth = 5;
    context.moveTo(30,30);

    var divHeight = Math.floor(height/cellSize);
    var newHeight = divHeight*cellSize;

    var divWidth = Math.floor(width/cellSize);
    var newWidth = divWidth*cellSize;

    context.lineTo(30,newHeight-30);
    context.lineTo(newWidth-30,newHeight-30);

    context.stroke();

    context.strokeStyle = strokeStyle;
    context.lineWidth = lineWidth;

    var point = [];
    point[0] = 30;
    point[1] = newHeight-30;
    point[2] = newWidth-30;
    point[3] = newHeight-30;
    return point;
}

function redrawGrid(context, canvas, cellSize, startDate, startHour, endHour, sectionLength){
    drawGrid(context,canvas,cellSize);

    var strokeStyle = context.strokeStyle;
    var lineWidth = context.lineWidth;

    context.lineWidth = 1;
    context.fillStyle = "#000000";

    var widthMultiply = 4;
    var hourWidth = widthMultiply*cellSize;
    var howManyMinutesNext = widthMultiply*sectionLength;

    var width = canvas.width;
    var height = canvas.height;
    var divHeight = Math.floor(height/cellSize);
    var newHeight = divHeight*cellSize;
    var divWidth = Math.floor(width/cellSize);
    var newWidth = divWidth*cellSize;

    var startPointX = 30.5;
    var startPointY = newHeight - 30;

    var fontSize = 15;
    context.font = fontSize + "px Arial";

    var startHourInt = parseInt(startHour.split(":")[0]);
    var endHourInt = parseInt(endHour.split(":")[0]);

    var date = new Date(startDate);
    var day = date.getDate();
    var month = date.getMonth()+1;
    var nextHour = startHourInt;
    for (var i = startPointX; i<newWidth - 30; i+= hourWidth){
        if (nextHour == startHourInt){
            context.fillText(day + "." + month, i, startPointY+2*fontSize);
        }
        context.fillRect(i, startPointY-5/2,5,5);
        context.fillText(nextHour, i, startPointY+fontSize);
        nextHour += (howManyMinutesNext / 60);
        if (nextHour > endHourInt){
            nextHour = startHourInt;
            day++;
        }
    }

    context.strokeStyle = strokeStyle;
    context.lineWidth = lineWidth;
}

function drawPoint(e, points, isDiscrete) {
    if (isDiscrete) {
        var x = e.pageX - canvas.offsetLeft;
        var y = e.pageY - canvas.offsetTop;
        context.fillRect(x, y, thickness, thickness);
        context.stroke();

        points.push({x: x, y: y});

        if (points.length >= 2) {
            var pointsLength = points.length;

            var firstPoint = points[pointsLength - 2];
            var secondPoint = points[pointsLength - 1];

            context.strokeStyle = blue;
            context.beginPath();
            context.moveTo(firstPoint.x, firstPoint.y);
            context.lineTo(secondPoint.x, secondPoint.y);
            context.stroke();
        }
    }
}

function drawLines(ctx, pts) {
    ctx.moveTo(pts[0], pts[1]);
    for (i = 2, l = pts.length - 1; i < l; i += 2)
        ctx.lineTo(pts[i], pts[i + 1]);
}

function getCurvePoints(ptsa, tension, isClosed, numOfSegments) {

    // use input value if provided, or use a default value
    tension = typeof tension === 'number' ? tension : 0.5;
    numOfSegments = typeof numOfSegments === 'number' ? numOfSegments : 16;

    var _pts, res = [],			/// clone array
        x, y,					/// our x,y coords
        t1x, t2x, t1y, t2y,		/// tension vectors
        c1, c2, c3, c4,			/// cardinal points
        st, t, i,				/// steps based on num. of segments
        pow3, pow2,				/// cache powers
        pow32, pow23,
        p0, p1, p2, p3,			/// cache points
        pl = ptsa.length;

    /// clone array so we don't change the original content
    _pts = ptsa.concat();

    _pts.unshift(ptsa[1]);					/// copy 1. point and insert at beginning
    _pts.unshift(ptsa[0]);
    _pts.push(ptsa[pl - 2], ptsa[pl - 1]);	/// copy last point and append

    /// 1. loop goes through point array
    /// 2. loop goes through each segment between the two points + one point before and after
    for (i = 2; i < pl; i += 2) {

        p0 = _pts[i];
        p1 = _pts[i + 1];
        p2 = _pts[i + 2];
        p3 = _pts[i + 3];

        /// calc tension vectors
        t1x = (p2 - _pts[i - 2]) * tension;
        t2x = (_pts[i + 4] - p0) * tension;

        t1y = (p3 - _pts[i - 1]) * tension;
        t2y = (_pts[i + 5] - p1) * tension;

        for (t = 0; t <= numOfSegments; t++) {

            /// calc step
            st = t / numOfSegments;

            pow2 = Math.pow(st, 2);
            pow3 = pow2 * st;
            pow23 = pow2 * 3;
            pow32 = pow3 * 2;

            /// calc cardinals
            c1 = pow32 - pow23 + 1;
            c2 = pow23 - pow32;
            c3 = pow3 - 2 * pow2 + st;
            c4 = pow3 - pow2;

            /// calc x and y cords with common control vectors
            x = c1 * p0 + c2 * p2 + c3 * t1x + c4 * t2x;
            y = c1 * p1 + c2 * p3 + c3 * t1y + c4 * t2y;

            /// store points in array
            res.push(x, y);
        }
    }

    return res;
}

function drawCurve(ctx, ptsa, tension, isClosed, numOfSegments, showPoints) {

    showPoints = showPoints ? showPoints : false;

    ctx.beginPath();

    drawLines(ctx, getCurvePoints(ptsa, tension, isClosed, numOfSegments));

    if (showPoints) {
        ctx.stroke();
        ctx.beginPath();
        for (var i = 0; i < ptsa.length - 2; i += 2) ctx.rect(ptsa[i] - 2, ptsa[i + 1] - 2, 4, 4);
    }
}