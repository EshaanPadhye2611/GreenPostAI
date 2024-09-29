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
            width: 100%; /* Full width */
            z-index: 1000; /* Keeps navbar on top of other content */
            display: flex;
            align-items: center; /* Vertically center items */
            justify-content: space-between; /* Space between items */
            box-sizing: border-box; /* Ensures padding doesn't increase height */
        }

        .navbar-logo {
            position: relative;
            bottom: 13px;
            font-size: 24px; /* Logo font size */
            color: #06D001; /* Green logo color */
            text-decoration: none; /* No underline */
            font-weight: 900; /* Extra bold */
        }

        .navbar-logo img {
            position: relative;
            bottom: 12px;
            height: 70px; /* Adjust the height of the logo */
            margin-right: 2px; /* Space between logo and text */
            vertical-align: middle; /* Align logo with text */
        }

        .navbar-menu {
            position: relative;
            bottom: 9px;
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

        /* Carousel styles */
        .carousel-container {
            width: 100%;
            height: 450px; /* Adjust height to your preference */
            position: relative;
            bottom: 8px;
            overflow: hidden;
            margin-top: 80px; /* Add margin to move the carousel down */
            margin-bottom: 0; /* Remove bottom margin */
        }

        .carousel-item img {
            height: 100%;
            object-fit: contain; /* Ensure images fit within the container without cutting */
            width: 100%;
            max-height: 450px; /* Restrict the maximum height of images */
            background-color: black; /* Optional: Black background to fill empty space */
        }

        /* Image section styles */
        .image-gallery {
            position: relative;
            bottom: 80px;
            display: flex;
            justify-content: center; /* Space between items */
            width: 100%; /* Full width for the gallery */
            margin-top: 20px; /* Add margin above the gallery */
        }

        .image-gallery .image-container {
            flex: 1; /* Allow items to grow equally */
            display: flex;
            flex-direction: column; /* Stack text and image vertically */
            align-items: center; /* Center items horizontally */
            margin: 10px; /* Space around images */
        }

        .image-caption {
            position: relative;
            bottom: 30px;
            font-size: 18px; /* Font size for captions */
            color: #06D001; /* Caption color */
            margin-bottom: 5px; /* Space below captions */
            text-align: center; /* Center the text */
            font-weight: bold; /* Make captions bold */
            background-color: black; /* Slightly transparent white background */
            border-radius: 10px; /* Rounded corners */
            padding: 10px; /* Padding inside the box */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Shadow for depth */
            width: 80%; /* Box width */
        }

        .image-gallery img {
            position: relative;
            right: 50px;
            width: 400%; /* Make images fill their container */
            height: 200px; /* Set a specific height for all images */
            max-width: 400px; /* Set a max width for larger images */
            max-height: 400px; /* Set a max height to match the specified height */
            border-radius: 20px; /* Rounded corners */
            cursor: pointer; /* Pointer on hover */
            transition: transform 0.3s; /* Smooth scaling effect */
            object-fit: cover; /* Ensure images cover the entire area without distortion */
        }

        .image-gallery img:hover {
            transform: scale(1.05); /* Scale effect on hover */
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
        <a href="index.php" class="navbar-logo">
            <img src="logo.jpeg" alt="Logo">  GreenPostAI
        </a>
        <nav>
            <ul class="navbar-menu">
                
                <li><a href="http://localhost:5000/">Live Image Feed</a></li>
                <li><a href="postoffice.php">Post Offices</a></li>
                <li><a href="index.php">Home</a></li>
                
            </ul>
        </nav>
    </div>

    <!-- Carousel Section -->
    <div class="carousel-container">
        <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="gg.jpeg" class="d-block" alt="Life Image">
                </div>
            </div>
        </div>
    </div>

    <!-- Image Gallery Section -->
    <div class="image-gallery">
        <div class="image-container">
            <div class="image-caption">Electricity Units Consumed</div>
            <a href="electricty.php"><img src="green_1.jpeg" alt="Image 1"></a>
        </div>
        <div class="image-container">
            <div class="image-caption">Water Used Statistics</div>
            <a href="water.php"><img src="water.jpeg" alt="Image 2"></a>
        </div>
        <div class="image-container">
            <div class="image-caption">E-Waste Statistics</div>
           <a href="e-waste.php"><img src="e-waste.jpeg" alt="Image 3"></a> 
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
    </script>
</body>
</html>
