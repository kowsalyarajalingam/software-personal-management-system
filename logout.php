<?php
// 1. Start the session so PHP knows which user is currently logged in
session_start();

// 2. Unset all session variables to clear the data
session_unset();

// 3. Destroy the session completely from the server
session_destroy();

// 4. Redirect the user back to the visual login page
header("Location: index.html");

// 5. Stop the script from running any further
exit();
?>