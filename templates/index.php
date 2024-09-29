<?php
// Allow cross-origin requests
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection details
$host = 'localhost';
$db = 'post_office';
$user = 'root';
$pass = 'astro';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch post office data along with cleanliness status
$sql = "
    SELECT p.name, p.Latitude, p.Longitude, p.Status, p.Compliance_Level, p.Unclean_Level, 
           pi.cleanliness_status, p.City, p.State 
    FROM post p
    LEFT JOIN processed_images pi ON p.name = pi.name"; // Join on the name column

$result = $conn->query($sql);

$post_offices = [];
$status_count = ['ALERT' => 0, 'SAFE' => 0];
$cities = [];
$states = [];
$dirty_count = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $post_offices[] = $row;
        if (isset($status_count[$row['Status']])) {
            $status_count[$row['Status']]++;
        }
        if (isset($row['cleanliness_status']) && $row['cleanliness_status'] === 'Dirty') {
            $dirty_count++; // Increment count for dirty status
        }

        // Collect unique cities and states safely
        if (isset($row['City'])) {
            $cities[] = $row['City'];
        }
        if (isset($row['State'])) {
            $states[] = $row['State'];
        }
    }
}

$conn->close();



// Get unique cities and states
$unique_cities = count(array_unique($cities));
$unique_states = count(array_unique($states));
$total_post_offices = count($post_offices);
$total_alerts = $dirty_count;

// Prepare data for top charts
$alert_post_offices = array_filter($post_offices, function($office) {
    return $office['Status'] === 'ALERT';
});

$safe_post_offices = array_filter($post_offices, function($office) {
    return $office['Status'] === 'SAFE';
});

// Sort and get top 5 compliant post offices with Compliance Levels from 0 to 9
$compliant_post_offices = array_filter($post_offices, function($office) {
    return $office['Compliance_Level'] >= 0 && $office['Compliance_Level'] <= 9;
});
usort($compliant_post_offices, function($a, $b) {
    return $b['Compliance_Level'] <=> $a['Compliance_Level'];
});
$top_compliant = array_slice($compliant_post_offices, 0, 5);

// Sort and get top 5 unclean post offices with Unclean Levels from 0 to 9
$unclean_post_offices = array_filter($post_offices, function($office) {
    return $office['Unclean_Level'] >= 0 && $office['Unclean_Level'] <= 9;
});
usort($unclean_post_offices, function($a, $b) {
    return $b['Unclean_Level'] <=> $a['Unclean_Level'];
});
$top_unclean = array_slice($unclean_post_offices, 0, 5);

