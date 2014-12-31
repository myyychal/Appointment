function showAndHideDiv(divElem1, divElem2, divElem3) {
    document.getElementById(divElem2).style.display = 'none';
    document.getElementById(divElem3).style.display = 'none';
    if (document.getElementById(divElem1).style.display != 'none') {
        document.getElementById(divElem1).style.display = 'none';
    } else {
        document.getElementById(divElem1).style.display = 'block'
    }
}

function reloadShowAndHideDiv(divElem1, divElem2, divElem3) {
    document.getElementById(divElem1).style.display = 'block';
    document.getElementById(divElem2).style.display = 'none';
    document.getElementById(divElem3).style.display = 'none';
}

function hideAll(divElem1, divElem2, divElem3) {
    document.getElementById(divElem1).style.display = 'none';
    document.getElementById(divElem2).style.display = 'none';
    document.getElementById(divElem3).style.display = 'none';
}

function _add_more() {
    var txt = document.createElement('input');
    txt.type = "file";
    txt.name = "item_file[]";
    var br = document.createElement('br');
    document.getElementById("files").appendChild(txt);
    document.getElementById("files").appendChild(br);
}

function getSelectedPerson() {
    var e = document.getElementById("selectPerson");
    var strUser = e.options[e.selectedIndex].value;
    return strUser;
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('imagePreview').src=e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function getSliderValue(){
    var rangeValue = 1000;
    //rangeValue = document.getElementById("fader").value;
    return rangeValue;
}

function createArrayOfPoints(notPoints) {
    var points = [];
    for (var i = 0; i < notPoints.length; i += 2) {
        var point = {x: notPoints[i], y: notPoints[i + 1]};
        points.push(point);
    }
    return points;
}

function createArrayOfNumbers(points) {
    var numbers = [];
    for (var i = 0; i < points.length; i++) {
        numbers.push(points[i].x);
        numbers.push(points[i].y);
    }
    return numbers;
}

function pointsToJSON(points) {
    var stringJson = JSON.stringify(points);
    return stringJson;
}

function setJSONStringToField(jsonString){
    document.getElementById("jsonString").value = jsonString;
}

function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function getClosestPointToGrid(x, cellSize){
    var unit = x % cellSize;
    if (unit > cellSize/2){
        return x - unit + cellSize + 0.5;
    } else {
        return x - unit + 0.5;
    }
}

function isXInContinuousPoints(x, continuousPoints){
    for (var i=0; i<continuousPoints.length; i+=2){
        if (x == continuousPoints[i]){
            return true;
        }
    }
    return false;
}

function isYInContinuousPoints(y, continuousPoints){
    for (var i=1; i<continuousPoints.length; i+=2){
        if (y == continuousPoints[i]){
            return true;
        }
    }
    return false;
}

function getPointsBetween(currX, currY, continuousPoints, cellSize){
    var prevPointsLength = continuousPoints.length;
    var prevX = continuousPoints[prevPointsLength-2];
    var prevY = continuousPoints[prevPointsLength-1];
    var diffX = currX - prevX;
    var diffY = currY - prevY;
    generatedPoints = [];
    if (diffX > 0){
        for (var i = prevX + cellSize; i < currX; i += cellSize){
            if (!isXInContinuousPoints(i,continuousPoints)){
                generatedPoints.push(i);
                generatedPoints.push(currY);
            }
        }
    } else {
        for (var i = prevX - cellSize; i > currX; i -= cellSize){
            if (!isXInContinuousPoints(i,continuousPoints)){
                generatedPoints.push(i);
                generatedPoints.push(currY);
            }
        }
    }
    return generatedPoints;
}

function sortPointsByX(points){
    arrayOfPoints = createArrayOfPoints(points);
    arrayOfPoints.sort(function(a,b){
        if (a.x > b.x){
            return 1;
        } else if (a.x < b.x) {
            return -1;
        } else {
            return 0;
        }
    });
    return badArray = createArrayOfNumbers(arrayOfPoints);
}

function getBestSelection(allPoints, interval, startPointX, startPointY, endPointX, endPointY, cellSize){
    var sumAllPoints = [];
    for (var j = startPointX; j<endPointX; j+=cellSize){
        sumAllPoints[j] = 0.0;
    }
    for (var i=0; i < allPoints.length; i++){
        var points = allPoints[i];
        if (points.length != 0){
            var iterator = 0;
            for (var j = startPointX; j<=endPointX; j+=cellSize){
                while (points[iterator] <= j && points[iterator] != j){
                    iterator += 2;
                }
                if (points[iterator] != j){
                    sumAllPoints[j] += 0.0;
                } else {
                    sumAllPoints[j] += startPointY - points[iterator+1];
                }
            }
        }
    }
    var max = 0;
    var chosenStartPointX = 0
    for (var j = startPointX; j<endPointX - interval; j+=cellSize){
        var localMax = 0
        for (var i=j; i<=j+interval; i+=cellSize){
            localMax += sumAllPoints[i];
        }
        if (localMax > max){
            max = localMax;
            chosenStartPointX = j;
        }
    }
    return chosenStartPointX;
}

function arePointsInOrder(points){
    var pointsLength = points.length;
    if (pointsLength > 6){
        var currPointX = points[pointsLength-2];
        var prevPointX = points[pointsLength-4];
        var beforePrevPointX = points[pointsLength-6];
        if (prevPointX > beforePrevPointX){
            if (currPointX > prevPointX){
                return true;
            } else {
                return false
            }
        } else {
            if (currPointX < prevPointX){
                return true;
            } else {
                return false;
            }
        }
    } else {
        return true;
    }
}

function getXDate(canvas, chosenX, cellSize, startDate, startHour, endHour, sectionLength){
    var width = canvas.width;
    var divWidth = Math.floor(width/cellSize);
    var newWidth = divWidth*cellSize;

    var startPointX = 30.5;

    var startHourInt = parseInt(startHour.split(":")[0]);
    var endHourInt = parseInt(endHour.split(":")[0]);

    var date = new Date(startDate);
    var day = date.getDate();
    var month = date.getMonth()+1;
    var nextHour = startHourInt;
    var minutes = 0;

    var chosenDate = "";

    for (var i = startPointX; i<newWidth - 30; i+= cellSize){
        if (chosenX == i){
            if (minutes == 0){
                minutes = "00";
            }
            chosenDate = day + "." + month + " at " + nextHour + ":" + minutes;
        }
        minutes += sectionLength;
        if (minutes >= 60){
            nextHour++;
        }
        if (nextHour > endHourInt){
            nextHour = startHourInt;
            day++;
        }
        if (minutes == 60){
            minutes = 0;
        } else if (minutes == 90){
            minutes = 30;
        } else if (minutes == 135 || minutes == 105){
            minutes = 15;
        }
    }

    return chosenDate;

}