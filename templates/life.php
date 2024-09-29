<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Office Energy Data</title>
    <link rel="stylesheet" href="postoffice.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Navbar styles */
        .navbar {
    background-color: black; /* Black background */
    height: 70px; /* Height of the navbar */
    padding: 10px 40px; /* Padding for navbar */
    position: fixed; /* Keeps the navbar fixed on the page */
    top: 0;
    bottom: 2px;
    width: 100%; /* Full width */
    z-index: 1000; /* Keeps navbar on top of other content */
    display: flex;
    align-items: center; /* Vertically center items */
    justify-content: space-between; /* Space between items */
    box-sizing: border-box; /* Ensures padding doesn't increase height */
}

        .navbar-logo {
    position: relative;
    bottom : 20px;
    right: 20px;
    font-size: 26px; /* Logo font size */
    color: #06D001; /* Green logo color */
    text-decoration: none; /* No underline */
    font-weight: 900; /* Extra bold */
}
        .navbar-logo img {
    position: relative;
    right: 9px;
    bottom: 7px;
    height: 65px; /* Adjust the height of the logo */
    margin-right:1px; /* Space between logo and text */
    vertical-align: middle; /* Align logo with text */
}

        
        .navbar.sticky {
            background-color: #444; /* Change color when sticky */
            position: sticky;
            top: 0; /* Stick to the top */
            z-index: 1000; /* Ensure it's above other content */
        }
        .navbar-menu li {
            display: inline;
            margin-right: 20px;
        }
        .navbar-menu a {
            color: white;
            text-decoration: none;
        }
        .navbar-menu a:hover {
            text-decoration: underline;
        }

        /* Carousel styles */
        .carousel-container {
            width: 100%;
            margin: 0 auto;
            height: 400px;
            position: relative;
            overflow: hidden;
        }
        .carousel-item img {
            height: 100%;
            object-fit: contain;
            width: 100%;
        }

        /* Chart container */
        .chart-container {
            width: 70%; /* Full width */
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Box shadow */
        }

        /* Chart styles */
        .chart-container canvas {
            max-width: 100%;
        }

        /* Set smaller height for the least units consumed bar graph */
        #leastUnitsConsumedChart {
            height: 150px;
            border-color: black;
            border-width: 1; /* Adjust this value to make the bar graph smaller */
        }
    </style>
</head>
<body>
    <div class="navbar">
    <a href="dashboard.php" class="navbar-logo">
            <!-- Logo image added here -->
            <img src="logo.jpeg" alt="Logo"> Swachhta & LiFE
        </a>
        <nav>
            <ul class="navbar-menu">
                <li><a href="#analytics">Analytics</a></li>
                <li><a href="http://localhost:5000/">Live Image Feed</a></li>
                <li><a href="postoffice.php">Post Offices</a></li>
                <li><a href="life_practices.php">LiFE Practices</a></li>
                <li><a href="#about-us">About Us</a></li>
            </ul>
        </nav>
    </div>

    <!-- Carousel Section -->
    <div class="carousel-container">
        <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="gg.jpeg" class="d-block" alt="Life Image 1">
                </div>
                <div class="carousel-item">
                    <img src="uu.jpeg" class="d-block" alt="Life Image 2">
                </div>
                <div class="carousel-item">
                    <img src="life6.jpeg" class="d-block" alt="Life Image 3">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleFade" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleFade" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleFade" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
        </div>
    </div>

    <!-- Charts Section for Least Units Consumed and Solar Panel Usage Side by Side -->
    <div class="container">
        <div class="row">
            <!-- Bar Chart Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Post Offices with Least Units Consumed</h3>
                    <canvas id="leastUnitsConsumedChart"></canvas>
                </div>
            </div>
            <!-- Pie Chart Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Post Offices Using Solar Panels</h3>
                    <canvas id="solarPanelUsageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    // Sticky Navbar on Scroll
    window.onscroll = function() {
        const navbar = document.querySelector('.navbar');
        if (window.pageYOffset > 0) {
            navbar.classList.add('sticky');
        } else {
            navbar.classList.remove('sticky');
        }
    };

    // Fetch and process the CSV file
    async function fetchCSV(url) {
        const response = await fetch(url);
        if (!response.ok) {
            console.error('Error fetching CSV:', response.statusText);
            return null; // Return null if fetch fails
        }
        const data = await response.text();
        return data;
    }

    function parseCSV(data) {
        const rows = data.split("\n").slice(1);
        const leastUnitsConsumedData = [];
        let solarPanelsCount = 0;
        let noSolarPanelsCount = 0;

        rows.forEach(row => {
            const columns = row.split(",");
            if (columns.length >= 10) {
                const postOfficeName = columns[0];
                const solarPanelsUsed = columns[8];
                const unitsConsumed = columns[5];

                leastUnitsConsumedData.push({
                    name: postOfficeName,
                    solarPanelsUsed: solarPanelsUsed,
                    units: parseInt(unitsConsumed)
                });

                // Check for solar panels
                if (solarPanelsUsed.toUpperCase() === 'TRUE') {
                    solarPanelsCount++;
                } else {
                    noSolarPanelsCount++;
                }
            } else {
                console.warn('Row does not have enough columns:', row);
            }
        });

        leastUnitsConsumedData.sort((a, b) => a.units - b.units);
        const leastFive = leastUnitsConsumedData.slice(0, 5);

        return { leastFive, solarPanelsCount, noSolarPanelsCount };
    }

    async function renderCharts() {
        try {
            const csvData = await fetchCSV('post_offices_with_energy_data.csv');
            if (!csvData) return; // If fetch fails, exit function

            const parsedData = parseCSV(csvData);

            // Render least units consumed chart
            const leastLabels = parsedData.leastFive.map(postOffice => postOffice.name);
            const leastUnits = parsedData.leastFive.map(postOffice => postOffice.units);

            const leastCtx = document.getElementById('leastUnitsConsumedChart').getContext('2d');
            new Chart(leastCtx, {
                type: 'bar',
                data: {
                    labels: leastLabels,
                    datasets: [{
                        label: 'Least Units Consumed',
                        data: leastUnits,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Render solar panel usage chart
            const totalPostOffices = parsedData.solarPanelsCount + parsedData.noSolarPanelsCount;
            const solarData = [
                parsedData.solarPanelsCount,
                parsedData.noSolarPanelsCount
            ];
            const solarLabels = ['Using Solar Panels', 'Not Using Solar Panels'];

            const solarCtx = document.getElementById('solarPanelUsageChart').getContext('2d');
            new Chart(solarCtx, {
                type: 'pie',
                data: {
                    labels: solarLabels,
                    datasets: [{
                        data: solarData,
                        backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'],
                        borderWidth: 1
                    }]
                }
            });

        } catch (error) {
            console.error('Error rendering charts:', error);
        }
    }

    // Render the charts after the page loads
    window.onload = function() {
        renderCharts();
    };
    </script>
</body>
</html>
