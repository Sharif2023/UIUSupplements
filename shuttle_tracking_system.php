<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form data was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['car_no']) && isset($_POST['current_location']) && isset($_POST['totalclicks']) && isset($_POST['needTime']) && isset($_POST['next_destination'])) {
        $car_no = $_POST['car_no'];
        $current_location = $_POST['current_location'];
        $remaining_capacity = $_POST['totalclicks'];
        $approximate_time = $_POST['needTime'];
        $next_destination = $_POST['next_destination'];

        // Prepare and execute the SQL insert query
        $sql = "INSERT INTO shuttle_tracking (car_no, current_location, remaining_capacity, approximate_time, next_destination)
                VALUES ('$car_no', '$current_location', '$remaining_capacity', '$approximate_time', '$next_destination')";

        if ($conn->query($sql) === TRUE) {
            echo "Record added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shuttle Tracking System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://maps.gomaps.pro/maps/api/js?key=AlzaSygAfpRH_g78jn6CrKdPpNZivYCddRS7LRz&libraries=places"></script>

    <style>
        #map {
            height: 400px;
            width: 70%;
        }

        #controls {
            margin-top: 10px;
        }

        .seat-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            justify-items: center;
            margin: 10px 0;
        }

        .seat {
            font-size: 20px;
            color: #9ce3f3;
        }

        .seat.taken {
            color: orange;
            /* Color for PICK */
        }

        .seat.dropped {
            color: #9ce3f3;
            /* Color for DROP */
        }

        .btn-custom {
            width: 100px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f0f0f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        nav {
            width: 100%;
            max-width: 250px;
            background-color: #fff;
            padding: 20px;
            height: 100vh;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
        }

        .styled-title {
            font-size: 1.4rem;
            color: #1F1F1F;
            text-shadow: 0 0 5px #ff005e, 0 0 10px #ff005e, 0 0 20px #ff005e, 0 0 40px #ff005e, 0 0 80px #ff005e;
            animation: glow 1.5s infinite alternate;
        }

        @keyframes glow {
            0% {
                text-shadow: 0 0 5px #ff005e, 0 0 10px #ff005e, 0 0 20px #ff005e, 0 0 40px #ff005e, 0 0 80px #ff005e;
            }

            100% {
                text-shadow: 0 0 10px #00d4ff, 0 0 20px #00d4ff, 0 0 40px #00d4ff, 0 0 80px #00d4ff, 0 0 160px #00d4ff;
            }
        }

        nav ul {
            list-style-type: none;
            padding-top: 20px;
        }

        nav ul li {
            margin: 15px -15px;
        }

        nav ul li a {
            color: #555;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 10px;
            text-decoration: none;
        }

        nav ul li a.active,
        nav ul li a:hover {
            background-color: #f0f0f5;
            border-radius: 10px;
        }

        nav ul li a .nav-item {
            margin-left: 15px;
        }

        /* Log Out Button */
        .logout-btn {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #1F1F1F;
        }

        /* Main content */
        .main {
            margin-left: 260px;
            /* Add margin to the right of the sidebar */
            padding: 20px;
            width: calc(100% - 260px);
            /* Adjust width to prevent overlapping */
        }

        .main-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .center-title {
            font-size: 24px;
            font-weight: bold;
            flex: 1;
            text-align: center;
        }

        .add-mentor-btn {
            margin-bottom: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            display: inline-block;
            font-size: 16px;
            text-decoration: none;
        }

        .add-mentor-btn:hover {
            background-color: #218838;
        }

        /* Cards Section */
        .product-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .product-cards .card {
            flex: 1 1 30%;
            max-width: 30%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            max-height: 200px;
            object-fit: cover;
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            font-size: 18px;
            font-weight: bold;
        }

        #bargain-success {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            display: none;
            /* Initially hidden */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="uiusupplementhomepage.php" class="logo">
                        <h1 class="styled-title">UIU Supplement</h1>
                    </a></li>
                <li><a href="uiusupplementhomepage.php">
                        <i class="fas fa-home"></i>
                        <span class="nav-item">Home</span>
                    </a></li>
                <li><a href="SellAndExchange.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-item">Sell</span>
                    </a></li>
                <li><a href="availablerooms.html">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.html">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.html">
            <i class="fas fa-briefcase"></i>
            <span class="nav-item">Jobs</span>
          </a></li>
                <li><a href="lostandfound.php">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
                    </a></li>
                <li><a href="shuttle_tracking_system.php" class="active">
                        <i class="fas fa-bus"></i>
                        <span class="nav-item">Shuttle Services</span>
                    </a></li>
            </ul>
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>
        <section class="main">
            <div class="main-top">

                <button id="startButton" class="btn btn-primary">Start</button>
                <!-- Select destination for map -->
                <div id="controls">
                    <!-- <label for="destination">Select Destination:</label> -->
                    <select id="destination">
                        <option value="">--Choose a destination--</option>
                        <option value="NotunBazar">Notun Bazar</option>
                        <option value="Sayednagar">Sayednagar</option>
                        <option value="FamilyBazar">FamilyBazar</option>
                        <option value="UIU">UIU</option>
                    </select>
                </div>
            </div>


            <div class=" text-center ">
                <div class="product-cards">
                    <div class="col">
                        <button class="btn"><a class="page-link" href="shulltequery.php">complex Query</a></button>
                        <button onclick="totalclick(-1)" id="PICK" class="btn btn-outline-dark btn-success">PICK</button>
                        <button onclick="totalclick(1)" id="dropBtn" class="btn btn-outline-dark btn-danger">DROP</button>
                        <br> <br> Remaining Capacity: <h3 id="totalclicks">40</h3>
                        Duration: <h2 id="needTime">-</h3>
                            <button class="btn" style="background-color:#9ce3f3;" disabled>available</button>
                            <button class="btn" style="background-color:orange;" disabled>booked</button><br>


                            <div class="" style="margin-left: 30px;"> <!-- 5 seats for the example -->
                                <br>
                                <i id="seat1" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat2" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat3" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat4" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat5" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat6" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat7" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat8" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat9" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat10" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat11" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat12" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat13" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat14" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat15" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat16" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat17" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat18" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat19" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat20" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat21" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat22" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat23" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat24" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat25" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat26" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat27" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat28" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat29" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat30" class="fa-solid fa-couch fa-sm seat" style="margin-right: 10px;"></i>
                                <i id="seat31" class="fa-solid fa-couch fa-sm seat"></i>
                                <i id="seat32" class="fa-solid fa-couch fa-sm seat"></i><br>
                                <i id="seat33" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;"></i>
                                <i id="seat34" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;" style="margin-right: 10px;"></i>
                                <i id="seat35" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;"></i>
                                <i id="seat36" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;"></i><br>
                                <i id="seat37" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;"></i>
                                <i id="seat38" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;" style="margin-right: 10px;"></i>
                                <i id="seat39" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;"></i>
                                <i id="seat40" class="fa-solid fa-couch fa-sm seat" style="visibility: hidden;"></i><br>
                            </div>
                    </div>
                    <script>
                        let totalSeats = 39;
                        let currentSeat = 1;

                        // Function to update seat color and status
                        function updateSeatStatus(seatIndex, action) {
                            const seat = document.getElementById(`seat${seatIndex}`);
                            if (action === 'PICK') {
                                seat.classList.add('taken'); // Color for PICK
                                seat.classList.remove('dropped');
                            } else if (action === 'DROP') {
                                seat.classList.add('dropped'); // Color for DROP
                                seat.classList.remove('taken');
                            }
                        }

                        // PICK button logic: change seat color to orange serially
                        document.getElementById('PICK').addEventListener('click', function() {
                            if (currentSeat > totalSeats) {
                                alert("Full/empty");
                            } else if (currentSeat <= totalSeats) {
                                updateSeatStatus(currentSeat, 'PICK');
                                currentSeat++;
                                if (currentSeat <= 40) {
                                    const totalclicks = document.getElementById('totalclicks');
                                    const remainingCapacity = document.getElementById('remaining-capacity');
                                    totalclicks.innerHTML = parseInt(totalclicks.innerHTML) - 1;
                                    remainingCapacity.innerHTML = parseInt(totalclicks.innerHTML);
                                }
                            }

                        });

                        // DROP button logic: change seat color to black serially, starting from the last updated seat
                        document.getElementById('dropBtn').addEventListener('click', function() {
                            if (currentSeat <= 1) {
                                alert("Full/empty");
                            } else if (currentSeat > 1) {

                                currentSeat--; // Move backwards from the last updated seat
                                updateSeatStatus(currentSeat, 'DROP');
                                const totalclicks = document.getElementById('totalclicks');
                                const remainingCapacity = document.getElementById('remaining-capacity');
                                totalclicks.innerHTML = parseInt(totalclicks.innerHTML) + 1;

                            }
                        });
                    </script>
                    <!-- FINISH seat arragement -->
                    <div class="col" id="map"></div>
                </div>
                <script>
                    let map, marker, directionsService, directionsRenderer;
                    let currentLocation = {
                        lat: 23.797193,
                        lng: 90.449684
                    };

                    const destinations = {
                        "NotunBazar": {
                            lat: 23.797965,
                            lng: 90.425739
                        },
                        "Sayednagar": {
                            lat: 23.798630,
                            lng: 90.434937
                        },
                        "FamilyBazar": {
                            lat: 23.798195,
                            lng: 90.429263
                        },
                        "UIU": {
                            lat: 23.797193,
                            lng: 90.449684
                        }
                    };

                    // Initialize the map after the page loads
                    window.onload = function() {
                        initMap();
                        document.getElementById('destination').addEventListener('change', calculateRoute);
                    }

                    // Function to initialize and display the map
                    function initMap() {
                        map = new google.maps.Map(document.getElementById("map"), {
                            zoom: 14,
                            center: currentLocation,
                            mapTypeControl: false,
                            streetViewControl: false,
                        });

                        directionsService = new google.maps.DirectionsService();
                        directionsRenderer = new google.maps.DirectionsRenderer();
                        directionsRenderer.setMap(map);

                        // Check if the browser supports Geolocation API
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    currentLocation = {
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude,
                                    };
                                    map.setCenter(currentLocation);

                                    // Place a marker at the user's current location
                                    marker = new google.maps.Marker({
                                        position: currentLocation,
                                        map: map,
                                        title: "You are here",
                                    });
                                },
                                (error) => {
                                    console.error("Geolocation error: ", error);
                                    alert("Unable to retrieve your location. Please allow location access.");
                                }
                            );
                        } else {
                            alert("Geolocation is not supported by your browser.");
                        }
                    }

                    // Function to calculate and display route
                    function calculateRoute() {
                        const destination = document.getElementById('destination').value;

                        if (destination) {
                            const destinationLatLng = destinations[destination];

                            const request = {
                                origin: currentLocation,
                                destination: destinationLatLng,
                                travelMode: google.maps.TravelMode.DRIVING
                            };

                            directionsService.route(request, function(result, status) {
                                if (status === 'OK') {
                                    directionsRenderer.setDirections(result);

                                    // Place a marker at the destination
                                    new google.maps.Marker({
                                        position: destinationLatLng,
                                        map: map,
                                        title: destination
                                    });

                                    // Optionally, display duration
                                    const duration = result.routes[0].legs[0].duration.text;
                                    document.getElementById('needTime').innerText = duration;
                                    alert(`Estimated travel time: ${duration}`);
                                } else {
                                    alert("Could not calculate route.");
                                }
                            });
                        }
                    }
                </script>
                <script>
                    function updateShuttleInfo() {
                        var car_no = document.getElementById('car_no').innerText;
                        var current_location = document.getElementById('current_location').innerText;
                        var remaining_capacity = document.getElementById('remaining_capacity').innerText;
                        var approximate_time = document.getElementById('approximate_time').innerText;
                        var next_destination = document.getElementById('next_destination').innerText;

                        // Simulate shuttle moving to the next location
                        if (current_location === "UIU") {
                            current_location = "Sayednagar";
                            next_destination = "Familybazar";
                        } else if (current_location === "Sayednagar") {
                            current_location = "Familybazar";
                            next_destination = "Notun Bazar";
                        } else if (current_location === "Familybazar") {
                            current_location = "Notun Bazar";
                            next_destination = "UIU";
                        } else if (current_location === "Notun Bazar") {
                            current_location = "UIU";
                            next_destination = "Sayednagar";
                        }

                        // Update the values in the HTML
                        document.getElementById('current_location').innerText = current_location;
                        document.getElementById('next_destination').innerText = next_destination;

                        // Create an AJAX request to store the updated values
                        var xhttp = new XMLHttpRequest();
                        xhttp.onreadystatechange = function() {
                            if (this.readyState == 4 && this.status == 200) {
                                console.log(this.responseText);
                            }
                        };
                        xhttp.open("POST", "shuttle_tracking_system.php", true);
                        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhttp.send("car_no=" + car_no + "&current_location=" + current_location + "&remaining_capacity=" + remaining_capacity + "&approximate_time=" + approximate_time + "&next_destination=" + next_destination);
                    }
                </script>


                <!-- Popup Modal for DID, CarNo Input -->
                <div class="modal fade" id="startModal" tabindex="-1" aria-labelledby="startModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="startModalLabel">Enter Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="startForm">
                                    <div class="mb-3">
                                        <label for="DID" class="form-label">DID</label>
                                        <input type="text" class="form-control" id="DID" name="DID" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="CarNo" class="form-label">Car Number</label>
                                        <input type="text" class="form-control" id="CarNo" name="CarNo" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button id="nextBtn" class="btn btn-success" disabled>Next</button>
            <!-- Table to Show Shuttle Details -->
            <div class="container mt-5">

                <table class="table">
                    <thead>
                        <tr>
                            <th>CarNo</th>
                            <th>Current Location</th>

                            <th>Departure Time</th>
                            <th>Next Destination</th>
                        </tr>
                    </thead>
                    <tbody id="output-table">
                        <!-- Output will be shown here dynamically -->
                    </tbody>
                </table>
            </div>


            <script>
                // Locations array for cycling through
                let route = ['UIU', 'Sayednager', 'Familybazar', 'Notunbazar', 'Familybazar', 'Sayednager'];
                let currentIndex = 0;
                let cycleCount = 0;

                // Event listener for Start button to show the modal
                document.getElementById('startButton').addEventListener('click', function() {
                    new bootstrap.Modal(document.getElementById('startModal')).show();
                });

                // Handle form submission and populate the table with the details
                document.getElementById('startForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    let DID = document.getElementById('DID').value;
                    let CarNo = document.getElementById('CarNo').value;

                    // After submission, populate the table
                    let tbody = document.getElementById('output-table');
                    tbody.innerHTML = `
                <tr>
                    <td>${CarNo}</td>
                    <td id="current-location">UIU</td>
                    
                    <td id="approximate-time">${new Date().toLocaleTimeString()}</td>
                    <td id="next-destination">Sayednager</td>
                </tr>
            `;

                    document.getElementById('nextBtn').disabled = false;

                    // Close the modal
                    let modal = bootstrap.Modal.getInstance(document.getElementById('startModal'));
                    modal.hide();
                });

                // Handle NEXT button click to update the route and table dynamically
                document.getElementById('nextBtn').addEventListener('click', function() {
                    currentIndex = (currentIndex + 1) % route.length;
                    if (currentIndex === 0) cycleCount++; // Increment cycle count on completing a route

                    let currentLocation = route[currentIndex];
                    let nextLocation = route[(currentIndex + 1) % route.length];

                    // Update table
                    document.getElementById('current-location').innerText = currentLocation;
                    document.getElementById('next-destination').innerText = nextLocation;

                    document.getElementById('needTime').innerText = new Date().toLocaleTimeString();
                });
            </script>
        </section>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>