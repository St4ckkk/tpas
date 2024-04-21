<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/signup.css">
    <title>Sign Up - Your Health Service</title>
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />
</head>

<body>
    <div class="container">
        <div class="signup__content">
            <img src="assets/img/lee-soo-hyun-QL-svdZCnYw-unsplash.jpg" alt="signup image" class="signup__img">
            <form action="signup.php" method="POST" class="signup__form">
                <h1 class="signup__title">TB Appointment Registration</h1>
                <div class="form-group">
                    <label for="firstname" class="signup__label">First Name</label>
                    <input type="text" id="firstname" name="patientFirstName" required>
                </div>
                <div class="form-group">
                    <label for="lastname" class="signup__label">Last Name</label>
                    <input type="text" id="lastname" name="patientLastName" required>
                </div>
                <div class="form-group">
                    <label for="email" class="signup__label">Email</label>
                    <input type="email" id="email" name="patientEmail" required>
                </div>
                <div class="form-group">
                    <label for="philhealth" class="signup__label">PhilHealth ID</label>
                    <input type="text" id="philhealth" name="philhealthId" required>
                </div>
                <div class="form-group">
                    <label for="password" class="signup__label">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="signup__label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="birthdate" class="signup__label">Birthdate</label>
                    <input type="date" id="birthdate" required>
                </div>
                <div class="form-group">
                    <span class="signup__label">Gender</span>
                    <label><input type="radio" name="patientGender" value="male" required> Male</label>
                    <label><input type="radio" name="patientGender" value="female" required> Female</label>
                </div>
                <div class="button-container">
                    <button type="submit" class="signup__button">Register</button>
                    <a href="signin.php" target="_blank" class="signin__link">Login</a>
                </div>
            </form>

        </div>
    </div>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/date/bootstrap-datepicker.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var date_input = $('input[name="date"]'); // Date input selector
            var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
            date_input.datepicker({
                format: 'yyyy-mm-dd',
                container: container,
                todayHighlight: true,
                autoclose: true,
            });
        });
    </script>
</body>

</html>