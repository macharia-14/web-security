<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("connection1.php");

//Secure logging function
function log_event($message)
{
  $log_file = __DIR__ . '/app_logs/app.log'; //Define log path
  if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0777, true);
  }


  //Encryption settings
  $encryption_key = " #Gh0streacon23";
  $iv = "1234567890123456";

  //Encrypt the message
  $encrypted_message = openssl_encrypt(
    $message,
    'aes-256-cbc',
    $encryption_key,
    0,
    $iv
  );

  if ($encrypted_message === false) {
    error_log("Failed to encrypt log message.");
    return;
  }
  $log_message = date('[Y-m-d H:i:s]') . " " . $encrypted_message . PHP_EOL;
  file_put_contents($log_file, $log_message, FILE_APPEND);
}

//Constants for brute force mitigation
define('MAX_ATTEMPTS', 5);
define('LOCKOUT_TIME', 15 * 60); //15minutes


// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Get the username and password from the form
  $username = trim($_POST['username']);
  $password = isset($_POST['password']) ? $_POST['password'] : '';


  //Validate inputs
  if (empty($username) || empty($password)) {
    $error = 'Username and password are required.';
  } else {

    //Prepare SQL statement to prevent SQL injection
    $sql = "SELECT password_hash, failed_attempts, lockout_time FROM sign_up WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
      echo "No user found. Please check your username.";
      exit;
    }

    if ($result->num_rows === 1) {


      // Check if account is locked due to failed login attempts
      if ($row['failed_attempts'] >= MAX_ATTEMPTS) {
        $lockout_time = $row['lockout_time'];
        if (time() - strtotime($lockout_time) < LOCKOUT_TIME) {
          $error = "Your account is locked. Please try again later.";
          log_event("Login attempt blocked due to lockout: Username={$username}, IP={$_SERVER['REMOTE_ADDR']}");
          exit;
        } else {

          // Reset failed attempts if lockout time has passed
          $reset_sql = "UPDATE sign_up SET failed_attempts = 0, lockout_time = NULL WHERE username = ?";
          $reset_stmt = $conn->prepare($reset_sql);
          $reset_stmt->bind_param("s", $username);
          $reset_stmt->execute();
          log_event("Lockout expired, failed attempts reset: Username={$username}, IP={$_SERVER['REMOTE_ADDR']}");
        }
      }

      //Verify the password
      if ($row && password_verify($password, $row['password_hash'])) {
        echo "Login successful!";

        //Reset failed attempts on successful login
        $update_sql = "UPDATE sign_up SET failed_attempts = 0, lockout_time = NULL WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $username);
        $update_stmt->execute();

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
        $update_sql = "UPDATE sign_up SET failed_attempts = failed_attempts + 1 WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $username);
        $update_stmt->execute();

        if ($row['failed_attempts'] + 1 >= MAX_ATTEMPTS) {
          $lockout_sql = "UPDATE sign_up SET lockout_time = NOW() WHERE username = ?";
          $lockout_stmt = $conn->prepare($lockout_sql);
          $lockout_stmt->bind_param("s", $username);
          $lockout_stmt->execute();
        }

        //Log failed login due to incorrect password
        log_event("Failed login: Incorrect password for Username={$username}, IP={$_SERVER['REMOTE_ADDR']}");
        $error = 'Wrong password, please try again!';
      }
    } else {
      //Log failed login due to unknown username
      log_event("Failed Login: Username not found ({$username}), IP={$_SERVER['REMOTE_ADDR']}");
      $error = 'Invalid username';
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

  <h1>ARTHIVE ART GALLERY</h1>
  <div class="login-form">
    <form method="post">
      <h2>Login</h2>

      <?php if (isset($error)) : ?>
        <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <label for="username">Username:</label>
      <input type="text" id="username" name="username" placeholder="Enter username" required><br><br>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

      <button type="submit">Login</button>

      Don't have an account? <a href="signup.php">Sign up</a>
    </form>
  </div>

</body>

</html>