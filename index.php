<!DOCTYPE html>
<html>
<head>
    <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"
            integrity="sha384-feJI7QwhOS+hwpX2zkaeJQjeiwlhOP+SdQDqhgvvo1DsjtiSQByFdThsxO669S2D"
            crossorigin="anonymous"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="style.css" rel="stylesheet" type="text/css">

    <title>Zadanie 4</title>
</head>
<body>

<div class="container fill">
    <header class="jumbotron">
        <h1>Zadanie 4</h1>
        <p>Websockety</p>
    </header>

    <div class="canvasDiv">
        <canvas id="mainCanvas" width="800" height="600"></canvas>
    </div>
    <p>
        <input type="button" value="Uložiť obrázok" onclick="savePicture()">
    </p>
    <div id="imageDiv">
        <h2>Obrázok na stiahnutie</h2>
        <img id="image" src="img.jpg" alt="obrazok z canvasu">
    </div>


    <div id="colorModal" class="colorModal">
        <div class="modal-vnutro">
            <p>
                <label for="colorPicker">Vybrať farbu: </label>
                <input id="colorPicker" type="color">
            </p>
            <input type="button" value="Potvrdiť" onclick="hideDiv()">
        </div>
    </div>

    <script>
        //WEBSOCKET FUNKCIE
        var socket = new WebSocket('wss://147.175.98.141:5500', 'echo-protocol');

        socket.onmessage = function (message) {
            var data = JSON.parse(JSON.parse(message.data).utf8Data);
            ctx.moveTo(data.points[0].x, data.points[0].y);
            draw(data);
        };

        socket.onerror = function (error) {
            console.log('WebSocket error: ' + error);
        };

        //CANVAS FUNKCIE
        var canvas = document.getElementById('mainCanvas');
        var ctx = canvas.getContext('2d');
        var points = [];

        ctx.imageSmoothingEnabled = false;
        ctx.lineWidth = 5;
        ctx.lineJoin = ctx.lineCap = 'round';

        var color = document.getElementById('colorPicker');

        canvas.addEventListener('mousedown', function (e) {
            drawHandler(e);
            canvas.addEventListener('mousemove', drawHandler, false);
        }, false);

        canvas.addEventListener('mouseup', function () {
            points = [];
            //vymazanie listenera pre pohyb
            canvas.removeEventListener('mousemove', drawHandler, false);
        }, false);

        function drawHandler(e) {
            var bounds = canvas.getBoundingClientRect();

            var realPoints = {x: e.clientX-bounds.left, y: e.clientY-bounds.top};


            points.push({"x": realPoints.x, "y": realPoints.y});
            var data = {
                "points": points,
                "color": color.value
            };

            ctx.moveTo(data.points[0].x, data.points[0].y);
            draw(data);
            socket.send(JSON.stringify(data));
        }

        function draw(data) {
            ctx.beginPath();

            for (var i = 0; i < data.points.length; i++) {
                ctx.lineTo(data.points[i].x, data.points[i].y);
            }

            //nastavenie farby
            ctx.strokeStyle = data.color;

            ctx.stroke();
        }


        //DALSIA FUNKCIONALITA
        function savePicture() {
            var url = $("#mainCanvas").get(0).toDataURL("img/png");

            var img = $("#image");
            img.attr("src",url.toString());

            $("#imageDiv").show();
        }

        function hideDiv() {
            $("#colorModal").hide();
        }
    </script>
</div>
</body>
</html>
