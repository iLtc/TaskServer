<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
        <title>Burndown Chart</title>

        <style>
            .chart-container canvas {
                max-width: 1500px;
                display: block;
                margin: auto;
            }
        </style>
    </head>
    <body>
    <div>
        <nav class="navbar navbar-light bg-light">
            <span class="navbar-brand mb-0 h1">Burndown Chart</span>

            <div class="form-inline">
                <label for="start" class="mr-sm-2">Start Date: </label>
                <input type="date" id="start" name="start" class="form-control mr-sm-2">
                <label for="end" class=" mr-sm-2">End Date:</label>
                <input type="date" id="end" name="end" class="form-control mr-sm-2">
                <button id="refresh-btn" class="btn btn-primary">Refresh</button>
            </div>
        </nav>

        <div class="chart-container">
            <canvas id="burndown-chat"></canvas>
        </div>

        <div class="table-container container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-bordered table-hover table-sm">
                        <thead>
                        <tr>
                            <th style="width: 50%;">Name</th>
                            <th style="width: 35%;">Due Date</th>
                            <th>Estimated Time</th>
                        </tr>
                        </thead>
                        <tbody id="incomplate-task-table"></tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="table table-bordered table-hover table-sm">
                        <thead>
                        <tr>
                            <th style="width: 50%;">Name</th>
                            <th style="width: 35%;">Completion Date</th>
                            <th>Estimated Time</th>
                        </tr>
                        </thead>
                        <tbody id="complated-task-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <script>
        $("input").change(function () {
            update()
        })

        function update() {
            const start = $("#start").val()
            const end = $("#end").val()

            if (start === "" || end === "" || start > end) {
                return
            }

            setCookie("startDate", start, 30)
            setCookie("endDate", end, 30)

            const startDate = new Date(start + "T00:00:00")
            const endDate = new Date(end + "T23:59:59")

            $.get("/api/tasks?start=" + startDate.toISOString() + "&end=" + endDate.toISOString(), function (data) {
                process(data, startDate, endDate)
            })
        }

        function process(data, startDate, endDate) {
            let task

            let completed = [];
            let incomplete = [];

            let totalMinutes = 0;

            for (task of data) {
                if (task.completionDate == null) {
                    incomplete.push(task)
                } else {
                    completed.push(task)
                }

                if (task.estimatedMinutes !== null) {
                    totalMinutes += task.estimatedMinutes
                }
            }

            console.log(totalMinutes)

            let guidelineData = [{x: startDate, y: (totalMinutes / 60).toFixed(2)}]

            const days = Math.round((endDate - startDate) / 1000 / 3600 / 24)
            const minutesPerDay = totalMinutes / days

            for (let d = 1; d <= days; d ++) {
                guidelineData.push({x: startDate.addDays(d), y: ((totalMinutes - minutesPerDay * d) / 60).toFixed(2)})
            }

            console.log(guidelineData)

            completed.sort((a, b) => (a.completionDate > b.completionDate) ? 1 : -1)

            $("#incomplate-task-table").html("")
            $("#complated-task-table").html("")

            const now = new Date()

            for (task of incomplete) {
                let date = new Date(task.dueDate)
                let trClass = (date <= now) ? "table-warning" : ""
                let tdClass = (task.estimatedMinutes === null) ? "table-danger" : ""

                $("#incomplate-task-table").append(
                    `<tr class="${trClass}"><td>${task.name}</td><td>${date.toLocaleString()}</td><td class="${tdClass}">${formatTime(task.estimatedMinutes)}</td></tr>`
                )
            }

            let completedData = [{x: startDate, y: (totalMinutes / 60).toFixed(2)}]

            for (task of completed) {
                let date = new Date(task.completionDate)
                let tdClass = (task.estimatedMinutes === null) ? "table-danger" : ""

                $("#complated-task-table").append(
                    `<tr><td>${task.name}</td><td>${date.toLocaleString()}</td><td class="${tdClass}">${formatTime(task.estimatedMinutes)}</td></tr>`
                )

                if (task.estimatedMinutes !== null) {
                    completedData.push({x: date, y: (totalMinutes / 60).toFixed(2)})
                    totalMinutes -= task.estimatedMinutes
                    completedData.push({x: date, y: (totalMinutes / 60).toFixed(2)})
                }
            }

            if (now < endDate) {
                completedData.push({x: now, y: (totalMinutes / 60).toFixed(2)})
            } else {
                completedData.push({x: endDate, y: (totalMinutes / 60).toFixed(2)})
            }

            console.log(completedData)

            burndown_chart(guidelineData, completedData)
        }

        function burndown_chart(guidelineData, completedData) {
            $(".chart-container").html("<canvas id=\"burndown-chat\"></canvas>")

            const ctx = document.getElementById("burndown-chat").getContext('2d');

            var config = {
                type:    'line',
                data:    {
                    datasets: [
                        {
                            label: "Guideline",
                            data: guidelineData,
                            fill: false,
                            borderColor: 'grey'
                        },
                        {
                            label: "Remaining Values",
                            data:  completedData,
                            fill:  false,
                            borderColor: 'red',
                            lineTension: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales:     {
                        xAxes: [{
                            type:       "time",
                            time:       {
                                parser: 'DD/MM/YYYY',
                                tooltipFormat: 'll',
                                unit: 'day'
                            },
                            scaleLabel: {
                                display:     true,
                                labelString: 'Date'
                            }
                        }],
                        yAxes: [{
                            scaleLabel: {
                                display:     true,
                                labelString: 'value'
                            }
                        }]
                    }
                }
            };

            new Chart(ctx, config)
        }

        function setCookie(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            var expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for(var i = 0; i <ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        function checkCookit() {
            let start = getCookie("startDate")
            let end = getCookie("endDate")

            if (start !== "" && end !== "") {
                $("#start").val(start)
                $("#end").val(end)
                update()
            }
        }

        function formatTime(m) {
            const hour = Math.floor(m / 60)
            const minute = m % 60

            return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`
        }

        Date.prototype.addDays = function(days) {
            const date = new Date(this.valueOf());
            date.setDate(date.getDate() + days);
            return date;
        }

        $("#refresh-btn").click(function () {
            update()
        })

        checkCookit()

        setInterval(() => update(), 1000 * 60 * 15)
    </script>
    </body>
</html>
