<?php
session_start();

// Connect to the database
include("connection1.php");
//Secure logging function
function log_event($message)
{
  $log_file = __DIR__ . '/app_logs/app.log'; //Define log path
  if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0777, true);
  }
  $log_message = date('[Y-m-d H:i:s]') . " " . $message . PHP_EOL;
  file_put_contents($log_file, $log_message, FILE_APPEND);
}

//Constants for brute force mitigation
define('MAX_ATTEMPTS', 5);
define('LOCKOUT_TIME', 15 * 60); //15minutes


// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Get the username and password from the form
  $username = trim($_POST['username']);
  $password = $_POST['password'];


  //Validate inputs
  if (empty($username) || empty($password)) {
    $error = 'Username and password are required.';
  } else {

    //Prepare SQL statement to prevent SQL injection
    $sql = "SELECT * FROM Artist WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $row = $result->fetch_assoc();

      //Verify the password
      if (password_verify($password, $row['password'])) {

        //Reset failed attempts on successful login
        $reset_sql = "UPDATE Artist SET failed_attempts = 0, lockout_time = NULL WHERE username = ?";
        $reset_stmt = $conn->prepare($reset_sql);
        $reset_stmt->bind_param("s", $username);
        $reset_stmt->execute();

        //Log successful login
        log_event("Successful login: Username={$username}, IP={$_SERVER['REMOTE_ADDR']}");

        //Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        //Store session information securely
        $_SESSION['username'] = $username;
        header('Location: /art/home.html');
        exit();
      } else {

        //Increment failed attempts
        $failed_attempts = $row['failed_attempts'] + 1;
        $lockout_time = $failed_attempts >= MAX_ATTEMPTS ? time() : $row['lockout_time'];
        $update_sql = "UPDATE Artist SET failed_attempts = ?, lockout_time = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iis", $failed_attempts, $lockout_time, $username);
        $update_stmt->execute();

        //Log failed login due to incorrect password
        log_event("Failed login: Incorrect password for Username={$username}, IP={$_SERVER['REMOTE_ADDR']}");
        $error = 'Invalid username or password.';
      }
    } else {
      //Log failed login due to unknown username
      log_event("Failed Login: Username not found ({$username}), IP={$_SERVER['REMOTE_ADDR']}");
      $error = 'Invalid username or password.';
    }

    $stmt->close();
  }
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>Login Form</title>
</head>

<body>

  <h1>Login Form</h1>

  <?php if (isset($error)): ?>
    <div style="color: red;"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Username:</label>
    <input type="text" name="username"><br><br>

    <label>Password:</label>
    <input type="password" name="password"><br><br>

    <input type="submit" value="Login">
  </form>

</body>

</html>