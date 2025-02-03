<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total number of users
$sql_users = "SELECT COUNT(*) as total_users FROM users";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total_users'];

// Fetch total number of admins
$sql_admins = "SELECT COUNT(*) as total_admins FROM admins";
$result_admins = $conn->query($sql_admins);
$total_admins = $result_admins->fetch_assoc()['total_admins'];

// Fetch total number of rooms
$sql_rooms = "SELECT COUNT(*) as total_rooms FROM availablerooms";
$result_rooms = $conn->query($sql_rooms);
$total_rooms = $result_rooms->fetch_assoc()['total_rooms'];

// Fetch total number of mentors
$sql_mentors = "SELECT COUNT(*) as total_mentors FROM uiumentorlist";
$result_mentors = $conn->query($sql_mentors);
$total_mentors = $result_mentors->fetch_assoc()['total_mentors'];

// Fetch new users (descending order by registration date, assuming you have a `created_at` column)
$sql_new_users = "SELECT id, username, email, mobilenumber FROM users ORDER BY id DESC LIMIT 10"; // Modify limit as per requirement
$new_users_result = $conn->query($sql_new_users);

// Fetch new mentors in descending order
$new_mentors_query = "SELECT * FROM uiumentorlist ORDER BY id DESC LIMIT 6"; // Assuming 'id' increments with each new mentor
$new_mentors_result = mysqli_query($conn, $new_mentors_query);

// Fetch available rooms in descending order
$sql_available_rooms = "SELECT room_id, room_location, room_rent FROM availablerooms ORDER BY room_id DESC";
$result_available_rooms = $conn->query($sql_available_rooms);

