    <?php
    session_start();
    include_once '../assets/conn/dbconnect.php';
    if (!isset($_SESSION['doctorSession'])) {
        header("Location: ../index.php");
    }
    $usersession = $_SESSION['doctorSession'];
    $res3 = mysqli_query($con, "SELECT * FROM doctor WHERE doctorId=" . $usersession);
    $userRow3 = mysqli_fetch_array($res3, MYSQLI_ASSOC);
    $res = mysqli_query($con, "SELECT * FROM patient WHERE philHealthId=" . $_GET['philhealthId']);
    $userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $res1 = mysqli_query($con, "SELECT * FROM appointment WHERE philHealthId=" . $_GET['philhealthId']);
    $userRow1 = mysqli_fetch_array($res1, MYSQLI_ASSOC);

    if (isset($_POST['prescription'])) {
        $philhealthId = mysqli_real_escape_string($con, $userRow['philhealthId']);
        $icDoctor = mysqli_real_escape_string($con, $userRow3['icDoctor']);
        $medication = mysqli_real_escape_string($con, $_POST['medication']);
        $dosage = mysqli_real_escape_string($con, $_POST['dosage']);
        $comment = mysqli_real_escape_string($con, $_POST['comment']);
        $instructions = mysqli_real_escape_string($con, $_POST['instructions']);

        // Insert data into prenatalprescription table
        $insertPrescriptionQuery = "INSERT INTO prenatalprescription (philhealthId, medication, icDoctor, dosage, comment, instructions)
                                VALUES ('$philhealthId', '$medication', '$icDoctor', '$dosage', '$comment', '$instructions')";
        $result = mysqli_query($con, $insertPrescriptionQuery);

        if ($result) {
    ?>
            <script type="text/javascript">
                alert('Prescription Submitted successfully.');
            </script>
        <?php
        } else {
        ?>
            <script type="text/javascript">
                alert('Submitted fail. Please try again.');
            </script>
    <?php
        }
    }
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

        <title>Prenatal Prescription</title>
        <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="../assets/css/default/style.css" rel="stylesheet">
        <link href="../assets/css/default/blocks.css" rcel="stylesheet">


        <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />

    </head>

    <body>
        <!-- navigation -->
        <!-- navigation -->
        <div class="container">

            <section style="padding-bottom: 50px; padding-top: 50px;">
                <div class="row">
                    <!-- start -->
                    <!-- USER PROFILE ROW STARTS-->

                    <div class="row">
                        <div class="col-md-3 col-sm-3">

                            <div class="user-wrapper">
                                <img src="../patient/assets/img/patient.png" class="img-responsive" />
                                <div class="description">
                                    <h4><?php echo $userRow['patientFirstName']; ?> <?php echo $userRow['patientLastName']; ?></h4>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-9 col-sm-9  user-wrapper">

                            <div class="description">


                                <div class="panel panel-default">
                                    <div class="panel-body">


                                        <div class="panel panel-default">
                                            <div class="panel-heading">Patient Information</div>
                                            <div class="panel-body">
                                                Patient Name: <?php echo $userRow['patientFirstName'] ?> <?php echo $userRow['patientLastName'] ?><br>
                                                Philhealth ID: <?php echo $userRow['philhealthId'] ?><br>
                                                Contact Number: <?php echo $userRow['patientPhone'] ?><br>
                                                Address: <?php echo $userRow['patientAddress'] ?><br>
                                                Symptoms/Concerns: <?php echo $userRow1['appSymptom'] ?><br>
                                                Comments: <?php echo $userRow1['appComment'] ?><br>
                                                Pregnancy Week: <?php echo $userRow1['pregnancyWeek'] ?><br>
                                                Weight: <?php echo $userRow1['weight'] ?><br>
                                                Blood Pressure: <?php echo $userRow1['bloodPressure'] ?><br>
                                            </div>
                                        </div>

                                        <form action="" method="POST">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">Prescription Information</div>
                                                <div class="panel-body">
                                                    <input type="hidden" name="icDoctor" value="<?php echo $userRow3['icDoctor'] ?>">
                                                    <input type="hidden" name="philhealthId" value="<?php echo $userRow['philhealthId'] ?>">
                                                    <div class="form-group">
                                                        <label for="medication">Medication:</label>
                                                        <input type="text" class="form-control" name="medication" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="dosage">Dosage:</label>
                                                        <input type="text" class="form-control" name="dosage" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="message-text" class="control-label">Comment:</label>
                                                        <textarea class="form-control" name="comment" required></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="instructions">Instructions:</label>
                                                        <textarea class="form-control" name="instructions" rows="3" required></textarea>
                                                    </div>

                                                    <button type="submit" name="prescription" class="btn btn-primary">Submit Prescription</button>


                                                </div>
                                            </div>
                                        </form>
                                    </div>


                                    </form>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <!-- USER PROFILE ROW END-->
                <!-- end -->
                <script src="../patient/assets/js/jquery.js"></script>

                <!-- Bootstrap Core JavaScript -->
                <script src="../patient/assets/js/bootstrap.min.js"></script>
                <script src="assets/js/bootstrap-clockpicker.js"></script>
                <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css" />
                <script>
                    $(document).ready(function() {
                        var date_input = $('input[name="date"]'); //our date input has the name "date"
                        var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
                        date_input.datepicker({
                            format: 'yyyy/mm/dd',
                            container: container,
                            todayHighlight: true,
                            autoclose: true,
                        })
                    })
                </script>
                <script type="text/javascript">
                    /*
                Please consider that the JS part isn't production ready at all, I just code it to show the concept of merging filters and titles together !
                */
                    $(document).ready(function() {
                        $('.filterable .btn-filter').click(function() {
                            var $panel = $(this).parents('.filterable'),
                                $filters = $panel.find('.filters input'),
                                $tbody = $panel.find('.table tbody');
                            if ($filters.prop('disabled') == true) {
                                $filters.prop('disabled', false);
                                $filters.first().focus();
                            } else {
                                $filters.val('').prop('disabled', true);
                                $tbody.find('.no-result').remove();
                                $tbody.find('tr').show();
                            }
                        });

                        $('.filterable .filters input').keyup(function(e) {
                            /* Ignore tab key */
                            var code = e.keyCode || e.which;
                            if (code == '9') return;
                            /* Useful DOM data and selectors */
                            var $input = $(this),
                                inputContent = $input.val().toLowerCase(),
                                $panel = $input.parents('.filterable'),
                                column = $panel.find('.filters th').index($input.parents('th')),
                                $table = $panel.find('.table'),
                                $rows = $table.find('tbody tr');
                            /* Dirtiest filter function ever ;) */
                            var $filteredRows = $rows.filter(function() {
                                var value = $(this).find('td').eq(column).text().toLowerCase();
                                return value.indexOf(inputContent) === -1;
                            });
                            /* Clean previous no-result if exist */
                            $table.find('tbody .no-result').remove();
                            /* Show all rows, hide filtered ones (never do that outside of a demo ! xD) */
                            $rows.show();
                            $filteredRows.hide();
                            /* Prepend no-result row if all rows are filtered */
                            if ($filteredRows.length === $rows.length) {
                                $table.find('tbody').prepend($('<tr class="no-result text-center"><td colspan="' + $table.find('.filters th').length + '">No result found</td></tr>'));
                            }
                        });
                    });
                </script>
                <script src="assets/js/jquery.js"></script>
                <script src="assets/js/bootstrap.min.js"></script>
    </body>

    </html>