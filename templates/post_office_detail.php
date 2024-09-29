<?php
// Function to read the CSV file and return data for the selected post office
function getPostOfficeData($postOfficeName) {
    $filePath = 'post_offices_with_garbage_data.csv';
    $data = [];

    if (($file = fopen($filePath, 'r')) !== FALSE) {
        // Read the CSV file
        while (($row = fgetcsv($file)) !== FALSE) {
            if ($row[0] === $postOfficeName) { // Assuming the first column is the post office name
                $data[] = $row; // Collect data for the matching post office
            }
        }
        fclose($file);
    }

    return $data;
}

// Get the post office name from the URL
$postOfficeName = htmlspecialchars($_GET['name']);
$postOfficeData = getPostOfficeData($postOfficeName);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Office Detail</title>
    <link rel="stylesheet" href="postoffice.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Your existing CSS styles */
        .navbar {
    background-color: black; /* Black background */
    height: 70px; /* Height of the navbar */
    padding: 10px 40px; /* Padding for navbar */
    position: fixed; /* Keeps the navbar fixed on the page */
    top: 0;
    width: 100%; /* Full width */
    z-index: 1000; /* Keeps navbar on top of other content */
    display: flex;
    align-items: center; /* Vertically center items */
    justify-content: space-between; /* Space between items */
    box-sizing: border-box; /* Ensures padding doesn't increase height */
}

.navbar-logo {
    position: relative;
    bottom: 6px;
    right: 40px;
    font-size: 24px; /* Logo font size */
    color: #06D001; /* Green logo color */
    text-decoration: none; /* No underline */
    font-weight: 900; /* Extra bold */
}
.navbar-logo img {
    position: relative;
    bottom: 12px;
    right: 5px;
    left: 4px;
    height: 70px; /* Adjust the height of the logo */
    margin-right:2px; /* Space between logo and text */
    vertical-align: middle; /* Align logo with text */
}

.navbar-menu {
    position: relative;
    bottom: 4px;
    list-style: none; /* No bullets for the list */
    display: flex; /* Display items in a row */
    margin: 0; /* No margin */
    padding: 0; /* No padding */
}

.navbar-menu li {
    margin: 0 20px; /* Margin between menu items */
}

.navbar-menu li a {
    color: #06D001; /* Green text */
    text-decoration: none; /* No underline */
    font-size: 18px; /* Font size */
    padding: 12px 15px; /* Padding inside each link */
    display: block; /* Makes the entire area clickable */
    font-weight: bold; /* Bold text */
    transition: color 0.3s ease, font-weight 0.3s ease; /* Smooth transition */
}

.navbar-menu li a:hover {
    color: #ffcc00; /* Change text color on hover */
    font-weight: bold; /* Bold text on hover */
}
        .chart-row {
            display: flex;
            justify-content: space-around;
            margin-top: 80px; /* Space below navbar */
        }

        .chart-container {
    flex: 1;
    padding: 10px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transition for hover effects */
}

.chart-container:hover {
    transform: translateY(-5px); /* Lift effect on hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
}

    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="navbar-logo">
            <img src="logo.jpeg" alt="Logo">  GreenPostAI
        </a>
        <nav>
            <ul class="navbar-menu">
                <li><a href="http://localhost:5000/">Live Image Feed</a></li>
                <li><a href="postoffice.php">Post Offices</a></li>
                <li><a href="life_practices.php">LiFE Practices</a></li>
            </ul>
        </nav>
    </div>

    <div class="container mt-5 pt-5">
        <h2>Details for Post Office: <?php echo $postOfficeName; ?></h2>

        <div class="chart-row">
            <div class="chart-container">
                <h3>Garbage Category Distribution</h3>
                <canvas id="garbageCategoryChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>Garbage Volume vs. Time</h3>
                <canvas id="garbageVolumeChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>Comparison of Garbage Detections Over 4 Weeks</h3>
                <canvas id="garbageDetectionChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Function to generate random colors
        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Prepare data for charts based on PHP variable
        const postOfficeData = <?php echo json_encode($postOfficeData); ?>;

        function parseGarbageData(data) {
            const garbageCategoryData = {};
            const garbageDetections = [0, 0, 0, 0]; // For 4 weeks

            data.forEach(row => {
                const garbageCategory = row[5]; // Assuming this is the garbage category
                const detections = [
                    parseInt(row[7]), // Week 1
                    parseInt(row[8]), // Week 2
                    parseInt(row[9]), // Week 3
                    parseInt(row[10])  // Week 4
                ];

                // Accumulate garbage categories
                garbageCategoryData[garbageCategory] = (garbageCategoryData[garbageCategory] || 0) + 1;

                // Accumulate detections
                detections.forEach((detection, index) => {
                    garbageDetections[index] += detection;
                });
            });

            return { garbageCategoryData, garbageDetections };
        }

        function renderCharts() {
            const parsedData = parseGarbageData(postOfficeData);

            // Render Garbage Category Distribution Chart
            const categoryCtx = document.getElementById('garbageCategoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(parsedData.garbageCategoryData),
                    datasets: [{
                        data: Object.values(parsedData.garbageCategoryData),
                        backgroundColor: Object.keys(parsedData.garbageCategoryData).map(() => getRandomColor()),
                    }]
                }
            });

            // Render Garbage Volume vs. Time Chart
            const volumeCtx = document.getElementById('garbageVolumeChart').getContext('2d');
            new Chart(volumeCtx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Garbage Detections',
                        data: parsedData.garbageDetections,
                        borderColor: getRandomColor(),
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Render Comparison of Garbage Detections Over 4 Weeks Chart
            const detectionCtx = document.getElementById('garbageDetectionChart').getContext('2d');
            new Chart(detectionCtx, {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Garbage Detections',
                        data: parsedData.garbageDetections,
                        backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)', 'rgba(255, 206, 86, 0.6)', 'rgba(54, 162, 235, 0.6)'],
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Render the charts after the page loads
        window.onload = function() {
            renderCharts();
        };
    </script>
</body>
</html>
