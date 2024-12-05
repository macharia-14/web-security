<?php
// Connect to the database
include("connection1.php");

// Retrieve the data
$sql = "SELECT * FROM art_submissions";
$result = mysqli_query($conn, $sql);

// Display the data
echo "<table>";
echo "<tr><th>Artist-id</th><th>Username</th><th>Art-name</th><th>Price</th><th>Email</th><th>Contacts</th><th>Art</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>".$row['artist_id']."</td><td>".$row['username']."</td><td>".$row['art_name']."</td><td>".$row['price']."</td><td>".$row['email']."</td><td>".$row['contact']."</td><td><img src='".$row['///C:/Users/BEN%20G/Desktop/art%20gallery/artsubmissions.html']."' width='100px'></td></tr>";
}
echo "</table>";

// Close the database connection
mysqli_close($conn);
?>
