<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Classification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
        }
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

<h1>CCTV Image Classification</h1>
<div id="result">
    <p id="status">Status: Loading...</p>
    <p id="timestamp">Timestamp: Loading...</p>
    <p id="latitude">Latitude: Loading...</p>
    <p id="longitude">Longitude: Loading...</p>
    <img id="image" src="" alt="No Image Available">
</div>
<form method="post">
    <button type="submit" name="next">Next Image</button>
</form>

<script>
    fetch('predict.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('status').innerText = 'Status: ' + data.status;
        document.getElementById('timestamp').innerText = 'Timestamp: ' + data.timestamp;
        document.getElementById('latitude').innerText = 'Latitude: ' + data.latitude;
        document.getElementById('longitude').innerText = 'Longitude: ' + data.longitude;
        document.getElementById('image').src = data.image;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('status').innerText = 'Status: Error loading data';
    });
</script>

</body>
</html>
