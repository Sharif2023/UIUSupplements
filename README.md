# UIU Supplements

**UIU Supplements** is a comprehensive student life management platform designed specifically for students of United International University (UIU). It serves as a one-stop solution for various student needs, ranging from finding accommodation and mentorship to buying/selling academic materials and finding part-time jobs.

## 🚀 Key Features

### 1. 🛒 Sell & Exchange Marketplace
*   **Buy & Sell**: Students can list items (books, gadgets, accessories) for sale.
*   **Bargaining System**: Integrated real-time bargaining allows buyers to negotiate prices with sellers.
*   **My Deals**: Track accepted offers and finalized deals.

### 2. 🏠 Room Rental Service
*   **Find Rooms**: Browse available rooms near the campus with photos, rent details, and location.
*   **Secure Booking**: Rent rooms directly through the platform (password-protected confirmation).
*   **Advanced Search**: Filter rooms by location and sort by rent.

### 3. 👨‍🏫 Mentorship Program
*   **Connect with Mentors**: Find experienced seniors or alumni for academic guidance.
*   **Profile Views**: View detailed mentor profiles including their expertise and bio.
*   **Session Booking**: Request mentorship sessions.

### 4. 💼 Part-Time Jobs
*   **Job Listings**: Access part-time job opportunities relevant to students.
*   **Easy Application**: Apply directly with a cover letter and CV upload.
*   **Email Notifications**: Job posters receive immediate email alerts with applicant CVs.

### 5. 🔍 Lost & Found
*   **Report Items**: Post details about lost items with images and location.
*   **Claim Items**: Students can claim found items by submitting proof of ownership (ID upload).
*   **Verification**: Claims are verified before approval.

### 6. 💬 Communication
*   **Real-time Chat**: Integrated messaging system to communicate with sellers, mentors, or other students.
*   **Notifications**: Real-time alerts for bargains, messages, and job applications.

### 7. 🚌 Shuttle Service
*   **Tracking**: View information about university shuttle services.

## 🛠️ Technology Stack
*   **Frontend**: HTML5, CSS3, JavaScript (Vanilla + Bootstrap)
*   **Backend**: PHP (Native)
*   **Database**: MySQL
*   **Server**: Apache (XAMPP/WAMP)

## ⚙️ Installation & Setup

1.  **Prerequisites**:
    *   Install [XAMPP](https://www.apachefriends.org/index.html) or any PHP local server environment.

2.  **Clone/Download**:
    *   Place the project folder `UIU_Supplements_Live` into your `htdocs` directory (e.g., `C:\xampp\htdocs\`).

3.  **Database Setup**:
    *   Open phpMyAdmin (`http://localhost/phpmyadmin`).
    *   Create a new database named `uiusupplements`.
    *   Import the `database/uiusupplements.sql` file provided in the project directory.
    *   (Optional) If you have migration files like `database/jobs_migration.sql`, visual check if they need to be imported.

4.  **Configuration**:
    *   Update database connection settings in PHP files if your MySQL credentials differ from default (User: `root`, Pass: ``).

5.  **Run the Application**:
    *   Open your browser and navigate to: `http://localhost/UIU_Supplements_Live/uiusupplementhomepage.php`

## 👥 User Roles

*   **Student**: Can access all standard features (Buy/Sell, Rent, Apply for jobs, etc.).
*   **Admin**: Access to the Admin Panel to manage users, approve/reject posts, manage rooms, and oversee the platform.

## 📁 Project Structure

*   `adminpanel/`: Admin dashboard scripts and styles.
*   `api/`: Backend API endpoints for AJAX requests.
*   `assets/`: CSS, JavaScript, and image resources.
*   `database/`: SQL files for database schema.
*   `uploads/`: Directory for user-uploaded files (CVs, images).
*   `*.php`: Core application pages.

---
*Developed for UIU Students*
