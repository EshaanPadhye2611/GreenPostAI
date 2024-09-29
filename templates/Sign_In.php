<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <style>
      body {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    background-image: url('post.jpg'); /* Use a high-resolution image */
    background-size: cover; /* Ensures the image covers the entire area */
    background-repeat: no-repeat; /* Prevents the image from repeating */
    background-position: center; /* Centers the image */
}

        .signin-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.5); /* Semi-transparent white background */
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .signin-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
        }
        .signin-title {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: black; /* Change title color to black for better visibility */
            animation: fadeInDown 1s ease;
        }
        .form-label {
            font-weight: bold;
            color: black; /* Change label color to black */
        }
        .form-control {
            border: 2px solid #ddd;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus {
            border-color: #74ebd5;
            box-shadow: 0 0 5px rgba(116, 235, 213, 0.5);
        }
        .btn-primary {
            background-color: #295F98;
            border: none;
            transition: background-color 0.3s;
            color: white;
            font-weight: bold;
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .input-group-text {
            cursor: pointer; /* Cursor changes to pointer */
        }
        .logo {
            max-width: 100px; /* Adjust the size of the logo */
            display: block;
            margin: 0 auto 10px; /* Center the logo and add margin below */
        }
    </style>
</head>
<body>
    <div class="signin-container">
        <img src="pp.png" alt="Logo" class="logo"> <!-- Add your logo image URL here -->
        <h2 class="signin-title">Indian Post Office</h2>
        <form id="signin-form" action="user.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Username</label>
                <input type="text" class="form-control" id="Name" name="Name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-eye" id="toggleCityIcon" onclick="toggleCityVisibility()" style="cursor:pointer;"></i>
                    </span>
                    <input type="password" class="form-control" id="City" name="City" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign In</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCityVisibility() {
            const cityInput = document.getElementById('City');
            const toggleIcon = document.getElementById('toggleCityIcon');

            if (cityInput.type === 'password') {
                cityInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                cityInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
