<?php
// Database connection (Replace with your own connection details)
$servername = "localhost";
$username = "root";
$password = "astro";
$dbname = "post_office"; // Make sure this database exists

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch data from API
function fetchPostOffices($searchQuery) {
    $apiUrl = is_numeric($searchQuery) ? 
        "https://api.postalpincode.in/pincode/$searchQuery" :
        "https://api.postalpincode.in/postoffice/$searchQuery";

    $response = file_get_contents($apiUrl);
    return json_decode($response, true);
}

// Function to fetch latitude and longitude from Google API
function fetchLatLong($address) {
    $apiKey = 'YOUR_GOOGLE_API_KEY'; // Replace with your Google API key
    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
    $response = file_get_contents($geocodeUrl);
    $data = json_decode($response, true);

    if (!empty($data['results'])) {
        $location = $data['results'][0]['geometry']['location'];
        return [
            'latitude' => $location['lat'],
            'longitude' => $location['lng']
        ];
    } else {
        return [
            'latitude' => null,
            'longitude' => null
        ];
    }
}

// Initialize variables
$searchQuery = '';
$postOffices = [];
$error = '';
$successMessage = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['searchQuery'])) {
    $searchQuery = $_POST['searchQuery'];

    if (empty($searchQuery)) {
        $error = 'Please enter a valid search query';
    } else {
        $data = fetchPostOffices($searchQuery);

        if ($data[0]['Status'] === 'Success') {
            foreach ($data[0]['PostOffice'] as $office) {
                $address = "{$office['Name']}, {$office['Block']}, {$office['District']}, {$office['State']}, {$office['Pincode']}";
                $location = fetchLatLong($address);
                $office['Latitude'] = $location['latitude'];
                $office['Longitude'] = $location['longitude'];
                $postOffices[] = $office;
            }
        } else {
            $error = 'No post offices found';
        }
    }
}

// Handle adding post office to database
if (isset($_POST['add_to_list'])) {
    $name = $_POST['name'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Check cleanliness status in processed_images table
    $cleanlinessStatus = '';
    $result = $conn->query("SELECT cleanliness_status FROM processed_images WHERE Name = '$name'");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cleanlinessStatus = $row['cleanliness_status'];
    }

    // Determine status based on cleanliness status
    if ($cleanlinessStatus === 'Dirty') {
        $status = 'ALERT';
    } elseif ($cleanlinessStatus === 'Clean') {
        $status = 'ALL GOOD';
    } else {
        $status = 'Loading'; // If cleanliness status is NULL
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO post (Name, City, State, Latitude, Longitude, Status) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("ssddss", $name, $city, $state, $latitude, $longitude, $status);

        if ($stmt->execute()) {
            $successMessage = 'Post Office added successfully';
        } else {
            $error = 'Error adding Post Office: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $error = "Failed to prepare the SQL statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search for Post Offices</title>
    <link rel="stylesheet" href="PostOffice.css"> <!-- Link to the CSS file -->
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="navbar-logo">
            <img src="logo.jpeg" alt="Logo"> GreenPostAI
        </a>
        <nav>
            <ul class="navbar-menu">
                <li><a href="http://localhost:5000/">Live Image Feed</a></li>
                <li><a href="life_practices.php">LiFE Practices</a></li>
                <li><a href="index.php">Home</a></li>
            </ul>
        </nav>
    </div>

    <div class="post-office-page">
        <h2>Search for Post Offices</h2>
        <form method="post">
            <div class="search-container">
                <input type="text" name="searchQuery" placeholder="Enter Pincode or Post Office Name" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <p class="success-message"><?php echo $successMessage; ?></p>
        <?php endif; ?>

        <table class="post-office-list">
            <?php if (!empty($postOffices)): ?>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Block</th>
                        <th>District</th>
                        <th>Pincode</th>
                        <th>State</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($postOffices as $office): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($office['Name']); ?></td>
                            <td><?php echo htmlspecialchars($office['Block']); ?></td>
                            <td><?php echo htmlspecialchars($office['District']); ?></td>
                            <td><?php echo htmlspecialchars($office['Pincode']); ?></td>
                            <td><?php echo htmlspecialchars($office['State']); ?></td>
                            <td><?php echo htmlspecialchars($office['Latitude']); ?></td>
                            <td><?php echo htmlspecialchars($office['Longitude']); ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($office['Name']); ?>">
                                    <input type="hidden" name="city" value="<?php echo htmlspecialchars($office['District']); ?>">
                                    <input type="hidden" name="state" value="<?php echo htmlspecialchars($office['State']); ?>">
                                    <input type="hidden" name="latitude" value="<?php echo htmlspecialchars($office['Latitude']); ?>">
                                    <input type="hidden" name="longitude" value="<?php echo htmlspecialchars($office['Longitude']); ?>">
                                    <button type="submit" name="add_to_list" class="search-button">Add to Database</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php else: ?>
                <tr>
                    <td colspan="8">No post offices available</td>
                </tr>
            <?php endif; ?>
        </table>

        <div class="added-post-offices">
    <h2>Live Feed of Post Offices</h2>
    <table class="added-post-offices-list">
        <thead>
            <tr>
                <th>Name</th>
                <th>Cleanliness Status</th>
                <th>Timestamp</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch added post offices from the processed_images table
            $result = $conn->query("SELECT Name, image_path, cleanliness_status, timestamp, latitude, longitude FROM processed_images");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>
                            <a href='post_office_detail.php?name=" . urlencode($row['Name']) . "'>
                                " . htmlspecialchars($row['Name']) . "
                            </a>
                        </td>
                        <td>" . htmlspecialchars($row['cleanliness_status']) . "</td>
                        <td>" . htmlspecialchars($row['timestamp']) . "</td>
                        <td>" . htmlspecialchars($row['latitude']) . "</td>
                        <td>" . htmlspecialchars($row['longitude']) . "</td>
                        <td><a href='" . htmlspecialchars($row['image_path']) . "' target='_blank' class='search-button'>View Live Image</a></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No post offices have been added from processed images yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

    </div>
</body>
</html>

<?php
$conn->close();
?>
