<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TPAS - Home</title>
    <link href="index.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />
    <link href="assets/css/material.css" rel="stylesheet">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    
</head>
<style>
    .left-links img {
        background-color: #3e81ec;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 0px;
        width: 70px;
        height: 70px;
    }

    .left-links {
        font-weight: bold;
    }

    input[name="date"] {
        background-color: #fff !important;
    }

    .header {
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        padding: 0 20px;
        width: 100%;
    }

    .right-links {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .right-links button {
        background-color: #f5f6f8;
        color: #000;
        padding: 10px 40px;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: background-color 0.3s;
        font-weight: bold;
    }

    .right-links button:hover {
        background-color: #3e81ec;

    }

    .right-links button img {
        margin-left: 10px;
        width: 15px;
        height: auto;

    }

    .middle-links a {
        font-size: 16px;
    }

    .not-available {
        background-color: red;
        color: #fff;
        cursor: pointer;
    }

    .available-day {
        background-color: limegreen;
        color: #fff;
        cursor: pointer;
    }

    .datepicker-dropdown .datepicker-days .table-condensed tbody tr>td.day.available-day:hover {
        background-color: lime;
        color: #fff;
    }

    .datepicker-dropdown .datepicker-days .table-condensed tbody tr>td.day.not-available:hover {
        background-color: coral;
        color: #fff;
    }

    #s {
        color: #3e81ec;

    }
</style>

<body style="background-color: #fff">
    <div class="header">
        <ul class="left-links">
            <li class="tags brand">
                <img src="assets/img/cd-logoo.png"> TPA<span id="s">S</span>
            </li>
        </ul>
        <ul class="middle-links">
            <li class="tags home"><a href="#home">Home</a></li>
            <li class="tags"><a href="#about">About</a></li>
            <li class="tags"><a href="#features">Features</a></li>
            <li class="tags"><a href="#contact">Contact Us</a></li>
        </ul>
        <ul class="right-links">
            <button onclick="window.open('auth/patient/index.php', '_blank')">
                Get Started <img src="assets/img/up-right-arrow.png" alt="">
            </button>
        </ul>
    </div>


    <div class="container">
        <div class="hero">
            <div class="hero-cta">
                <h1>Welcome to TPAS</h1>
            </div>
            <div>
                <h3>Make an appointment today!</h3>
                <p>This is Doctor's Schedule. Please <span class="label label-danger">login</span> to make an appointment.</p>
                <div class="input-group" style="margin-bottom:10px;">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <input class="form-control" id="date" name="date" value="<?php echo date("Y-m-d"); ?>" onchange="showUser(this.value)" />
                </div>
                <div id="txtHint"><b></b></div>
            </div>

        </div>
        <div class="hero-image">
            <img src="assets/img/cd-home.png" class="img-responsive center-block" alt="Doctor" style="max-height: 500px; width: auto;">
        </div>
    </div>




    </div>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/date/bootstrap-datepicker.js"></script>
    <script src="assets/js/moment.js"></script>
    <script src="assets/js/transition.js"></script>
    <script src="assets/js/collapse.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $('#myModal').on('shown.bs.modal', function() {
            $('#myInput').focus()
        })
        $(document).ready(function() {
            var date_input = $('#date');
            var currentMonthData = {};

            function fetchMonthData(date) {
                var startOfMonth = date.startOf('month').format('YYYY-MM-DD');
                var endOfMonth = date.endOf('month').format('YYYY-MM-DD');
                $.ajax({
                    url: 'calendar.php',
                    data: {
                        start_date: startOfMonth,
                        end_date: endOfMonth
                    },
                    success: function(data) {
                        currentMonthData = data;
                        date_input.datepicker('update');
                    }
                });
            }

            date_input.datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                beforeShowDay: function(date) {
                    var formattedDate = moment(date).format('YYYY-MM-DD');
                    var status = currentMonthData[formattedDate];
                    if (status === 'limegreen') {
                        return {
                            classes: 'available-day',
                            tooltip: 'Doctor is Available'
                        };
                    } else if (status === 'red') {
                        return {
                            classes: 'not-available',
                            tooltip: 'Doctor is not Available'
                        };
                    }
                    return {};
                }
            }).on('changeMonth', function(e) {
                var monthDate = moment(e.date);
                console.log("Changing month to: ", monthDate.format('YYYY-MM-DD'));
                fetchMonthData(monthDate);
            }).on('changeDate', function(e) {
                // Your existing code to handle date changes
            });

            // Initially load the current month's data
            fetchMonthData(moment());
        });


        function showUser(str) {
            if (str == "") {
                document.getElementById("txtHint").innerHTML = "";
                return;
            } else {
                if (window.XMLHttpRequest) {
                    xmlhttp = new XMLHttpRequest();
                } else {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                        document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
                    }
                };
                xmlhttp.open("GET", "get-schedule.php?q=" + str, true);
                console.log(str);
                xmlhttp.send();
            }
        }
    </script>
</body>

</html>