<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Office Water Data</title>
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
            right: 30px;
            font-size: 26px; /* Logo font size */
            color: #06D001; /* Green logo color */
            text-decoration: none; /* No underline */
            font-weight: 900; /* Extra bold */
        }
        
        .navbar-logo img {
            position: relative;
            right: 20px;
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
            width: 90%; 
            padding: 10px; 
            background-color: white;
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
            transition: box-shadow 0.3s; 
        }

        .chart-container:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4); 
        }

        /* Chart styles */
        .chart-container canvas {
            max-width: 100%;
        }

        /* Histogram height */
        #waterConsumptionHistogram {
            height: 300px; 
        }

        /* Bar chart height */
        #leastWaterBillChart {
            height: 120px; 
        }

        /* Line chart height */
        #waterLeakageLineChart {
            height: 300px; 
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
            <!-- Histogram Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Water Consumption Distribution (Liters)</h3>
                    <canvas id="waterConsumptionHistogram"></canvas>
                </div>
            </div>
            <!-- Bar Chart Column -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Top 5 Post Offices with Least Water Bill</h3>
                    <canvas id="leastWaterBillChart"></canvas>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <!-- Line Chart Column -->
            <div class="col-md-12">
                <div class="chart-container">
                    <h3>Water Leakage Proportion of Post Offices</h3>
                    <canvas id="waterLeakageLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            return null; 
        }
        const data = await response.text();
        return data;
    }

    function parseCSV(data) {
        const rows = data.split("\n").slice(1);
        const waterConsumptionData = [];
        const waterBillData = [];
        const leakageData = [];

        rows.forEach(row => {
            const columns = row.split(",");
            if (columns.length >= 14) { // Update to match your CSV structure
                const postOfficeName = columns[0];
                const waterConsumption = parseInt(columns[10]); // Column 10 for Water_Consumption_Liters
                const waterBill = parseInt(columns[11]); // Column 11 for Water_Bill
                const waterLeakage = parseFloat(columns[12]); // Column 12 for Water_Leakage_Proportion

                if (waterConsumption >= 1000 && waterConsumption <= 5000) {
                    waterConsumptionData.push(waterConsumption);
                    waterBillData.push({ name: postOfficeName, bill: waterBill });
                    leakageData.push({ name: postOfficeName, leakage: waterLeakage });
                }
            }
        });

        waterBillData.sort((a, b) => a.bill - b.bill);
        const leastFiveWaterBills = waterBillData.slice(0, 5);

        return { waterConsumptionData, leastFiveWaterBills, leakageData };
    }

    async function renderCharts() {
        try {
            const csvData = await fetchCSV('post_offices_with_energy_and_water_data.csv');
            if (!csvData) return; 

            const parsedData = parseCSV(csvData);

            // Render water consumption histogram
            const histogramData = new Array(5).fill(0); // Create bins for 1000-5000
            parsedData.waterConsumptionData.forEach(consumption => {
                const binIndex = Math.floor((consumption - 1000) / 800); // Group by 800s
                if (binIndex >= 0 && binIndex < histogramData.length) {
                    histogramData[binIndex]++;
                }
            });

            const histogramCtx = document.getElementById('waterConsumptionHistogram').getContext('2d');
            new Chart(histogramCtx, {
                type: 'bar',
                data: {
                    labels: ['1000-1800', '1801-2600', '2601-3400', '3401-4200', '4201-5000'],
                    datasets: [{
                        label: 'Water Consumption Distribution',
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

            // Render least water bill chart
            const leastLabels = parsedData.leastFiveWaterBills.map(postOffice => postOffice.name);
            const leastWaterBills = parsedData.leastFiveWaterBills.map(postOffice => postOffice.bill);

            const leastCtx = document.getElementById('leastWaterBillChart').getContext('2d');
            new Chart(leastCtx, {
                type: 'bar',
                data: {
                    labels: leastLabels,
                    datasets: [{
                        label: 'Least Water Bill',
                        data: leastWaterBills,
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
                                autoSkip: false 
                            }
                        }
                    },
                    hover: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });

            // Render water leakage line chart
            const leakageLabels = parsedData.leakageData.map(postOffice => postOffice.name);
            const leakageValues = parsedData.leakageData.map(postOffice => postOffice.leakage);

            const leakageCtx = document.getElementById('waterLeakageLineChart').getContext('2d');
            new Chart(leakageCtx, {
                type: 'line',
                data: {
                    labels: leakageLabels,
                    datasets: [{
                        label: 'Water Leakage Proportion',
                        data: leakageValues,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        fill: true
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
