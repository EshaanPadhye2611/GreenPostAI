<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Office Cleanliness Monitoring</title>
    <link rel="stylesheet" href="static\Live.css"> 
    <script>
        async function fetchPostOfficeData(event) {
            event.preventDefault(); // Prevent the default form submission

            const postOfficeName = document.getElementById('postOfficeSelect').value; // Get the selected post office name
            
            if (postOfficeName) {
                const formData = new FormData();
                formData.append('post_office_name', postOfficeName); // Append the selected name

                try {
                    const response = await fetch('/', {
                        method: 'POST',
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();

                    // Display the result
                    document.getElementById('result').innerText = `Cleanliness Status: ${data.status}`;
                    document.getElementById('timestamp').innerText = `Timestamp: ${data.timestamp}`;
                    document.getElementById('latitude').innerText = `Latitude: ${data.latitude}`;
                    document.getElementById('longitude').innerText = `Longitude: ${data.longitude}`;
                    
                    // Show the output image if it exists
                    if (data.output_image) {
                        document.getElementById('outputImage').src = data.output_image; // This should match the path in the Flask app
                        document.getElementById('outputImage').style.display = 'block'; // Ensure the image is shown
                    } else {
                        document.getElementById('outputImage').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                    document.getElementById('result').innerText = 'An error occurred. Please try again.';
                }
            } else {
                alert('Please select a post office.');
            }
        }
    </script>
</head>
<body>
<div class="navbar">
    <a href="http://localhost:3000/index.php" class="navbar-logo">
            <!-- Logo image added here -->
            <img src="static\logo.jpeg" alt="Logo"> GreenPostAI
        </a>
        <nav>
            <ul class="navbar-menu">
                <li><a href="http://localhost:5000/">Live Image Feed</a></li>
                <li><a href="http://localhost:3000/postoffice.php">Post Offices</a></li>
                <li><a href="http://localhost:3000/life_practices.php">LiFE Practices</a></li>
                <li><a href="http://localhost:3000/index.php">Home</a></li>
                
            </ul>
        </nav>
    </div>
    <h1>Post Office Cleanliness Monitoring System</h1>

    <!-- Form to select post office -->
    <form onsubmit="fetchPostOfficeData(event)">
        <label for="postOfficeSelect">Select Post Office:</label>
        <select id="postOfficeSelect">
            <option value="">-- Select Post Office --</option>
            {% for post_office in post_offices %}
                <option value="{{ post_office.Name }}">{{ post_office.Name }}</option>
            {% endfor %}
        </select>
        <button type="submit">Check Cleanliness</button>
    </form>

    <h2 id="result"></h2>
    <p id="timestamp"></p>
    <p id="latitude"></p>
    <p id="longitude"></p>
    <img id="outputImage" src="" alt="Output Image" style="max-width: 500px; display:none;">
</body>
</html>
