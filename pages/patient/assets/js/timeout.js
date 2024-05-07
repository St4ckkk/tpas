function setupInactivityCheck() {
    var timeoutDuration = 60000;  // Set to 10 minutes in milliseconds
    var logoutTimer;

    function resetTimer() {
        clearTimeout(logoutTimer);  // Clear the previous timeout to reset the timer
        logoutTimer = setTimeout(function() {
            window.location.href = 'patientlogout.php?logout';  // Redirect to logout
        }, timeoutDuration);
    }

    // Register event listeners to reset the timer on various user actions
    function addEventListeners() {
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.ontouchstart = resetTimer;
    }

    // Initialize event listeners when the page loads
    addEventListeners();
}

// Call setupInactivityCheck to set everything up
setupInactivityCheck();
