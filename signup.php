<?php
session_start();

include("connection1.php");
//include("functions.php");

// Function to log events
function log_event($message)
{
	$log_file = __DIR__ . '/app_logs/app.log';
	if (!file_exists(dirname($log_file))) {
		mkdir(dirname($log_file), 0777, true);
	}
	$log_message = date('[Y-m-d H:i:s]') . " " . $message . PHP_EOL;
	file_put_contents($log_file, $log_message, FILE_APPEND);
}

//Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] == "POST") {

	//Sanitize and validate inputs
	$username = trim(mysqli_real_escape_string($conn, $_POST['username']));
	$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
	$phonenumber = trim(mysqli_real_escape_string($conn, $_POST['phonenumber']));
	$password = $_POST['password'];
	$confirm_password = $_POST['confirm_password'];


	//Input validation
	if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
		$error = "All fields are required.";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = "Invalid email format.";
	} elseif ($password !== $confirm_password) {
		$error = "Passwords do not match.";
	} else {
		//Check if username or email already exists
		$query = "SELECT id FROM sign_up WHERE username = ? OR email = ?";
		$stmt = mysqli_prepare($conn, $query);
		mysqli_stmt_bind_param($stmt, "ss", $username, $email);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		if (mysqli_nUm_rows($result) > 0) {
			$error = "Username or email is already taken.";
		} else {
			//Hash the password
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);

			//Insert the new user
			$insert_query = "INSERT INTO sign_up (username, email, phonenumber, password_hash) VALUES (?, ?, ?, ?)";
			$insert_stmt = mysqli_prepare($conn, $insert_query);
			mysqli_stmt_bind_param($insert_stmt, "ssss", $username, $email, $phonenumber, $hashed_password);

			if (mysqli_stmt_execute($insert_stmt)) {

				// Log the successful registration
				log_event("New user registered: Username={$username}, Email={$email}, IP={$_SERVER['REMOTE_ADDR']}");
				$_SESSION['username'] = $username;
				header("Location: /art/home.html ");
				exit();
			} else {
				$error = "Failed to register. Please try again.";
			}
		}
	}
}



?>


<!DOCTYPE html>
<html>

<head>
	<title>Sign Up | ARTHIVE</title>
	<link rel="stylesheet" href="index.css">
</head>

<body background="images/colourful dark-skinned female portrait_.jpg">
	<h1 style="font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif">ARTHIVE ART GALLERY</h1>
	<div class="signup-form">
		<form method="post" cass="sign-up">
			<h2>Sign Up</h2>

			<?php if (isset($error)) : ?>
				<div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>

			<label for="username">Username:</label>
			<input type="text" id="username" name="username" placeholder="Enter username" required><br><br>

			<label for="email">Email:</label>
			<input type="email" id="email" name="email" placeholder="example@email.com" required><br><br>

			<label for="phonenumber">Phone:</label>
			<input type="number" id="phonenumber" name="phonenumber" placeholder="(+254)123456789" required><br><br>

			<label for="password">Password:</label>
			<input type="password" id="password" name="password" placeholder="Enter password" required><br><br>

			<label for="confirm_password">Confirm Password:</label>
			<input type="password" id="confirm_password" name="confirm_password" placeholder="Enter password" required><br><br>

			<button type="submit">Sign Up</button>

			Already have an account?<a href="login.php">Sign in</a>
		</form>

	</div>



</body>

</html>