<?php
session_start();

// Admin authentication required - redirect non-admins to homepage
if (!isset($_SESSION['admin_id'])) {
    header("Location: uiusupplementhomepage.php");
    exit();
}

$isAdmin = true; // Always true since we checked above
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rented Rooms - Admin Panel | UIU Supplement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        .room-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .room-card.expired {
            border-left: 4px solid #FF3300;
            background-color: #fff5f5;
        }
        
        .room-card.active {
            border-left: 4px solid #4CAF50;
        }
        
        .room-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #4CAF50;
            color: white;
        }
        
        .status-expired {
            background-color: #FF3300;
            color: white;
        }
        
        .room-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .detail-item {
            padding: 8px;
            background: #f8f8f8;
            border-radius: 4px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 12px;
            margin-bottom: 4px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .rules-section {
            margin: 15px 0;
            padding: 12px;
            background: #f0f0f5;
            border-radius: 4px;
            border-left: 3px solid #FF3300;
        }
        
        .rules-section h4 {
            margin-top: 0;
            color: #FF3300;
        }
        
        .rules-content {
            white-space: pre-wrap;
            color: #555;
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-approve:hover {
            background-color: #45a049;
        }
        
        .btn-reject {
            background-color: #FF3300;
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #cc2900;
        }
        
        .no-rooms {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 18px;
        }
        
        .carousel {
            width: 100%;
            max-height: 250px;
            overflow: hidden;
            position: relative;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .carousel-images {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        
        .carousel-img {
            flex: 0 0 100%;
            object-fit: cover;
            width: 100%;
            height: 250px;
        }
        
        .carousel-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            padding: 0 10px;
        }
        
        .nav-button {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 4px;
        }
        
        .nav-button:hover {
            background-color: rgba(0, 0, 0, 0.7);
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
                <li><a href="availablerooms.php">
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
                <i class="fas fa-sign-out-alt"></i>
                Log Out
            </a>
        </nav>

        <section class="main">
            <div class="main-top">
                <h1>Rented Rooms - Admin Panel</h1>
                <p style="text-align:center; color:#666;">Manage rented rooms and handle relisting requests</p>
                <div style="text-align: center; margin: 20px 0;">
                    <button onclick="checkExpiredRentals()" class="btn btn-approve" style="background:#007bff;">
                        <i class="fas fa-sync-alt"></i> Check for Expired Rentals
                    </button>
                </div>
            </div>
            <div id="rented-room-list">
                <!-- Room cards will be dynamically inserted here -->
            </div>
            <div class="button-group" style="text-align: center; margin-top: 30px;">
                <button onclick="location.href='availablerooms.php'" class="btn btn-approve">Available Rooms</button>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isAdmin = true; // Always true - page is admin-only
            let carouselIndexes = {};

            fetch('api/rentedrooms.php')
                .then(response => response.json())
                .then(rooms => {
                    const container = document.getElementById('rented-room-list');
                    
                    if (rooms.length === 0) {
                        container.innerHTML = '<div class="no-rooms"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i><br>No rented rooms found</div>';
                        return;
                    }

                    rooms.forEach((room, index) => {
                        const roomCard = createRoomCard(room, index);
                        container.appendChild(roomCard);
                    });
                })
                .catch(error => {
                    console.error('Error fetching rented rooms:', error);
                    document.getElementById('rented-room-list').innerHTML = 
                        '<div class="no-rooms" style="color: #FF3300;">Error loading rooms. Please try again.</div>';
                });

            function createRoomCard(room, index) {
                const card = document.createElement('div');
                const isExpired = room.rental_status === 'expired';
                card.className = `room-card ${isExpired ? 'expired' : 'active'}`;

                // Create photo carousel
                let carouselHTML = '';
                if (room.room_photos && room.room_photos.length > 0) {
                    carouselHTML = `
                        <div class="carousel" id="carousel-${index}">
                            <div class="carousel-images">
                                ${room.room_photos.map(photo => `
                                    <img src="${photo.trim()}" alt="Room ${room.room_id}" class="carousel-img" />
                                `).join('')}
                            </div>
                            ${room.room_photos.length > 1 ? `
                                <div class="carousel-nav">
                                    <button class="nav-button" onclick="prevSlide(${index})">&#10094;</button>
                                    <button class="nav-button" onclick="nextSlide(${index})">&#10095;</button>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    carouselIndexes[index] = 0;
                }

                card.innerHTML = `
                    ${carouselHTML}
                    <div class="room-header">
                        <h3 style="margin:0; color:#FF3300;">Room ID: ${room.room_id}</h3>
                        <span class="status-badge status-${isExpired ? 'expired' : 'active'}">
                            ${isExpired ? '⚠️ Rental Expired' : '✓ Active Rental'}
                        </span>
                    </div>
                    
                    <div class="room-details">
                        <div class="detail-item">
                            <div class="detail-label">Location</div>
                            <div class="detail-value">${room.room_location || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Rent</div>
                            <div class="detail-value">৳${room.room_rent || 'N/A'}/month</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tenant</div>
                            <div class="detail-value">${room.tenant_name || room.rented_user_name || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Contact</div>
                            <div class="detail-value">${room.tenant_email || room.rented_user_email || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Rented From</div>
                            <div class="detail-value">${room.rented_from_date || room.available_from || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Rented Until</div>
                            <div class="detail-value">${room.rented_until_date || room.available_to || 'N/A'}</div>
                        </div>
                    </div>
                    
                    ${room.rental_rules ? `
                        <div class="rules-section">
                            <h4><i class="fas fa-file-contract"></i> Rental Terms & Conditions</h4>
                            <div class="rules-content">${room.rental_rules}</div>
                        </div>
                    ` : ''}
                    
                    ${isAdmin && isExpired && room.is_relisting_pending ? `
                        <div class="action-buttons">
                            <button class="btn btn-approve" onclick="approveRelisting('${room.room_id}')">
                                <i class="fas fa-check-circle"></i> Approve & Relist
                            </button>
                            <button class="btn btn-reject" onclick="rejectRelisting('${room.room_id}')">
                                <i class="fas fa-times-circle"></i> Reject & Delete
                            </button>
                        </div>
                    ` : ''}
                `;

                return card;
            }

            // Carousel functions
            window.nextSlide = function(index) {
                const carousel = document.querySelectorAll('.carousel-images')[index];
                if (!carousel) return;
                
                const totalImages = carousel.children.length;
                carouselIndexes[index] = (carouselIndexes[index] + 1) % totalImages;
                carousel.style.transform = `translateX(-${carouselIndexes[index] * 100}%)`;
            };

            window.prevSlide = function(index) {
                const carousel = document.querySelectorAll('.carousel-images')[index];
                if (!carousel) return;
                
                const totalImages = carousel.children.length;
                carouselIndexes[index] = (carouselIndexes[index] - 1 + totalImages) % totalImages;
                carousel.style.transform = `translateX(-${carouselIndexes[index] * 100}%)`;
            };

            // Admin action functions
            window.approveRelisting = function(roomId) {
                if (!confirm(`Are you sure you want to relist room ${roomId}? This will make it available to students again.`)) {
                    return;
                }

                fetch('api/handle_relisting.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'approve',
                        room_id: roomId
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Room relisted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to relist room'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while relisting the room');
                });
            };

            window.rejectRelisting = function(roomId) {
                if (!confirm(`Are you sure you want to PERMANENTLY DELETE room ${roomId}? This action cannot be undone.`)) {
                    return;
                }

                fetch('api/handle_relisting.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'reject',
                        room_id: roomId
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Room deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to delete room'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the room');
                });
            };
            
            // Check expired rentals function
            window.checkExpiredRentals = function() {
                fetch('api/check_rental_expiration.php', {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        if (result.expired_rentals_found > 0) {
                            alert(`Found ${result.expired_rentals_found} expired rental(s). The page will refresh to show them.`);
                            location.reload();
                        } else {
                            alert('No expired rentals found. All rentals are up to date!');
                        }
                    } else {
                        alert('Error: ' + (result.error || 'Failed to check expirations'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while checking expirations');
                });
            };
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

</html>
