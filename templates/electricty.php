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
            bottom: 20px;
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
            margin-right: 1px; /* Space between logo and text */
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

        /* Chart container */
        .chart-container {
            width: 90%; /* Full width */
            padding: 10px; /* Reduced padding */
            background-color: white;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Box shadow */
            transition: box-shadow 0.3s; /* Transition for box-shadow */
        }

        /* Chart hover effect */
        .chart-container:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4); /* Increase shadow on hover */
        }

        /* Chart styles */
        .chart-container canvas {
            max-width: 100%;
        }

        /* Set smaller height for the least units consumed bar graph */
        #leastUnitsConsumedChart {
            height: 120px; /* Adjust height for smaller size */
        }
        
        /* Pie chart height */
        #solarPanelUsageChart {
            height: 40px;
            width: 30px; /* Adjust height for smaller size */
        }

        /* Line chart height */
        #cityWiseUnitsLineChart {
            height: 120px; /* Adjust height for smaller size */
        }
        
        /* Histogram height */
        #cityWiseUnitsHistogram {
            position: relative;
            top: 50px;
            height: 300px; /* Keep this taller if needed */
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
        <div class="row mb-4">
            <!-- Pie Chart Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Post Offices Using Solar Panels</h3>
                    <canvas id="solarPanelUsageChart"></canvas>
                </div>
            </div>
            <!-- Histogram Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Overall Distribution of Units Consumed by City</h3>
                    <canvas id="cityWiseUnitsHistogram"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Line Chart Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>City-wise Distribution of Units Consumed</h3>
                    <canvas id="cityWiseUnitsLineChart"></canvas>
                </div>
            </div>
            <!-- Bar Chart Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Post Offices with Least Units Consumed</h3>
                    <canvas id="leastUnitsConsumedChart"></canvas>
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

    // Function to generate random colors
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

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
        const cityData = {};

        rows.forEach(row => {
            const columns = row.split(",");
            if (columns.length >= 10) {
                const postOfficeName = columns[0];
                const solarPanelsUsed = columns[8];
                const unitsConsumed = parseInt(columns[5]);
                const cityName = columns[1]; // Assuming the city name is in the second column

                leastUnitsConsumedData.push({
                    name: postOfficeName,
                    solarPanelsUsed: solarPanelsUsed,
                    units: unitsConsumed
                });

                // Accumulate data by city
                if (!cityData[cityName]) {
                    cityData[cityName] = [];
                }
                cityData[cityName].push(unitsConsumed);

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

        return { leastFive, solarPanelsCount, noSolarPanelsCount, cityData };
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
                        backgroundColor: leastLabels.map(() => getRandomColor()),
                        borderColor: leastLabels.map(() => getRandomColor()),
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                        x: {
                            ticks: {
                                autoSkip: false // Show all labels
                            }
                        }
                    },
                    hover: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });

            // Render solar panel usage chart
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

            // Prepare data for histogram
            const histogramData = new Array(10).fill(0); // Create bins for units consumed

            for (const city in parsedData.cityData) {
                const units = parsedData.cityData[city];
                units.forEach(unit => {
                    const binIndex = Math.floor(unit / 100); // Group by 100s
                    if (binIndex < histogramData.length) {
                        histogramData[binIndex]++;
                    }
                });
            }

            const histogramCtx = document.getElementById('cityWiseUnitsHistogram').getContext('2d');
            new Chart(histogramCtx, {
                type: 'bar',
                data: {
                    labels: Array.from({ length: histogramData.length }, (_, i) => `${i * 100} - ${(i + 1) * 100}`),
                    datasets: [{
                        label: 'Units Consumed Distribution',
                        data: histogramData,
                        backgroundColor: histogramData.map(() => getRandomColor()),
                        borderColor: 'rgba(0, 0, 0, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    hover: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });

            // Prepare data for line chart
            const cityOrder = ['Thane', 'Mumbai', 'Aurangabad'];
            const lineLabels = cityOrder;
            const lineData = cityOrder.map(city => {
                return (parsedData.cityData[city] || []).reduce((acc, curr) => acc + curr, 0);
            });

            const lineCtx = document.getElementById('cityWiseUnitsLineChart').getContext('2d');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: lineLabels,
                    datasets: [{
                        label: 'Total Units Consumed by City',
                        data: lineData,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false // No area fill
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    hover: {
                        mode: 'index',
                        intersect: false
                    }
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
