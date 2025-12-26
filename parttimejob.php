<?php
session_start();

// Authentication check
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
    <title>Part-Time Jobs | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 28px;
            color: #1f2937;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .post-job-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .post-job-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .my-jobs-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .my-jobs-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        /* Filters */
        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #FF3300;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            min-width: 150px;
            cursor: pointer;
        }

        /* Job Cards Grid */
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .job-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .job-card-header {
            padding: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .job-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .job-type-badge.part-time {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .job-type-badge.full-time {
            background: #d1fae5;
            color: #065f46;
        }

        .job-type-badge.internship {
            background: #fef3c7;
            color: #92400e;
        }

        .job-type-badge.contract {
            background: #e0e7ff;
            color: #3730a3;
        }

        .job-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px;
        }

        .job-company {
            font-size: 14px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .job-card-body {
            padding: 20px;
        }

        .job-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 14px;
            color: #4b5563;
        }

        .job-detail i {
            width: 20px;
            color: #FF3300;
        }

        .job-salary {
            font-weight: 600;
            color: #059669;
        }

        .job-card-footer {
            padding: 15px 20px;
            background: #f9fafb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .posted-by {
            font-size: 12px;
            color: #9ca3af;
        }

        .apply-btn {
            background: linear-gradient(135deg, #FF3300 0%, #dc2626 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 51, 0, 0.3);
        }

        .apply-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .applied-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Modal */
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
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 20px;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            min-height: 150px;
            resize: vertical;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #FF3300;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
        }

        .modal-actions button {
            flex: 1;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: #f3f4f6;
            color: #4b5563;
        }

        .btn-submit {
            background: linear-gradient(135deg, #FF3300 0%, #dc2626 100%);
            color: white;
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

        .empty-state h3 {
            color: #374151;
            margin-bottom: 10px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .jobs-grid {
                grid-template-columns: 1fr;
            }
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            .header-actions {
                justify-content: center;
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
                <h1><i class="fas fa-briefcase"></i> Part-Time Jobs</h1>
                <div class="header-actions">
                    <a href="myjobs.php" class="my-jobs-btn">
                        <i class="fas fa-list"></i> My Jobs
                    </a>
                    <a href="postjob.php" class="post-job-btn">
                        <i class="fas fa-plus"></i> Post a Job
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search jobs...">
                </div>
                <select class="filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Teaching">Teaching</option>
                    <option value="Research">Research</option>
                    <option value="Marketing">Marketing</option>
                    <option value="IT">IT / Tech</option>
                    <option value="Administrative">Administrative</option>
                    <option value="Other">Other</option>
                </select>
                <select class="filter-select" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="part-time">Part-Time</option>
                    <option value="full-time">Full-Time</option>
                    <option value="internship">Internship</option>
                    <option value="contract">Contract</option>
                </select>
            </div>

            <!-- Jobs Grid -->
            <div class="jobs-grid" id="jobsGrid">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading jobs...
                </div>
            </div>
        </section>
    </div>

    <!-- Apply Modal -->
    <div class="modal" id="applyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-paper-plane"></i> Apply for Job</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="applyJobTitle" style="font-weight: 600; margin-bottom: 20px;"></div>
            <form id="applyForm" enctype="multipart/form-data">
                <input type="hidden" id="applyJobId">
                <div class="form-group">
                    <label for="cvFile">Upload CV/Resume <span style="color: #ef4444;">*</span></label>
                    <input type="file" id="cvFile" accept=".pdf,.doc,.docx" required
                           style="padding: 10px; background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 10px;">
                    <p style="font-size: 12px; color: #6b7280; margin-top: 6px;">Accepted formats: PDF, DOC, DOCX (Max 5MB)</p>
                </div>
                <div class="form-group">
                    <label for="coverLetter">Cover Letter / Message (Optional)</label>
                    <textarea id="coverLetter" placeholder="Tell the employer why you're a good fit for this role..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-submit" id="applySubmitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const userId = <?php echo $_SESSION['user_id']; ?>;
        let allJobs = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadJobs();
            
            document.getElementById('searchInput').addEventListener('input', filterJobs);
            document.getElementById('categoryFilter').addEventListener('change', filterJobs);
            document.getElementById('typeFilter').addEventListener('change', filterJobs);
            
            document.getElementById('applyForm').addEventListener('submit', submitApplication);
        });

        function loadJobs() {
            fetch('api/jobs.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        allJobs = data.jobs;
                        renderJobs(allJobs);
                    }
                })
                .catch(err => {
                    document.getElementById('jobsGrid').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Error loading jobs</h3>
                            <p>Please try again later.</p>
                        </div>
                    `;
                });
        }

        function filterJobs() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value;
            const type = document.getElementById('typeFilter').value;
            
            let filtered = allJobs.filter(job => {
                const matchSearch = !search || 
                    job.title.toLowerCase().includes(search) ||
                    (job.company && job.company.toLowerCase().includes(search)) ||
                    (job.description && job.description.toLowerCase().includes(search));
                const matchCategory = !category || job.category === category;
                const matchType = !type || job.job_type === type;
                return matchSearch && matchCategory && matchType;
            });
            
            renderJobs(filtered);
        }

        function renderJobs(jobs) {
            const grid = document.getElementById('jobsGrid');
            
            if (jobs.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="fas fa-briefcase"></i>
                        <h3>No jobs found</h3>
                        <p>No jobs match your search criteria. Try adjusting your filters.</p>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = jobs.map(job => {
                const isOwnJob = job.posted_by_user_id == userId;
                return `
                <div class="job-card">
                    <div class="job-card-header">
                        <span class="job-type-badge ${job.job_type}">${job.job_type.replace('-', ' ')}</span>
                        <h3 class="job-title">${escapeHtml(job.title)}</h3>
                        <div class="job-company">
                            <i class="fas fa-building"></i> ${escapeHtml(job.company || 'Not specified')}
                        </div>
                    </div>
                    <div class="job-card-body">
                        <div class="job-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${escapeHtml(job.location)}</span>
                        </div>
                        <div class="job-detail">
                            <i class="fas fa-tag"></i>
                            <span>${escapeHtml(job.category)}</span>
                        </div>
                        ${job.salary ? `
                        <div class="job-detail">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="job-salary">${escapeHtml(job.salary)}</span>
                        </div>
                        ` : ''}
                        ${job.days_per_week ? `
                        <div class="job-detail">
                            <i class="fas fa-calendar-alt"></i>
                            <span>${job.days_per_week} days/week</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="job-card-footer">
                        <span class="posted-by">
                            <i class="fas fa-user"></i> ${escapeHtml(job.poster_name || 'Admin')}
                            ${job.application_count > 0 ? `<br><small>${job.application_count} application(s)</small>` : ''}
                        </span>
                        ${isOwnJob ? 
                            `<span class="applied-badge"><i class="fas fa-user-edit"></i> Your Job</span>` :
                            `<button class="apply-btn" onclick="openApplyModal(${job.id}, '${escapeHtml(job.title)}')">
                                <i class="fas fa-paper-plane"></i> Apply
                            </button>`
                        }
                    </div>
                </div>
                `;
            }).join('');
        }

        function openApplyModal(jobId, title) {
            document.getElementById('applyJobId').value = jobId;
            document.getElementById('applyJobTitle').textContent = title;
            document.getElementById('coverLetter').value = '';
            document.getElementById('cvFile').value = '';
            document.getElementById('applyModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('applyModal').classList.remove('active');
        }

        function submitApplication(e) {
            e.preventDefault();
            
            const jobId = document.getElementById('applyJobId').value;
            const coverLetter = document.getElementById('coverLetter').value;
            const cvFile = document.getElementById('cvFile').files[0];
            const submitBtn = document.getElementById('applySubmitBtn');
            
            if (!cvFile) {
                alert('Please upload your CV/Resume');
                return;
            }
            
            // Validate file size (5MB max)
            if (cvFile.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            const formData = new FormData();
            formData.append('job_id', jobId);
            formData.append('cover_letter', coverLetter);
            formData.append('cv_file', cvFile);
            
            fetch('api/job_applications.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Application submitted successfully! Your CV has been sent to the job poster.');
                    closeModal();
                    loadJobs();
                } else {
                    alert(data.error || 'Failed to submit application');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
            })
            .catch(err => {
                alert('Error submitting application');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modal on outside click
        document.getElementById('applyModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
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