// Fetch appointed rooms in descending order
$sql_appointed_rooms = "SELECT appointed_room_id, appointed_user_id FROM appointedrooms ORDER BY appointed_room_id DESC";
$result_appointed_rooms = $conn->query($sql_appointed_rooms);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .admin-panel-title {
            flex: 1;
            text-align: center;
            font-size: 30px;
            color: #333;
            padding-top: 10px;
        }

        body {
            min-height: 100vh;
            color: #555;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        a {
            text-decoration: none;
        }

        li {
            list-style: none;
        }

        h1 {
            padding-top: 20px;
            font-family: "Montserrat", sans-serif;
            font-weight: 800;
            font-size: 1.5vw;
            text-transform: uppercase;
        }

        .title-word {
            animation: color-animation 4s linear infinite;
        }

        .title-word-1 {
            --color-1: #DF8453;
            --color-2: #3D8DAE;
            --color-3: #E4A9A8;
        }

        .title-word-2 {
            --color-1: #DBAD4A;
            --color-2: #ACCFCB;
            --color-3: #17494D;
        }

        .title-word-3 {
            --color-1: #ACCFCB;
            --color-2: #E4A9A8;
            --color-3: #ACCFCB;
        }

        .title-word-4 {
            --color-1: #3D8DAE;
            --color-2: #DF8453;
            --color-3: #E4A9A8;
        }

        @keyframes color-animation {
            0% {
                color: var(--color-1)
            }

            32% {
                color: var(--color-1)
            }

            33% {
                color: var(--color-2)
            }

            65% {
                color: var(--color-2)
            }

            66% {
                color: var(--color-3)
            }

            99% {
                color: var(--color-3)
            }

            100% {
                color: var(--color-1)
            }
        }

        h2 {
            color: #555;
        }

        h3 {
            color: #555;
        }

        .btn {
            background: #ff3300;
            color: white;
            padding: 5px 10px;
            text-align: center;
            border-radius: 10px;
        }

        .btn:hover {
            color: #ff3300;
            background: white;
            padding: 3px 8px;
            border: 2px solid #ff3300;
        }

        .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 10px;
            border-bottom: 2px solid #999;
        }

        table {
            padding: 10px;
            width: 100%;
        }

        th,
        td {
            text-align: left;
            padding: 8px;
        }

        .side-menu {
            position: fixed;
            background: #1F1F1F;
            width: 20vw;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px 0px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .side-menu .title-name {
            height: 10vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .side-menu ul {
            padding: 0;
            /* Reset padding */
            margin: 0;
            /* Reset margin */
            flex-grow: 1;
            /* Allow the list to grow and take available space */
        }

        .side-menu li {
            font-size: 18px;
            padding: 15px 20px;
            /* Add more padding for better spacing */
            color: #f1f1f1;
            display: flex;
            align-items: center;
            gap: 15px;
            /* Increased gap for better spacing */
            transition: background 0.3s;
            /* Add a transition effect for hover */
        }

        .side-menu li:hover {
            background: rgba(255, 255, 255, 0.2);
            /* Slightly transparent background on hover */
        }

        .logout-btn {
            background-color: #ff3300;
            color: white;
            padding: 10px 30px;
            text-align: center;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            margin: 20px 10px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 30px;
        }

        .logout-btn:hover {
            background-color: #1F1F1F;
        }

        .container {
            position: absolute;
            right: 0;
            width: 80vw;
            height: 100vh;
            background: #f1f1f1;
        }

        .container .header {
            position: relative;
            top: 0;
            right: 0;
            width: 80vw;
            height: 10vh;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            z-index: 1;
        }

        .container .header .nav {
            width: 90%;
            display: flex;
            align-items: center;
        }

        .container .header .nav .search {
            flex: 3;
            display: flex;
            justify-content: center;
            border-radius: 10px;
        }

        .container .header .nav .search input[type=text] {
            border: none;
            background: #f1f1f1;
            padding: 5px;
            width: 50%;
            border-radius: 10px;
        }

        .container .header .nav .search button {
            width: 40px;
            height: 40px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .container .header .nav .search button img {
            width: 30px;
            height: 30px;
        }

        .container .header .nav .user {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container .header .nav .user img {
            width: 40px;
            height: 40px;
        }

        .container .header .nav .user .img-case {
            position: relative;
            width: 50px;
            height: 50px;
        }

        .container .header .nav .user .img-case img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .header {
            margin: 10px 20px;
        }

        .container .content {
            position: relative;
            margin-top: 10vh;
            min-height: 90vh;
            background: #f1f1f1;
        }

        .container .content .cards {
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .container .content .cards .card {
            width: 250px;
            height: 150px;
            background: white;
            margin: 20px 10px;
            display: flex;
            align-items: center;
            justify-content: space-around;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .container .content .content-2 {
            min-height: 60vh;
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .container .content .content-2 .new-users,
        .container .content .content-2 .new-mentors {
            background: white;
            min-height: 50vh;
            margin: 20px 40px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            flex: 2;
        }

        .container .content .content-2 .new-mentors table {
            width: 100%;
            /* Ensure table uses the full width */
            table-layout: fixed;
            /* Set table layout to fixed */
        }

        .container .content .content-2 .new-mentors table td {
            overflow: hidden;
            /* Prevent overflow */
            text-overflow: ellipsis;
            /* Add ellipsis for overflow text */
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        .container .content .content-2 .new-mentors table td:nth-child(1) img {
            height: 50px;
            /* Reduced height */
            width: 50px;
            /* Reduced width */
            object-fit: cover;
            /* Ensures images are resized correctly */
            border-radius: 50%;
            /* Optional: Makes the images circular */
            max-width: 100%;
            /* Ensure images do not exceed container */
        }

        .cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card {
            width: 22%;
            background-color: #f1f1f1;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.3s ease-in-out;
        }

        .card:hover {
            background-color: #e2e2e2;
        }

        .card .box {
            text-align: left;
        }

        .card .box h1 {
            font-size: 36px;
            margin: 0;
            color: #333;
        }

        .card .box h3 {
            margin: 10px 0 0;
            font-size: 18px;
            color: #777;
        }

        .card .icon-case {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card .icon-case img {
            width: 50px;
            height: 50px;
        }

        /* Specific colors for each card */
        .card.users {
            background-color: #ff6666;
            /* Red */
        }

        .card.admins {
            background-color: #66b3ff;
            /* Blue */
        }

        .card.rooms {
            background-color: #66ff66;
            /* Green */
        }

        .card.mentors {
            background-color: #ffcc66;
            /* Yellow */
        }

        .mentor-profile {
            height: 50px;
            /* Height for the mentor photo */
            width: 50px;
            /* Width for the mentor photo */
            object-fit: cover;
            /* Ensures images are resized correctly */
            border-radius: 50%;
            /* Circular profile image */
        }

        /* New Mentor Container */
        .container-mentors {
            background-color: white;
            /* White background */
            padding: 20px;
            /* Padding inside the container */
            margin: 20px 40px;
            /* Add margin at the top for spacing */
            border-radius: 10px;
            /* Rounded corners for the container */
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.1), 0 6px 20px 0 rgba(0, 0, 0, 0.1);
            /* Light shadow for a subtle 3D effect */
        }

        /* Table inside Mentor Container */
        .container-mentors table {
            width: 100%;
            /* Make table fill the width of the container */
            table-layout: fixed;
            /* Ensure table columns have fixed width */
        }

        .container-mentors table td {
            overflow: hidden;
            /* Prevent content from overflowing */
            text-overflow: ellipsis;
            /* Add ellipsis for overflow text */
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        .container-mentors table td:nth-child(1) img {
            height: 50px;
            /* Image size */
            width: 50px;
            object-fit: cover;
            border-radius: 50%;
            /* Circular image */
        }

        /* Container for room data */
        .container-rooms {
            background-color: white;
            /* White background */
            padding: 20px;
            margin: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.1), 0 6px 20px 0 rgba(0, 0, 0, 0.1);
        }

        .container-rooms h2 {
            margin-bottom: 10px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Add styles for the pop-up form and admin panel */
        .popup,
        .message-popup {
            display: none;
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .popup-content,
        .message-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .popup input {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
        }

        .popup .btn,
        .message-popup .btn {
            padding: 10px 20px;
            background-color: #ff0000;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button {
            background: #ff3300;
            color: white;
            padding: 6px 10px;
            text-align: center;
            border-radius: 10px;
            border: none;
        }

        button:hover {
            color: #ff3300;
            background: white;
            padding: 3px 8px;
            border: 2px solid #ff3300;
        }
    </style>
    <title>Admin Panel</title>
</head>

<body>
    <div class="side-menu">
        <div class="title-name">
            <h1 class="title"><span class="title-word title-word-1">U</span>
                <span class="title-word title-word-2">I</span>
                <span class="title-word title-word-3">U</span>
                <span class="title-word title-word-4">Supplement</span>
            </h1>
        </div>
        <ul>
            <li><i class="fas fa-tachometer-alt"></i> <span>Home</span></li>
            <li><i class="fas fa-coins"></i> <span>Sell Request</span></li>
            <li><i class="fas fa-building"></i> <span>Room Request</span></li>
            <li><i class="fas fa-camera-retro"></i> <span>Lost and Found Request</span></li>
        </ul>
        <a href="uiusupplementlogin.html" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
    </div>
    <div class="container">
        <div>
            <h2 class="admin-panel-title">Admin Panel</h2>
        </div>
        <div class="header">
            <div class="nav">
                <div class="search">
                    <input type="text" placeholder="Search..">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <div class="add-driver-button">
                    <a href="#" class="btn">Add Driver</a>
                </div>
                <div class="user">
                    <a href="addnewmentor.php" class="btn">Add New Mentor</a>
                    <i class="fas fa-bell"></i>
                    <div class="img-case">
                        <img src="adminpanel/user.png" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="cards">
                <div class="card">
                    <div class="box">
                        <h1><?php echo $total_users; ?></h1>
                        <h3>Users</h3>
                    </div>
                    <div class="icon-case">
                        <img src="adminpanel/students.png" alt="">
                    </div>
                </div>
                <div class="card">
                    <div class="box">
                        <h1><?php echo $total_admins; ?></h1>
                        <h3>Admins</h3>
                    </div>
                    <div class="icon-case">
                        <img src="adminpanel/teachers.png" alt="">
                    </div>
                </div>
                <div class="card">
                    <div class="box">
                        <h1><?php echo $total_rooms; ?></h1>
                        <h3>Rooms</h3>
                    </div>
                    <div class="icon-case">
                        <img src="adminpanel/schools.png" alt="">
                    </div>
                </div>
                <div class="card">
                    <div class="box">
                        <h1><?php echo $total_mentors; ?></h1>
                        <h3>Mentors</h3>
                    </div>
                    <div class="icon-case">
                        <img src="adminpanel/income.png" alt="">
                    </div>
                </div>
            </div>

            <div class="content-2">
                <div class="new-users">
                    <div class="title">
                        <h2>New Users</h2>
                        <button onclick="openPopup('deleteUserPopup')">Delete User Account</button>
                    </div>
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile Number</th>
                        </tr>
                        <?php while ($row = $new_users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['mobilenumber']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
            <div class="container-mentors">
                <div class="new-mentors">
                    <div class="title">
                        <h2>New Mentors</h2>
                        <div>
                            <a href="browsementors.html" class="btn">View All</a>
                            <button onclick="openPopup('deleteMentorPopup')">Delete Mentors</button>
                        </div>
                    </div>
                    <table>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Option</th>
                        </tr>
                        <?php while ($mentor = mysqli_fetch_assoc($new_mentors_result)) { ?>
                            <tr>
                                <td><img src="<?php echo $mentor['photo']; ?>" alt="mentor profile" class="mentor-profile"></td>
                                <td><?php echo $mentor['name']; ?></td>
                                <td><a href="#" class="btn">View</a></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
            <!-- Available Rooms Container -->
            <div class="container-rooms">
                <div class="title">
                    <h2>Available Rooms</h2>
                    <div>
                        <a href="availablerooms.html" class="btn">View All</a>
                        <button onclick="openPopup('deleteRoomPopup')">Delete Rooms</button>
                    </div>
                </div>
                <table>
                    <tr>
                        <th>Room ID</th>
                        <th>Location</th>
                        <th>Rent</th>
                    </tr>
                    <?php while ($row = $result_available_rooms->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['room_id']; ?></td>
                            <td><?php echo $row['room_location']; ?></td>
                            <td><?php echo $row['room_rent']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <!-- Appointed Rooms Container -->
            <div class="container-rooms">
                <div class="title">
                    <h2>Rented Rooms</h2>
                    <div>
                        <a href="appointedrooms.html" class="btn">View All</a>
                        <button onclick="openPopup('deleteRentalPopup')">Remove Rental Status</button>
                    </div>
                </div>
                <table>
                    <tr>
                        <th>Room ID</th>
                        <th>User ID</th>
                    </tr>
                    <?php while ($row = $result_appointed_rooms->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['appointed_room_id']; ?></td>
                            <td><?php echo $row['appointed_user_id']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
    <!-- Pop-up for deleting user -->
    <div id="deleteUserPopup" class="popup">
        <div class="popup-content">
            <h3>Delete User Account</h3>
            <form id="deleteUserForm" method="POST">
                <input type="hidden" name="action" value="delete_user">
                <input type="text" name="user_id" placeholder="Enter User ID" required>
                <button type="submit" class="btn">Delete</button>
                <button type="button" class="btn" onclick="closePopup('deleteUserPopup')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Pop-up for deleting room -->
    <div id="deleteRoomPopup" class="popup">
        <div class="popup-content">
            <h3>Delete Room</h3>
            <form id="deleteRoomForm" method="POST">
                <input type="hidden" name="action" value="delete_room">
                <input type="text" name="room_id" placeholder="Enter Room ID" required>
                <button type="submit" class="btn">Delete</button>
                <button type="button" class="btn" onclick="closePopup('deleteRoomPopup')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Pop-up for deleting mentor -->
    <div id="deleteMentorPopup" class="popup">
        <div class="popup-content">
            <h3>Delete Mentor</h3>
            <form id="deleteMentorForm" method="POST">
                <input type="hidden" name="action" value="delete_mentor">
                <input type="email" name="mentor_email" placeholder="Enter Mentor Email" required>
                <button type="submit" class="btn">Delete</button>
                <button type="button" class="btn" onclick="closePopup('deleteMentorPopup')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Success/Error Message Popup -->
    <div id="messagePopup" class="message-popup">
        <div class="message-content">
            <h3 id="messageText"></h3>
            <button type="button" class="btn" onclick="closePopup('messagePopup')">Close</button>
        </div>
    </div>

    <script>
        // JavaScript to open and close popups
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function showMessagePopup(message) {
            document.getElementById('messageText').innerText = message;
            openPopup('messagePopup');
            setTimeout(function() {
                closePopup('messagePopup');
            }, 3000); // Close after 3 seconds
        }

        // Function to handle form submission via AJAX
        function handleFormSubmission(formId, popupId) {
            var form = document.getElementById(formId);
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(form);

                fetch('delete.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        closePopup(popupId); // Close the specific popup
                        showMessagePopup(data); // Show success or error message in popup
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessagePopup('An error occurred. Please try again.');
                    });
            });
        }

        // Attach form submissions with AJAX for each form
        handleFormSubmission('deleteUserForm', 'deleteUserPopup');
        handleFormSubmission('deleteRoomForm', 'deleteRoomPopup');
        handleFormSubmission('deleteMentorForm', 'deleteMentorPopup');
    </script>
</body>

</html>