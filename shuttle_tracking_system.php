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
    <title>Shuttle Tracking System | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/index.css" />

    <style>
        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 15px;
        }

        /* Route Tabs */
        .route-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .route-tab {
            padding: 15px 30px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 15px;
            color: #555;
            position: relative;
        }

        .route-tab:hover {
            border-color: #FF3300;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 51, 0, 0.15);
        }

        .route-tab.active {
            background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%);
            color: white;
            border-color: transparent;
            box-shadow: 0 5px 25px rgba(255, 51, 0, 0.3);
        }

        .route-tab.active i {
            color: white;
        }

        .route-tab i {
            font-size: 20px;
            color: #FF3300;
        }

        .coming-soon-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
            color: #333;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 2px 10px rgba(255, 193, 7, 0.4);
        }

        /* Route Content */
        .route-content {
            display: none;
        }

        .route-content.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Route Overview */
        .route-overview {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .route-overview h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .route-overview h3 i {
            color: #FF3300;
        }

        /* Route Timeline */
        .route-timeline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            position: relative;
            flex-wrap: wrap;
            gap: 10px;
        }

        .route-timeline::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #FF3300, #ff6b4a, #ffaa80, #ff6b4a, #FF3300);
            border-radius: 2px;
            z-index: 0;
        }

        .timeline-stop {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            z-index: 1;
            background: white;
            padding: 10px;
        }

        .stop-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(255, 51, 0, 0.3);
        }

        .stop-icon.uiu {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
        }

        .stop-name {
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-align: center;
        }

        /* Direction Toggle */
        .direction-toggle {
            display: flex;
            background: #f0f0f5;
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 25px;
            max-width: 400px;
        }

        .direction-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .direction-btn.active {
            background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 51, 0, 0.3);
        }

        .direction-btn:hover:not(.active) {
            background: #e0e0e0;
        }

        /* Map Container */
        .map-container {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .map-container h4 {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-container h4 i {
            color: #FF3300;
        }

        .map-iframe {
            width: 100%;
            height: 450px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .map-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .map-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 51, 0, 0.3);
            color: white;
        }

        /* Segment Cards */
        .segment-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .segment-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #FF3300;
            transition: all 0.3s ease;
        }

        .segment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .segment-card h5 {
            font-size: 15px;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .segment-card h5 i {
            color: #FF3300;
        }

        .segment-card a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #FF3300;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .segment-card a:hover {
            color: #cc2900;
            gap: 10px;
        }

        /* Coming Soon */
        .coming-soon-container {
            text-align: center;
            padding: 60px 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .coming-soon-icon {
            font-size: 80px;
            color: #ffc107;
            margin-bottom: 20px;
        }

        .coming-soon-container h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .coming-soon-container p {
            color: #666;
            font-size: 15px;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Route Info Grid */
        .route-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .route-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .route-info-card i {
            font-size: 28px;
            color: #FF3300;
            margin-bottom: 10px;
        }

        .route-info-card .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .route-info-card .value {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        @media (max-width: 768px) {
            .route-tabs {
                flex-direction: column;
            }

            .route-tab {
                width: 100%;
                justify-content: center;
            }

            .direction-toggle {
                max-width: 100%;
            }

            .route-timeline {
                flex-direction: column;
                gap: 20px;
            }

            .route-timeline::before {
                width: 4px;
                height: 100%;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
            }

            .map-iframe {
                height: 350px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="uiusupplementhomepage.php" class="logo">
                        <h1 class="styled-title" id="dynamicTitle">UIU Supplement</h1>
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
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-bus-alt"></i> UIU Shuttle Routes</h1>
                <p>Track shuttle routes and plan your commute with live maps</p>
            </div>

            <!-- Route Tabs -->
            <div class="route-tabs">
                <div class="route-tab active" onclick="switchRoute('notunbazar')">
                    <i class="fas fa-route"></i>
                    <span>UIU ↔ Notunbazar</span>
                </div>
                <div class="route-tab" onclick="switchRoute('kuril')">
                    <i class="fas fa-route"></i>
                    <span>UIU ↔ Kuril</span>
                    <span class="coming-soon-badge">Coming Soon</span>
                </div>
                <div class="route-tab" onclick="switchRoute('ewugate')">
                    <i class="fas fa-route"></i>
                    <span>UIU ↔ EWU Gate</span>
                    <span class="coming-soon-badge">Coming Soon</span>
                </div>
            </div>

            <!-- Notunbazar Route Content -->
            <div id="notunbazar-route" class="route-content active">
                <!-- Route Overview -->
                <div class="route-overview">
                    <h3><i class="fas fa-map-marked-alt"></i> Route Overview</h3>
                    
                    <!-- Route Info -->
                    <div class="route-info-grid">
                        <div class="route-info-card">
                            <i class="fas fa-map-signs"></i>
                            <div class="label">Total Stops</div>
                            <div class="value">4</div>
                        </div>
                        <div class="route-info-card">
                            <i class="fas fa-road"></i>
                            <div class="label">Route Distance</div>
                            <div class="value">~3 KM</div>
                        </div>
                        <div class="route-info-card">
                            <i class="fas fa-clock"></i>
                            <div class="label">Est. Duration</div>
                            <div class="value">15-20 Min</div>
                        </div>
                    </div>

                    <!-- Route Timeline -->
                    <div class="route-timeline">
                        <div class="timeline-stop">
                            <div class="stop-icon uiu"><i class="fas fa-university"></i></div>
                            <span class="stop-name">UIU Campus</span>
                        </div>
                        <div class="timeline-stop">
                            <div class="stop-icon"><i class="fas fa-map-pin"></i></div>
                            <span class="stop-name">Sayednagar</span>
                        </div>
                        <div class="timeline-stop">
                            <div class="stop-icon"><i class="fas fa-shopping-cart"></i></div>
                            <span class="stop-name">Family Bazar</span>
                        </div>
                        <div class="timeline-stop">
                            <div class="stop-icon"><i class="fas fa-flag-checkered"></i></div>
                            <span class="stop-name">Notun Bazar</span>
                        </div>
                    </div>
                </div>

                <!-- Direction Toggle -->
                <div class="direction-toggle">
                    <button class="direction-btn active" onclick="switchDirection('from-uiu')">
                        <i class="fas fa-arrow-right"></i> UIU → Notunbazar
                    </button>
                    <button class="direction-btn" onclick="switchDirection('to-uiu')">
                        <i class="fas fa-arrow-left"></i> Notunbazar → UIU
                    </button>
                </div>

                <!-- Main Map - From UIU -->
                <div id="map-from-uiu" class="map-container">
                    <h4><i class="fas fa-map"></i> UIU to Notun Bazar Route</h4>
                    <iframe 
                        class="map-iframe"
                        src="https://www.google.com/maps/embed?pb=!1m28!1m12!1m3!1d14606.394949967895!2d90.42910469999999!3d23.7978829!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m13!3e2!4m5!1s0x3755c7d8042caf2d%3A0x686fa3e360361ddf!2sUnited%20International%20University%2C%20QCXX%2B5V2%2C%20United%20City%2C%20Madani%20Ave%2C%20Dhaka%201212!3m2!1d23.7978829!2d90.44971!4m5!1s0x3755c7b0e0a0c82d%3A0x9f6e0b0c0b0d0e0f!2sNotun%20Bazar%2C%20Dhaka!3m2!1d23.797965!2d90.425739!5e0!3m2!1sen!2sbd!4v1703891234567!5m2!1sen!2sbd"
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <a href="https://maps.app.goo.gl/uoYcfuUXJkagbjWX7" target="_blank" class="map-link">
                        <i class="fas fa-external-link-alt"></i> Open in Google Maps
                    </a>
                </div>

                <!-- Main Map - To UIU (Hidden by default) -->
                <div id="map-to-uiu" class="map-container" style="display: none;">
                    <h4><i class="fas fa-map"></i> Notun Bazar to UIU Route</h4>
                    <iframe 
                        class="map-iframe"
                        src="https://www.google.com/maps/embed?pb=!1m28!1m12!1m3!1d14606.394949967895!2d90.42910469999999!3d23.7978829!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m13!3e2!4m5!1s0x3755c7b0e0a0c82d%3A0x9f6e0b0c0b0d0e0f!2sNotun%20Bazar%2C%20Dhaka!3m2!1d23.797965!2d90.425739!4m5!1s0x3755c7d8042caf2d%3A0x686fa3e360361ddf!2sUnited%20International%20University%2C%20QCXX%2B5V2%2C%20United%20City%2C%20Madani%20Ave%2C%20Dhaka%201212!3m2!1d23.7978829!2d90.44971!5e0!3m2!1sen!2sbd!4v1703891234567!5m2!1sen!2sbd"
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <a href="https://maps.app.goo.gl/mPkADk7N3SFTCQ2R9" target="_blank" class="map-link">
                        <i class="fas fa-external-link-alt"></i> Open in Google Maps
                    </a>
                </div>

                <!-- Segment Routes -->
                <div class="route-overview">
                    <h3><i class="fas fa-map-pin"></i> Segment Routes</h3>
                    <p style="color: #666; margin-bottom: 20px;">Quick access to individual route segments</p>
                    
                    <div class="segment-cards">
                        <div class="segment-card">
                            <h5><i class="fas fa-arrow-right"></i> UIU → Sayednagar</h5>
                            <a href="https://maps.app.goo.gl/RMq5DxjF43PedF8S7" target="_blank">
                                View on Maps <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="segment-card">
                            <h5><i class="fas fa-arrow-left"></i> Sayednagar → UIU</h5>
                            <a href="https://maps.app.goo.gl/S3GzjCLwwJuWjo8g6" target="_blank">
                                View on Maps <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="segment-card">
                            <h5><i class="fas fa-arrow-right"></i> UIU → Family Bazar</h5>
                            <a href="https://maps.app.goo.gl/dgwgZUr36J5EArS97" target="_blank">
                                View on Maps <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="segment-card">
                            <h5><i class="fas fa-arrow-left"></i> Family Bazar → UIU</h5>
                            <a href="https://maps.app.goo.gl/BUfBQiTc5b3bcB3Y8" target="_blank">
                                View on Maps <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kuril Route Content -->
            <div id="kuril-route" class="route-content">
                <div class="coming-soon-container">
                    <div class="coming-soon-icon">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <h3>UIU ↔ Kuril Route</h3>
                    <p>This route is currently under development. We're working on adding detailed maps and route information. Check back soon!</p>
                    <div style="margin-top: 20px;">
                        <span style="display: inline-flex; align-items: center; gap: 8px; background: #fff3cd; padding: 12px 20px; border-radius: 8px; color: #856404; font-weight: 600;">
                            <i class="fas fa-clock"></i> Coming Soon
                        </span>
                    </div>
                </div>
            </div>

            <!-- EWU Gate Route Content -->
            <div id="ewugate-route" class="route-content">
                <div class="coming-soon-container">
                    <div class="coming-soon-icon">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <h3>UIU ↔ EWU Gate Route</h3>
                    <p>This route is currently under development. We're working on adding detailed maps and route information. Check back soon!</p>
                    <div style="margin-top: 20px;">
                        <span style="display: inline-flex; align-items: center; gap: 8px; background: #fff3cd; padding: 12px 20px; border-radius: 8px; color: #856404; font-weight: 600;">
                            <i class="fas fa-clock"></i> Coming Soon
                        </span>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <script>
        // Route Tab Switching
        function switchRoute(route) {
            // Remove active class from all tabs
            document.querySelectorAll('.route-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Add active class to clicked tab
            event.currentTarget.classList.add('active');
            
            // Hide all route contents
            document.querySelectorAll('.route-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Show selected route content
            document.getElementById(route + '-route').classList.add('active');
        }

        // Direction Toggle
        function switchDirection(direction) {
            // Update buttons
            document.querySelectorAll('.direction-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Toggle map containers
            if (direction === 'from-uiu') {
                document.getElementById('map-from-uiu').style.display = 'block';
                document.getElementById('map-to-uiu').style.display = 'none';
            } else {
                document.getElementById('map-from-uiu').style.display = 'none';
                document.getElementById('map-to-uiu').style.display = 'block';
            }
        }
    </script>

    <footer class="footer">
        <div class="social-icons">
            <a href="https://www.facebook.com/sharif.me2018"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="http://uiusupplements.yzz.me/"><i class="fab fa-google"></i></a>
            <a href="https://www.instagram.com/shariful_islam10"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://www.github.com/sharif2023"><i class="fab fa-github"></i></a>
        </div>
        <div class="copyright">
            &copy; 2020 Copyright: <a href="https://www.youtube.com/@SHARIFsCODECORNER">Sharif Code Corner</a>
        </div>
    </footer>
    <script src="assets/js/index.js"></script>
</body>

</html>
