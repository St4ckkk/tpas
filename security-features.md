### System Logs
```php
function log_action($con, $accountNumber, $actionDescription, $userType)
{
    $currentDateTime = date('Y-m-d g:i A');
    $sql = "INSERT INTO logs (accountNumber, actionDescription, userType, dateTime) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $accountNumber, $actionDescription, $userType, $currentDateTime);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing log statement: " . $con->error);
    }
}
//call the function in your code
```

### Login Attempts
```php
$failedAttempts = $row['login_attempts'] + 1;
                        if ($failedAttempts >= 5) {
                            $lockoutTime = date('Y-m-d H:i:s', strtotime("+5 minutes"));
                            $con->query("UPDATE tb_patients SET login_attempts = $failedAttempts, lock_until = '$lockoutTime' WHERE patientId = {$row['patientId']}");
                            $login_error = 'Your account has been locked due to too many failed attempts. Please try again in 5 minutes.';
                        } else {
                            $con->query("UPDATE tb_patients SET login_attempts = $failedAttempts WHERE patientId = {$row['patientId']}");
                            $login_error = 'Incorrect password. Please try again.';
                        }

//put this in your login process
```

### CHATGPT PROMPT
- integrate this <mycode> to this <your code>

