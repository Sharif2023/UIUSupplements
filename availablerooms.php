<?php
session_start();

// Authentication check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms | UIU Supplement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        /* Page-specific styles for Available Rooms */
        .main-top {
            padding: 5px;
            margin-bottom: 40px;
        }

        .main-content {
            margin-top: 20px;
            text-align: center;
        }

        .room-details {
            padding: 10px;
            font-size: 16px;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
        }

        .carousel {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .carousel-images {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .carousel-img {
            flex: 0 0 100%;
            object-fit: cover;
            width: 100%;
            height: 100%;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .search-sort-container {
            display: flex;
            align-items: center;
            gap: 10px;
            position: absolute;
            right: 20px;
        }

        .search-bar {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-bar i.search-icon {
            position: absolute;
            left: 10px;
            color: #888;
            pointer-events: none;
        }

        #search-room {
            padding: 8px 35px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 200px;
        }

        .search-btn {
            background-color: #FF3300;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
            cursor: pointer;
        }

        .search-btn:hover {
            background-color: #1F1F1F;
        }

        .sort-bar {
            position: relative;
            display: flex;
            align-items: center;
        }

        .sort-bar i.sort-icon {
            position: absolute;
            left: 10px;
            color: #888;
        }

        #sort-options {
            padding: 8px 8px 8px 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 150px;
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        .nav-button {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
        }

        .nav-button:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .card-btn:hover {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(0, 0, 0, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
            }
        }

        .card-btn-appointed {
            background-color: #ccc;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: not-allowed;
            margin-top: 15px;
            width: 100px;
            align-self: flex-end;
        }

        .center-title {
            text-align: center;
            font-size: 30px;
            color: #333;
            flex-grow: 1;
        }

        .main-skills {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
            margin-top: 70px;
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
                <li><a href="availablerooms.php" class="active">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.php">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.php">
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

            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <div class="main-top">
                <h1 class="center-title">Roomrental Service</h1>
            </div>
            <div class="search-sort-container">
                <div class="search-sort-container">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="search-room" placeholder="Search by location..." onkeyup="filterRooms()">
                        <button id="search-button" class="search-btn">Search</button>
                    </div>
                    <div class="sort-bar">
                        <i class="fas fa-sort sort-icon"></i>
                        <select id="sort-options" onchange="sortRooms()">
                            <option value="">Sort by Rent</option>
                            <option value="low-to-high">⬆ Rent: Low to High</option>
                            <option value="high-to-low">⬇ Rent: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="room-list" class="main-skills">
                <!-- Room details will be dynamically inserted here -->
            </div>
            <div class="button-group">
                <button id="add-new-room" onclick="location.href='addnewroom.php'" class="card-btn">Add New
                    Room</button>
                <button id="view-rooms" onclick="location.href='appointedrooms.php'" class="card-btn">Rented
                    Rooms</button>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let roomsData = [];

            fetch('api/rooms.php')
                .then(response => response.json())
                .then(rooms => {
                    roomsData = rooms; // Store rooms data
                    displayRooms(roomsData);
                })
                .catch(error => {
                    console.error('Error fetching room data:', error);
                });

            function displayRooms(rooms) {
                const roomListContainer = document.getElementById('room-list');
                roomListContainer.innerHTML = ''; // Clear previous data

                rooms.forEach((room, index) => {
                    const roomDiv = document.createElement('div');
                    roomDiv.classList.add('card'); // Card grid

                    // Carousel for room photos at the top
                    const carousel = document.createElement('div');
                    carousel.classList.add('carousel');
                    const carouselImages = document.createElement('div');
                    carouselImages.classList.add('carousel-images');

                    room.room_photos.forEach((photo, photoIndex) => {
                        const img = document.createElement('img');
                        img.src = photo.trim(); // Use the photo URL
                        img.alt = `Room ${room.room_id} Photo ${photoIndex + 1}`;
                        img.classList.add('carousel-img');
                        carouselImages.appendChild(img);
                    });

                    carousel.appendChild(carouselImages);

                    // Add navigation buttons for carousel
                    const navButtons = document.createElement('div');
                    navButtons.classList.add('carousel-nav');
                    navButtons.innerHTML = `
                        <button class="nav-button" onclick="prevSlide(${index})">&#10094;</button>
                        <button class="nav-button" onclick="nextSlide(${index})">&#10095;</button>
                    `;
                    carousel.appendChild(navButtons);

                    // Room details in the middle
                    const detailsDiv = document.createElement('div');
                    detailsDiv.classList.add('room-details');
                    detailsDiv.innerHTML = `
                        <h3>Room ID: ${room.room_id}</h3>
                        <p><strong>Location:</strong> ${room.room_location}</p>
                        <p><strong>Details:</strong> ${room.room_details}</p>
                        <p><strong>Status:</strong> ${room.status === 'available' ? 'Available' : '<span class="appointed-status">Rented</span>'}</p>
                        <p><strong>Available From:</strong> ${room.available_from}</p>
                        <p><strong>Available To:</strong> ${room.available_to}</p>
                        <p><strong>Rent:</strong> ${room.room_rent}</p>
                    `;

                    // Rent button at the bottom
                    const buttonDiv = document.createElement('div');
                    buttonDiv.classList.add('button-container');

                    if (room.status === 'available') {
                        const rentButton = document.createElement('button');
                        rentButton.textContent = 'Rent';
                        rentButton.classList.add('card-btn');
                        rentButton.addEventListener('click', function () {
                            rentRoom(room.room_id, rentButton);
                        });
                        buttonDiv.appendChild(rentButton);
                    } else {
                        const rentedButton = document.createElement('button');
                        rentedButton.textContent = 'Rented';
                        rentedButton.classList.add('card-btn-appointed');
                        rentedButton.setAttribute('disabled', 'disabled');
                        buttonDiv.appendChild(rentedButton);
                    }

                    // Append elements to the card
                    roomDiv.appendChild(carousel);
                    roomDiv.appendChild(detailsDiv);
                    roomDiv.appendChild(buttonDiv);

                    roomListContainer.appendChild(roomDiv);
                });
            }

            // Carousel functionality
            const currentIndexes = {}; // Store current index for each room

            function showSlide(index, roomIndex) {
                const carouselImages = document.querySelectorAll('.carousel-images')[roomIndex];
                const totalImages = carouselImages.children.length;

                // Ensure valid index and image display
                if (totalImages > 0) {
                    // Avoid index out of bounds
                    if (index >= totalImages) index = 0;
                    if (index < 0) index = totalImages - 1;

                    // Apply translation based on the current index for that specific room
                    carouselImages.style.transform = `translateX(-${index * 100}%)`;
                }
            }

            window.nextSlide = function (roomIndex) {
                const carouselImages = document.querySelectorAll('.carousel-images')[roomIndex];
                const totalImages = carouselImages.children.length;

                if (!currentIndexes[roomIndex]) {
                    currentIndexes[roomIndex] = 0;
                }

                currentIndexes[roomIndex] = (currentIndexes[roomIndex] + 1) % totalImages;
                showSlide(currentIndexes[roomIndex], roomIndex);
            };

            window.prevSlide = function (roomIndex) {
                const carouselImages = document.querySelectorAll('.carousel-images')[roomIndex];
                const totalImages = carouselImages.children.length;

                if (!currentIndexes[roomIndex]) {
                    currentIndexes[roomIndex] = 0;
                }

                currentIndexes[roomIndex] = (currentIndexes[roomIndex] - 1 + totalImages) % totalImages;
                showSlide(currentIndexes[roomIndex], roomIndex);
            };

            // Search Functionality
            function filterRooms() {
                const searchValue = document.getElementById('search-room').value.toLowerCase();
                console.log(`Searching for: ${searchValue}`); // Debug statement

                const filteredRooms = roomsData.filter(room =>
                    room.room_location.toLowerCase().includes(searchValue)
                );

                console.log(`Filtered Rooms: ${JSON.stringify(filteredRooms)}`); // Debug statement
                displayRooms(filteredRooms);
            }

            // Trigger search on button click
            document.getElementById('search-button').addEventListener('click', filterRooms);

            // Sorting Functionality - Client-side sorting for better UX
            function sortRooms() {
                const sortOption = document.getElementById('sort-options').value;
                let sortedRooms = [...roomsData]; // Create a copy to sort

                if (sortOption === 'low-to-high') {
                    sortedRooms.sort((a, b) => parseFloat(a.room_rent) - parseFloat(b.room_rent));
                } else if (sortOption === 'high-to-low') {
                    sortedRooms.sort((a, b) => parseFloat(b.room_rent) - parseFloat(a.room_rent));
                }

                console.log('Sorted Rooms:', sortedRooms);
                displayRooms(sortedRooms);
            }

            // Trigger sort on dropdown change
            document.getElementById('sort-options').addEventListener('change', sortRooms);


            function rentRoom(roomId, button) {
                const userId = prompt("Enter your User ID:");
                const password = prompt("Enter your Password:");

                if (userId && password) {
                    fetch('appointroom.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ user_id: userId, password: password, room_id: roomId })
                    })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data); // Log the server response for debugging
                            if (data.status === 'success') {
                                alert('Room rented successfully!');
                                button.textContent = 'Rented';
                                button.classList.add('card-btn-appointed');
                                button.setAttribute('disabled', 'disabled');
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error renting room:', error);
                        });
                } else {
                    alert("User ID and Password are required.");
                }
            }
        });
    </script>
</body>
<footer class="footer">
    <div class="social-icons">
        <a href="https://www.facebook.com/sharif.me2018"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-google"></i></a>
        <a href="https://www.instagram.com/shariful_islam10"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.github.com/sharif2023"><i class="fab fa-github"></i></a>
    </div>
    <div class="copyright">
        &copy; 2020 Copyright: <a href="https://www.youtube.com/@SHARIFsCODECORNER">Sharif Code Corner</a>
    </div>
</footer>
<script src="assets/js/index.js"></script>

</html>
