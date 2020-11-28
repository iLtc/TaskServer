<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Burndown Chart</title>

        <style>
            .table-container table {
                width: 100%;
                border-collapse: collapse;
            }
            .table-container table, th, td {
                border: 1px solid black;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="form-container">
            <label for="start">Start Date:</label>
            <input type="date" id="start" name="start">
            <label for="end">End Date:</label>
            <input type="date" id="end" name="end">
        </div>
        <div class="chart-container">
            <canvas id="burndown-chat"></canvas>
        </div>
        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Due Date</th>
                    <th>Estimated Minutes</th>
                </tr>
                </thead>
                <tbody id="incomplate-task-table"></tbody>
            </table>
            <hr>
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Completion Date</th>
                    <th>Estimated Minutes</th>
                </tr>
                </thead>
                <tbody id="complated-task-table"></tbody>
            </table>
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

            const startDate = new Date(start + " 00:00:00")
            const endDate = new Date(end + " 23:59:59")

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

            let guidelineData = [
                {x: startDate, y: totalMinutes},
                {x: endDate, y: 0}
            ]

            console.log(guidelineData)

            completed.sort((a, b) => (a.completionDate > b.completionDate) ? 1 : -1)

            $("#incomplate-task-table").html("")
            $("#complated-task-table").html("")

            for (task of incomplete) {
                let date = new Date(task.dueDate)

                $("#incomplate-task-table").append(
                    "<tr><td>" + task.name + "</td><td>" + date.toLocaleString() + "</td><td>" + task.estimatedMinutes + "</td></tr>"
                )
            }

            let completedData = [{x: startDate, y: totalMinutes}]

            for (task of completed) {
                let date = new Date(task.completionDate)

                $("#complated-task-table").append(
                    "<tr><td>" + task.name + "</td><td>" + date.toLocaleString() + "</td><td>" + task.estimatedMinutes + "</td></tr>"
                )

                if (task.estimatedMinutes !== null) {
                    completedData.push({x: date, y: totalMinutes})
                    totalMinutes -= task.estimatedMinutes
                    completedData.push({x: date, y: totalMinutes})
                }
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
                    title:      {
                        display: true,
                        text:    "Burndown Chart"
                    },
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

        checkCookit()
    </script>
    </body>
</html>
