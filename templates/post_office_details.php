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

// Check if the post office name is set in the session
if (isset($_SESSION['post_office_id'])) {
    $post_office_name = $_SESSION['post_office_id'];

    // Prepare SQL statement to fetch details from the processed_images table
    $sql = "SELECT Name, image_path, cleanliness_status, timestamp FROM processed_images WHERE Name='$post_office_name'";
    $result = $conn->query($sql);

    // If records are found, fetch them into an array
    if ($result->num_rows > 0) {
        $images_data = [];
        while ($row = $result->fetch_assoc()) {
            $images_data[] = $row; // Store each row in an array
        }
    } else {
        // If no records are found, set the images_data to an empty array
        $images_data = [];
    }
} else {
    // If no session is set, redirect to the sign-in page
    header("Location: Sign_In.php");
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($images_data) && count($images_data) > 0 ? htmlspecialchars($images_data[0]['Name']) : 'Post Office Images'; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="post_office_details.css">
    <style>
        .alert {
            position: fixed;
            top: 80px; /* Adjust as needed to position below navbar */
            right: 20px; /* Position on the right */
            background-color: red; /* Alert background color */
            color: white; /* Alert text color */
            padding: 10px 20px; /* Alert padding */
            border-radius: 5px; /* Rounded corners */
            z-index: 999; /* Ensure it stays above other elements */
            display: none; /* Hidden by default */
            font-weight: bold; /* Bold text style */
            transition: opacity 0.3s; /* Transition for smoothness */
        }

        .alert:hover {
            opacity: 0.8; /* Slightly transparent on hover */
            cursor: pointer; /* Change cursor to pointer on hover */
        }

        .centered-content {
            display: flex;
            flex-direction: column; /* Stack items vertically */
            align-items: center; /* Center horizontally */
            margin-top: 100px; /* Add space below the navbar */
        }

        h1 {
            margin-top: 30px; /* Move post office name down */
        }
    </style>
</head>
<body>
    
<div class="navbar">
    <a href="index.php" class="navbar-logo">
        <img src="logo.jpeg" alt="Logo">  GreenPostAI
    </a>
    <ul class="navbar-menu">
        <li><a href="">LiFE Practices</a></li>
        <li><a href="">Electricity Bill</a></li>
        <li><a href="">Water Bill</a></li>
        <li><a href="Sign_In.php">Sign Out</a></li>
    </ul>
</div>

<div class="container mt-5 centered-content">
    <h1><?php echo isset($images_data) && count($images_data) > 0 ? htmlspecialchars($images_data[0]['Name']) : 'Post Office Images'; ?></h1>

    <div class="alert" id="cleanlinessAlert">ALERT</div>

    <!-- Display the images if available -->
    <div class="mt-3">
        <?php if (!empty($images_data)): ?>
            <h3> Current Live Image Update:</h3>
            <?php foreach ($images_data as $image): ?>
                <div class="image-item mb-3">
                    <?php 
                        // Replace backslashes with forward slashes in the image path
                        $image_path = str_replace('\\', '/', htmlspecialchars(trim($image['image_path']))); 
                        $cleanliness_status = htmlspecialchars($image['cleanliness_status']);
                    ?>
                    <img src="<?php echo $image_path; ?>" alt="Post Office Image" style="width: 100%; max-width: 300px; margin: 5px;">
                    <p class="bold-text">Cleanliness Status: <?php echo $cleanliness_status; ?></p>
                    <p>Timestamp: <?php echo htmlspecialchars($image['timestamp']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>NO ALERTS.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Check if cleanliness status is dirty
    const imagesData = <?php echo json_encode($images_data); ?>;
    const alertElement = document.getElementById('cleanlinessAlert');

    // Show the alert if any image has cleanliness status "dirty"
    if (imagesData.length > 0 && imagesData.some(image => image.cleanliness_status.toLowerCase() === "dirty")) {
        alertElement.style.display = "block"; // Show alert
    }
</script>
</body>
</html>
