<?php
session_start(); // Start the session

// Database configuration
$host = "localhost"; // Change as needed
$user = "root"; // Database username
$password = "astro"; // Database password
$dbname = "post_office"; // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['Name']);
    $city = $conn->real_escape_string($_POST['City']);

    // Check if the user is admin
    if ($name === 'admin' && $city === 'abc') {
        // Redirect to index.php
        header("Location: index.php");
        exit();
    }

    // Prepare SQL statement to check user credentials
    $sql = "SELECT * FROM post WHERE Name='$name' AND City='$city'";
    $result = $conn->query($sql);

    // If a record is found, redirect to the post office details page
    if ($result->num_rows > 0) {
        // Fetch the details of the post office
        $post_office = $result->fetch_assoc();
        
        // Store the post office information in session variables
        $_SESSION['post_office_id'] = $post_office['Name']; // Assuming the primary key is 'Name'
        $_SESSION['Name'] = $post_office['Name']; // Store additional details as needed

        // Redirect to the dynamic post office details page
        header("Location: post_office_details.php?name=" . urlencode($post_office['Name']));
        exit();
    } else {
        // If no record is found, redirect back to the sign-in page with an error message
        header("Location: Sign_In.php?error=Invalid credentials");
        exit();
    }
}

$conn->close();
?>
