<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Jobs | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #e5e7eb;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .tab-btn {
            flex: 1;
            padding: 14px 20px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #FF3300 0%, #dc2626 100%);
            color: white;
        }

        .tab-btn:not(.active):hover {
            background: #f3f4f6;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Job Cards */
        .jobs-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .job-item {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: all 0.3s;
        }

        .job-item:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .job-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .job-content {
            flex: 1;
        }

        .job-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px;
        }

        .job-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .job-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .job-meta i {
            color: #FF3300;
        }

        .job-stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
        }

        .stat-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .stat-badge.applications {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .stat-badge.status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .stat-badge.status-closed {
            background: #f3f4f6;
            color: #6b7280;
        }

        .job-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .action-btn {
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-view {
            background: #e0e7ff;
            color: #3730a3;
        }

        .btn-edit {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-view-apps {
            background: #dbeafe;
            color: #1d4ed8;
        }

        /* Application Items */
        .application-item {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 15px;
        }

        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .application-job-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px;
        }

        .application-company {
            font-size: 14px;
            color: #6b7280;
        }

        .application-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-reviewed { background: #dbeafe; color: #1d4ed8; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        .application-details {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #6b7280;
            margin-top: 10px;
        }

        .application-details span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        /* Applications Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
        }

        .modal-body {
            padding: 20px 25px;
        }

        .applicant-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .applicant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .applicant-name {
            font-weight: 600;
            font-size: 16px;
        }

        .applicant-contact {
            font-size: 13px;
            color: #6b7280;
        }

        .cover-letter {
            background: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            color: #4b5563;
            margin: 10px 0;
        }

        .status-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .status-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }

        .status-btn.accept {
            background: #10b981;
            color: white;
        }

        .status-btn.reject {
            background: #ef4444;
            color: white;
        }

        @media (max-width: 768px) {
            .job-item {
                flex-direction: column;
            }
            .job-actions {
                width: 100%;
            }
            .tabs {
                flex-direction: column;
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
            <div class="page-header">
                <h1><i class="fas fa-briefcase"></i> My Jobs</h1>
                <a href="parttimejob.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Browse Jobs
                </a>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('posted')">
                    <i class="fas fa-edit"></i> My Posted Jobs
                </button>
                <button class="tab-btn" onclick="switchTab('applications')">
                    <i class="fas fa-paper-plane"></i> My Applications
                </button>
            </div>

            <!-- Posted Jobs Tab -->
            <div class="tab-content active" id="posted-tab">
                <div class="jobs-list" id="postedJobsList">
                    <div class="empty-state">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>

            <!-- Applications Tab -->
            <div class="tab-content" id="applications-tab">
                <div id="applicationsList">
                    <div class="empty-state">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- View Applications Modal -->
    <div class="modal" id="applicationsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-users"></i> <span id="modalJobTitle">Applications</span></h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="applicantsContainer">
                Loading...
            </div>
        </div>
    </div>

    <script>
        const userId = <?php echo $userId; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            loadPostedJobs();
            loadApplications();
        });

        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
            document.getElementById(`${tab}-tab`).classList.add('active');
        }

        function loadPostedJobs() {
            fetch('api/jobs.php?my_jobs=1')
                .then(r => r.json())
                .then(data => {
                    const container = document.getElementById('postedJobsList');
                    if (data.success && data.jobs.length > 0) {
                        container.innerHTML = data.jobs.map(job => `
                            <div class="job-item">
                                <div class="job-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="job-content">
                                    <h3 class="job-title">${escapeHtml(job.title)}</h3>
                                    <div class="job-meta">
                                        <span><i class="fas fa-building"></i> ${escapeHtml(job.company || 'Not specified')}</span>
                                        <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(job.location)}</span>
                                        <span><i class="fas fa-tag"></i> ${escapeHtml(job.category)}</span>
                                    </div>
                                    <div class="job-stats">
                                        <span class="stat-badge applications">
                                            <i class="fas fa-users"></i> ${job.application_count || 0} Applications
                                        </span>
                                        <span class="stat-badge status-${job.status}">
                                            ${job.status.charAt(0).toUpperCase() + job.status.slice(1)}
                                        </span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    ${job.application_count > 0 ? `
                                        <button class="action-btn btn-view-apps" onclick="viewApplications(${job.id}, '${escapeHtml(job.title)}')">
                                            <i class="fas fa-users"></i> View
                                        </button>
                                    ` : ''}
                                    <button class="action-btn btn-delete" onclick="deleteJob(${job.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-plus-circle"></i>
                                <h3>No Jobs Posted</h3>
                                <p>You haven't posted any jobs yet.</p>
                                <a href="postjob.php" class="action-btn btn-view" style="display: inline-flex; margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Post a Job
                                </a>
                            </div>
                        `;
                    }
                });
        }

        function loadApplications() {
            fetch('api/jobs.php?my_applications=1')
                .then(r => r.json())
                .then(data => {
                    const container = document.getElementById('applicationsList');
                    if (data.success && data.applications.length > 0) {
                        container.innerHTML = data.applications.map(app => `
                            <div class="application-item">
                                <div class="application-header">
                                    <div>
                                        <h4 class="application-job-title">${escapeHtml(app.title)}</h4>
                                        <p class="application-company">${escapeHtml(app.company || 'Company')} • Posted by ${escapeHtml(app.poster_name)}</p>
                                    </div>
                                    <span class="application-status status-${app.status}">${app.status}</span>
                                </div>
                                <div class="application-details">
                                    <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(app.location)}</span>
                                    <span><i class="fas fa-money-bill-wave"></i> ${escapeHtml(app.salary || 'Not specified')}</span>
                                    <span><i class="fas fa-clock"></i> Applied ${formatDate(app.applied_at)}</span>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-paper-plane"></i>
                                <h3>No Applications</h3>
                                <p>You haven't applied to any jobs yet.</p>
                                <a href="parttimejob.php" class="action-btn btn-view" style="display: inline-flex; margin-top: 15px;">
                                    <i class="fas fa-search"></i> Browse Jobs
                                </a>
                            </div>
                        `;
                    }
                });
        }

        function viewApplications(jobId, title) {
            document.getElementById('modalJobTitle').textContent = title + ' - Applications';
            document.getElementById('applicationsModal').classList.add('active');
            
            fetch(`api/job_applications.php?job_id=${jobId}`)
                .then(r => r.json())
                .then(data => {
                    const container = document.getElementById('applicantsContainer');
                    if (data.success && data.applications.length > 0) {
                        container.innerHTML = data.applications.map(app => `
                            <div class="applicant-card">
                                <div class="applicant-header">
                                    <div>
                                        <div class="applicant-name">${escapeHtml(app.username)}</div>
                                        <div class="applicant-contact">
                                            <i class="fas fa-envelope"></i> ${escapeHtml(app.email)}
                                            ${app.mobilenumber ? ` • <i class="fas fa-phone"></i> ${escapeHtml(app.mobilenumber)}` : ''}
                                        </div>
                                    </div>
                                    <span class="application-status status-${app.status}">${app.status}</span>
                                </div>
                                ${app.cover_letter ? `<div class="cover-letter">${escapeHtml(app.cover_letter)}</div>` : ''}
                                <div style="font-size: 12px; color: #9ca3af;">Applied ${formatDate(app.applied_at)}</div>
                                ${app.status === 'pending' ? `
                                    <div class="status-actions">
                                        <button class="status-btn accept" onclick="updateStatus(${app.id}, 'accepted')">
                                            <i class="fas fa-check"></i> Accept
                                        </button>
                                        <button class="status-btn reject" onclick="updateStatus(${app.id}, 'rejected')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p style="text-align: center; color: #6b7280;">No applications yet.</p>';
                    }
                });
        }

        function updateStatus(appId, status) {
            fetch('api/job_applications.php', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({application_id: appId, status: status})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Failed to update status');
                }
            });
        }

        function deleteJob(jobId) {
            if (!confirm('Are you sure you want to delete this job?')) return;
            
            fetch(`api/jobs.php?id=${jobId}`, {method: 'DELETE'})
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        loadPostedJobs();
                    } else {
                        alert(data.error || 'Failed to delete job');
                    }
                });
        }

        function closeModal() {
            document.getElementById('applicationsModal').classList.remove('active');
        }

        function formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.getElementById('applicationsModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
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
</body>

</html>