// Get top 5 clean post offices with Unclean Levels between 0 and 5
$clean_post_offices = array_filter($post_offices, function($office) {
    return $office['Unclean_Level'] >= 0 && $office['Unclean_Level'] <= 5;
});
usort($clean_post_offices, function($a, $b) {
    return $a['Unclean_Level'] <=> $b['Unclean_Level'];
});
$top_clean_post_offices = array_slice($clean_post_offices, 0, 5);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> GreenPostAI</title>
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        #map {
            height: 400px; 
            margin-top: 70px; /* Ensure no overlap with the navbar */
        }
        .chart-container {
            display: flex; /* Use flexbox for side-by-side layout */
            justify-content: space-between; /* Space out the charts */
            margin: 20px 0; /* Margin for spacing */
            flex-wrap: wrap; /* Allow wrapping for smaller screens */
        }
        .chart-box {
            width: 30%; /* Adjusted width for each chart box */
            background-color: #f9f9f9; /* Light background */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Box shadow for 3D effect */
            padding: 15px;
            margin: 20px 0; /* Extra margin between the chart containers */
            border-radius: 10px; /* Rounded corners */
        }
        canvas {
            height: 130px; /* Fixed height for the charts */
            width: 100%; /* Make canvas take full width of chart box */
        }
        .info-box {
            display: inline-block;
            width: 23%; /* Adjust width as needed */
            padding: 10px;
            margin: 10px 1%;
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
        }
        .alert {
            background-color: #ffcccc; /* Light red for ALERT */
            border: 1px solid red;
        }
        h3 {
            width: 100%; /* Full width for headings */
            text-align: center; /* Centered headings */
            margin-bottom: 10px; /* Spacing below headings */
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="index.php" class="navbar-logo">
            <!-- Logo image added here -->
            <img src="logo.jpeg" alt="Logo">  GreenPostAI
        </a>
        <ul class="navbar-menu">
        <li><a href="life_practices.php">LiFE Practices</a></li>
            <li><a href="http://localhost:5000/">Live Image Feed</a></li>
            <li><a href="postoffice.php">Post Offices</a></li>
            <li><a href="Sign_In.php">Sign Out</a></li>
            
        </ul>
    </div>

    <div id="map"></div>

    <div class="info-boxes">
        <div class="info-box">Total Post Offices Monitored: <strong><?php echo $total_post_offices; ?></strong></div>
        <div class="info-box">Total Cities: <strong><?php echo $unique_cities; ?></strong></div>
        <div class="info-box">Total States: <strong><?php echo $unique_states; ?></strong></div>
        <div class="info-box alert">Total ALERT Count: <strong><?php echo $total_alerts; ?></strong></div>
    </div>

    <div class="chart-container">
        <div class="chart-box">
            <h3>Top 5 Compliant Post Offices</h3>
            <canvas id="complianceChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Top 5 Unclean Post Offices</h3>
            <canvas id="uncleanChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Top 5 Clean Post Offices</h3>
            <canvas id="cleanPostOfficesChart"></canvas>
        </div>
    </div>
    <script>
    function initMap() {
        const mumbai = { lat: 19.076, lng: 72.8777 };
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: mumbai,
        });

        const postOffices = <?php echo json_encode($post_offices); ?>;

        postOffices.forEach(postOffice => {
            const iconUrl = postOffice.cleanliness_status === 'Dirty' 
                ? 'garbage.svg'  // Use garbage icon for Dirty status
                : 'clean.svg';   // Use clean icon for Clean status

            const marker = new google.maps.Marker({
                position: { lat: parseFloat(postOffice.Latitude), lng: parseFloat(postOffice.Longitude) },
                map: map,
                title: postOffice.name,
                icon: {
                    url: iconUrl,
                    scaledSize: new google.maps.Size(50, 50), // Scale the icon size to 30x30 pixels
                }
            });
        });
    }
</script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDOYZvj20isw_zi_d1iuCazAKVoBgssNJY&callback=initMap">
</script>

<script>
    // Top 5 Compliant Post Offices Chart
    const compliantPostOffices = <?php echo json_encode($top_compliant); ?>;
    const complianceChartCtx = document.getElementById('complianceChart').getContext('2d');
    const complianceChart = new Chart(complianceChartCtx, {
        type: 'bar',
        data: {
            labels: compliantPostOffices.map(office => office.name),
            datasets: [{
                label: 'Compliance Level',
                data: compliantPostOffices.map(office => office.Compliance_Level),
                backgroundColor: ['#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107'],
                borderColor: '#333',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            }
        }
    });

    // Top 5 Unclean Post Offices Chart
    const uncleanPostOffices = <?php echo json_encode($top_unclean); ?>;
    const uncleanChartCtx = document.getElementById('uncleanChart').getContext('2d');
    const uncleanChart = new Chart(uncleanChartCtx, {
        type: 'bar',
        data: {
            labels: uncleanPostOffices.map(office => office.name),
            datasets: [{
                label: 'Unclean Level',
                data: uncleanPostOffices.map(office => office.Unclean_Level),
                backgroundColor: ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5'],
                borderColor: '#333',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            }
        }
    });

    // Top 5 Clean Post Offices Chart
    const cleanPostOffices = <?php echo json_encode($top_clean_post_offices); ?>;
    const cleanPostOfficesChartCtx = document.getElementById('cleanPostOfficesChart').getContext('2d');
    const cleanPostOfficesChart = new Chart(cleanPostOfficesChartCtx, {
        type: 'bar',  // Changed from 'pie' to 'bar'
        data: {
            labels: cleanPostOffices.map(office => office.name),
            datasets: [{
                label: 'Unclean Level',
                data: cleanPostOffices.map(office => office.Unclean_Level),
                backgroundColor: ['#2196f3', '#00bcd4', '#009688', '#4caf50', '#8bc34a'],
                borderColor: '#333',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10  // Set maximum value for the Y-axis
                }
            }
        }
    });
</script>

</body>
</html>
