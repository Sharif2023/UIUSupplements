<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Supplement Shuttle Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="uiusupplementhomepage.css">
    <style>
        .seat-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            justify-items: center;
            margin: 10px 0;
        }

        .seat {
            font-size: 20px;
        }

        .seat.taken {
            color: orange;
            /* Color for PICK */
        }

        .seat.dropped {
            color: black;
            /* Color for DROP */
        }

        .btn-custom {
            width: 100px;
        }

        .progress-container {
            position: relative;
            height: 30px;
        }

        .destination {
            position: absolute;
            top: -3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #581845;
            background-color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #000;
        }

        .destination.active {
            background-color: #fd7e14;
            color: #fff;
        }

        .destination-1 {
            left: 0%;
            transform: translateX(-50%);
        }

        .destination-2 {
            left: 33%;
            transform: translateX(-50%);
        }

        .destination-3 {
            left: 66%;
            transform: translateX(-50%);
        }

        .destination-4 {
            left: 100%;
            transform: translateX(-50%);
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
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
                <li><a href="#">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-item">Jobs</span>
                    </a></li>
                <li><a href="lostandfound.php">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
                    </a></li>
                <li><a href="shuttle_tracking_system.php">
                        <i class="fas fa-bus"></i>
                        <span class="nav-item">Shuttle Services</span>
                    </a></li>
            </ul>
            <!-- Log Out Button -->
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Log Out
            </a>
        </nav>

        <!-- Main Content -->
        <section class="main">
            <div class="main-top">
                <h1>Shuttle Services</h1>
            </div>

            <div class="main-skills">
                <!-- Shuttle Service Features -->
                <div class="card">
                    <div class="dropdown-center">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Destination
                        </button>
                        <ul class="dropdown-menu">
                            <li><button onclick="selectDestination('Notun Bazar')" class="dropdown-item">Notun Bazar</button></li>
                            <li><button onclick="selectDestination('UIU')" class="dropdown-item">UIU</button></li>
                        </ul>
                    </div>
                    <div class="col">
                        <button onclick="totalclick(1)" id="PICK" class="btn btn-outline-dark btn-success">PICK</button>
                        <button onclick="totalclick(1)" id="dropBtn" class="btn btn-outline-dark btn-danger">DROP</button>
                    </div>
                    <div class="mt-3">
                        Remaining Capacity: <h3 id="totalclicks">40</h3>
                    </div>
                </div>

                <div class="card">
                    <h3>Tracking</h3>
                    <div class="progress-container m-3">
                        <div class="progress">
                            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div id="destination1" class="destination destination-1"></div>
                        <div id="destination2" class="destination destination-2"></div>
                        <div id="destination3" class="destination destination-3"></div>
                        <div id="destination4" class="destination destination-4"></div>
                    </div>
                    <button id="progress-button" class="btn btn-primary">Start-Journey</button>
                </div>

                <div class="card">
                    <h3>Current Status</h3>
                    <div>Time: <span id="current"><?php date_default_timezone_set('Asia/Dhaka');
                                                    echo date('h:i:s A'); ?></span></div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Remaining Capacity</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="output-table">
                            <tr>
                                <td id="location-data">-</td>
                                <td id="remaining-capacity"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div class="seat-container">
                        <!-- 5 seats for the example -->
                        <i id="seat1" class="fa-solid fa-couch fa-sm seat"></i>
                        <i id="seat2" class="fa-solid fa-couch fa-sm seat"></i>
                        <i id="seat3" class="fa-solid fa-couch fa-sm seat"></i>
                        <i id="seat4" class="fa-solid fa-couch fa-sm seat"></i>
                        <i id="seat5" class="fa-solid fa-couch fa-sm seat"></i>
                    </div>
                </div>


                <script>
                    let totalSeats = 5;
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
                        if (currentSeat <= totalSeats) {
                            updateSeatStatus(currentSeat, 'PICK');
                            currentSeat++;
                        }
                    });

                    // DROP button logic: change seat color to black serially, starting from the last updated seat
                    document.getElementById('dropBtn').addEventListener('click', function() {
                        if (currentSeat > 1) {
                            currentSeat--; // Move backwards from the last updated seat
                            updateSeatStatus(currentSeat, 'DROP');
                        }
                    });

                    // Auto-updating clock function
                    function updateTime() {
                        const currentTime = new Date().toLocaleTimeString();
                        document.getElementById('current').innerText = currentTime;
                    }
                    setInterval(updateTime, 1000);

                    // Total click management for capacity
                    function totalclick(click) {
                        const totalclicks = document.getElementById('totalclicks');
                        const remainingCapacity = document.getElementById('remaining-capacity');
                        totalclicks.innerHTML = parseInt(totalclicks.innerHTML) + click;
                        remainingCapacity.innerHTML = parseInt(totalclicks.innerHTML);
                        if (sumvalue <= 40 && sumvalue >= 0) {
                            totalclicks.innerText = sumvalue;
                            remainingCapacity.innerText = sumvalue;
                        } else if (sumvalue < 0) {
                            alert("Bus is already empty");
                        } else if (sumvalue > 40) {
                            alert("Bus is full");
                        }
                    }
                </script>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>