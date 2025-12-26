<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

$isAdmin = isset($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job | UIU Supplement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            margin: 0;
        }

        .back-btn {
            background: #f3f4f6;
            color: #4b5563;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #e5e7eb;
        }

        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .form-card-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 25px 30px;
            color: white;
        }

        .form-card-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .form-card-header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }

        .form-card-body {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: #10b981;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group label .required {
            color: #ef4444;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group .hint {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 6px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
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
                <li><a href="parttimejob.php" class="active">
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
            <div class="form-container">
                <div class="page-header">
                    <a href="parttimejob.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <h1><i class="fas fa-plus-circle"></i> Post a Job</h1>
                </div>

                <div id="alertContainer"></div>

                <div class="form-card">
                    <div class="form-card-header">
                        <h2>Job Details</h2>
                        <p>Fill in the information below to post a new job opportunity</p>
                    </div>
                    <div class="form-card-body">
                        <form id="postJobForm">
                            <!-- Basic Info -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-info-circle"></i> Basic Information
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="title">Job Title <span class="required">*</span></label>
                                        <input type="text" id="title" name="title" required 
                                               placeholder="e.g., Teaching Assistant - CSE">
                                    </div>
                                    <div class="form-group">
                                        <label for="company">Company / Organization</label>
                                        <input type="text" id="company" name="company" 
                                               placeholder="e.g., UIU, Tech Startup">
                                    </div>
                                    <div class="form-group">
                                        <label for="location">Location <span class="required">*</span></label>
                                        <input type="text" id="location" name="location" required
                                               placeholder="e.g., UIU Campus, Remote">
                                    </div>
                                    <div class="form-group">
                                        <label for="job_type">Job Type <span class="required">*</span></label>
                                        <select id="job_type" name="job_type" required>
                                            <option value="part-time">Part-Time</option>
                                            <option value="full-time">Full-Time</option>
                                            <option value="internship">Internship</option>
                                            <option value="contract">Contract</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="category">Category <span class="required">*</span></label>
                                        <select id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Teaching">Teaching</option>
                                            <option value="Research">Research</option>
                                            <option value="Marketing">Marketing</option>
                                            <option value="IT">IT / Tech</option>
                                            <option value="Administrative">Administrative</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group full-width">
                                        <label for="description">Job Description <span class="required">*</span></label>
                                        <textarea id="description" name="description" required
                                                  placeholder="Describe the role, responsibilities, and what the job entails..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Compensation -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-money-bill-wave"></i> Compensation & Schedule
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="salary">Salary / Compensation</label>
                                        <input type="text" id="salary" name="salary" 
                                               placeholder="e.g., 8000-10000 BDT/month">
                                    </div>
                                    <div class="form-group">
                                        <label for="days_per_week">Days per Week</label>
                                        <select id="days_per_week" name="days_per_week">
                                            <option value="">Select</option>
                                            <option value="1">1 day</option>
                                            <option value="2">2 days</option>
                                            <option value="3">3 days</option>
                                            <option value="4">4 days</option>
                                            <option value="5">5 days</option>
                                            <option value="6">6 days</option>
                                            <option value="7">7 days (Full week)</option>
                                        </select>
                                    </div>
                                    <div class="form-group full-width">
                                        <label for="requirements">Requirements / Qualifications</label>
                                        <textarea id="requirements" name="requirements"
                                                  placeholder="List any required skills, experience, or qualifications..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-address-book"></i> Contact Information
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="contact_email">Contact Email</label>
                                        <input type="email" id="contact_email" name="contact_email"
                                               placeholder="email@example.com">
                                    </div>
                                    <div class="form-group">
                                        <label for="contact_phone">Contact Phone</label>
                                        <input type="text" id="contact_phone" name="contact_phone"
                                               placeholder="01XXXXXXXXX">
                                    </div>
                                    <div class="form-group">
                                        <label for="expires_at">Expires On</label>
                                        <input type="date" id="expires_at" name="expires_at">
                                        <p class="hint">Leave empty for no expiration</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="submit-btn" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Post Job
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.getElementById('postJobForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            
            const formData = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                company: document.getElementById('company').value,
                location: document.getElementById('location').value,
                job_type: document.getElementById('job_type').value,
                category: document.getElementById('category').value,
                salary: document.getElementById('salary').value,
                days_per_week: document.getElementById('days_per_week').value || null,
                requirements: document.getElementById('requirements').value,
                contact_email: document.getElementById('contact_email').value,
                contact_phone: document.getElementById('contact_phone').value,
                expires_at: document.getElementById('expires_at').value || null
            };
            
            fetch('api/jobs.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertContainer').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Job posted successfully!
                        </div>
                    `;
                    setTimeout(() => {
                        // Redirect based on session type
                        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
                        window.location.href = isAdmin ? 'adminpanel.php' : 'parttimejob.php';
                    }, 1500);
                } else {
                    document.getElementById('alertContainer').innerHTML = `
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> ${data.error || 'Failed to post job'}
                        </div>
                    `;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Job';
                }
            })
            .catch(err => {
                document.getElementById('alertContainer').innerHTML = `
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> Error posting job
                    </div>
                `;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Job';
            });
        });
    </script>

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
</body>

</html>
