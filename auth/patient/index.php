<?php
require_once './process/process.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/signin-signup.css">
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <title>Login & Register!</title>
</head>
<style>

</style>

<body>


    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login__form">
                <h1>Create Account</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
                <span>or use your email or Philhealth ID registration</span>
                <input type="text" name="firstname" placeholder="Firstname" required>
                <input type="text" name="lastname" placeholder="Lastname" required>
                <input type="text" name="philhealthId" placeholder="PhilHealth ID (optional)">
                <input type="text" name="phoneno" placeholder="Phone Number" required>
                <input type="email" name="email" placeholder="Email" required>
                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="Password" required onkeyup="checkPasswordStrength()">
                    <i class="bx bx-show" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
                </div>
                <div class="password-container">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required onkeyup="checkPasswordStrength()">
                    <i class="bx bx-show" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')"></i>
                </div>
                <div id="password-criteria">
                    <p id="match-check"><i class="bx bx-x"></i> Passwords match</p>
                    <p id="length-check"><i class="bx bx-x"></i> Minimum 8 characters</p>
                    <p id="lower-check"><i class="bx bx-x"></i> Contains a lowercase letter</p>
                    <p id="upper-check"><i class="bx bx-x"></i> Contains an uppercase letter</p>
                    <p id="number-check"><i class="bx bx-x"></i> Contains a number</p>
                    <p id="special-check"><i class="bx bx-x"></i> Contains a special character</p>
                </div>

                <button type="submit" name="register">Sign Up</button>
            </form>

        </div>
        <div class="form-container sign-in">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login__form">
                <h1>Sign In</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-instagram"></i></a>
                </div>
                <span>or use your email or PhilHealth ID</span>
                <input type="text" name="identifier" placeholder="Email or PhilHealth ID">
                <div class="password-container">
                    <input type="password" name="password" id="signin_password" placeholder="Password">
                    <i class="bx bx-show" id="toggleSignInPassword" onclick="togglePasswordVisibility('signin_password', 'toggleSignInPassword')"></i>
                </div>

                <a href="#">Forget Your Password?</a>
                <?php if (!empty($login_error)) : ?>
                    <div class="error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Sign in with your details to access your account and manage your appointments.</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>New to Our Service?</h1>
                    <p>Register to start booking your TB appointments and more.</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        function checkPasswordStrength() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            var passwordCriteria = document.getElementById("password-criteria");

            passwordCriteria.style.display = password.length > 0 ? 'block' : 'none';
            setTimeout(function() {
                passwordCriteria.style.opacity = password.length > 0 ? '1' : '0';
            }, 10);

            updateCriteria("length-check", password.length >= 8);
            updateCriteria("lower-check", /[a-z]/.test(password));
            updateCriteria("upper-check", /[A-Z]/.test(password));
            updateCriteria("number-check", /[0-9]/.test(password));
            updateCriteria("special-check", /[\W_]/.test(password));
            updateCriteria("match-check", password === confirmPassword);
        }

        function updateCriteria(id, isMet) {
            var element = document.getElementById(id);
            element.className = isMet ? "met" : "";
            element.children[0].className = isMet ? "bx bx-check" : "bx bx-x";
        }

        function togglePasswordVisibility(passwordInputId, toggleIconId) {
            var passwordInput = document.getElementById(passwordInputId);
            var toggleIcon = document.getElementById(toggleIconId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.className = 'bx bx-hide';
            } else {
                passwordInput.type = "password";
                toggleIcon.className = 'bx bx-show';
            }
        }
    </script>


</body>

</html>