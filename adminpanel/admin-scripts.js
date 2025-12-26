// Admin Panel JavaScript

// Global State
let currentPage = 'dashboard';
let currentUserPage = 1;
let currentMentorPage = 1;
let currentRoomPage = 1;

// Chart instances - track to prevent canvas reuse errors
let growthChartInstance = null;
let usageChartInstance = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    initNavigation();
    loadDashboard();
    loadNotifications();
    setupEventListeners();
});

// Navigation
function initNavigation() {
    const menuItems = document.querySelectorAll('.menu-item');

    menuItems.forEach(item => {
        item.addEventListener('click', function () {
            // Update active state
            menuItems.forEach(mi => mi.classList.remove('active'));
            this.classList.add('active');

            // Get page name
            const page = this.getAttribute('data-page');
            currentPage = page;

            // Update page title
            const pageTitle = this.querySelector('span').textContent;
            document.getElementById('pageTitle').textContent = pageTitle;

            // Show page section
            const sections = document.querySelectorAll('.page-section');
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(page).classList.add('active');

            // Load page data
            loadPageData(page);
        });
    });
}

// Load Data based on page
function loadPageData(page) {
    switch (page) {
        case 'dashboard':
            loadDashboard();
            break;
        case 'users':
            loadUsers();
            break;
        case 'mentors':
            loadMentors();
            break;
        case 'rooms':
            loadRooms();
            break;
        case 'products':
            loadProducts();
            break;
        case 'lostandfound':
            loadLostFound();
            break;
        case 'shuttle':
            loadDrivers();
            break;
        case 'sessions':
            loadSessions();
            break;
        case 'jobs':
            loadJobs();
            break;
        case 'analytics':
            loadAnalytics();
            break;
    }
}

// Dashboard Data
async function loadDashboard() {
    try {
        const response = await fetch('api/admin_analytics.php?type=overview');
        const data = await response.json();

        if (data.success) {
            // Update stats
            document.getElementById('totalUsers').textContent = data.stats.total_users || 0;
            document.getElementById('totalMentors').textContent = data.stats.total_mentors || 0;
            document.getElementById('totalRooms').textContent = data.stats.total_rooms || 0;
            document.getElementById('pendingSessions').textContent = data.stats.pending_sessions || 0;

            // Load growth chart
            loadGrowthChart();
            loadUsageChart(data.stats);
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showToast('Failed to load dashboard data', 'error');
    }
}

// Growth Chart
async function loadGrowthChart() {
    try {
        const response = await fetch('api/admin_analytics.php?type=growth');
        const data = await response.json();

        if (data.success && data.growth.length > 0) {
            const ctx = document.getElementById('growthChart').getContext('2d');

            // Destroy existing chart instance to prevent canvas reuse error
            // Use Chart.getChart() for more reliable detection
            const existingGrowthChart = Chart.getChart('growthChart');
            if (existingGrowthChart) {
                existingGrowthChart.destroy();
            }

            growthChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.growth.map(item => item.date),
                    datasets: [{
                        label: 'New Users',
                        data: data.growth.map(item => item.count),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading growth chart:', error);
    }
}

// Usage Chart
function loadUsageChart(stats) {
    const ctx = document.getElementById('usageChart').getContext('2d');

    // Destroy existing chart instance to prevent canvas reuse error
    // Use Chart.getChart() for more reliable detection
    const existingUsageChart = Chart.getChart('usageChart');
    if (existingUsageChart) {
        existingUsageChart.destroy();
    }

    usageChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Users', 'Mentors', 'Rooms', 'Products', 'Lost Items'],
            datasets: [{
                data: [
                    stats.total_users || 0,
                    stats.total_mentors || 0,
                    stats.total_rooms || 0,
                    stats.total_products || 0,
                    stats.total_lost_items || 0
                ],
                backgroundColor: [
                    '#667eea',
                    '#f093fb',
                    '#4facfe',
                    '#43e97b',
                    '#fa709a'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Load Users
async function loadUsers(page = 1, search = '') {
    const container = document.getElementById('usersTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading users...</p></div>';

    try {
        const response = await fetch(`api/admin_users.php?page=${page}&limit=10&search=${search}`);
        const data = await response.json();

        if (data.success) {
            let html = '<table><thead><tr>';
            html += '<th>ID</th><th>Username</th><th>Email</th><th>Mobile</th><th>Gender</th><th>Joined</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.users.forEach(user => {
                const joinDate = new Date(user.created_at).toLocaleDateString();
                html += `<tr>
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${user.mobilenumber}</td>
                    <td>${user.Gender === 'm' ? 'Male' : user.Gender === 'f' ? 'Female' : 'Other'}</td>
                    <td>${joinDate}</td>
                    <td>
                        <button class="btn btn-secondary" onclick='editUser(${JSON.stringify(user)})' style="padding: 6px 12px; margin-right: 5px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger" onclick="deleteUser(${user.id})" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';

            // Add pagination
            if (data.totalPages > 1) {
                html += '<div class="pagination">';
                html += `<button onclick="loadUsers(${page - 1}, '${search}')" ${page === 1 ? 'disabled' : ''}>Previous</button>`;
                for (let i = 1; i <= data.totalPages; i++) {
                    html += `<button onclick="loadUsers(${i}, '${search}')" ${i === page ? 'class="active"' : ''}>${i}</button>`;
                }
                html += `<button onclick="loadUsers(${page + 1}, '${search}')" ${page === data.totalPages ? 'disabled' : ''}>Next</button>`;
                html += '</div>';
            }

            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading users:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h4>Error loading users</h4></div>';
    }
}

// Load Mentors
async function loadMentors(page = 1, search = '') {
    const container = document.getElementById('mentorsTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading mentors...</p></div>';

    try {
        const response = await fetch(`api/admin_mentors.php?page=${page}&limit=10&search=${search}`);
        const data = await response.json();

        if (data.success) {
            let html = '<table><thead><tr>';
            html += '<th>Photo</th><th>Name</th><th>Company</th><th>Industry</th><th>Email</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.mentors.forEach(mentor => {
                html += `<tr>
                    <td><img src="${mentor.photo}" class="mentor-photo" alt="${mentor.name}"></td>
                    <td>${mentor.name}</td>
                    <td>${mentor.company}</td>
                    <td>${mentor.industry}</td>
                    <td>${mentor.email}</td>
                    <td>
                        <a href="viewmentorprofile.php?id=${mentor.id}" class="btn btn-secondary" style="padding: 6px 12px; margin-right: 5px;">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-danger" onclick="deleteMentor(${mentor.id})" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';

            // Add pagination
            if (data.totalPages > 1) {
                html += '<div class="pagination">';
                html += `<button onclick="loadMentors(${page - 1}, '${search}')" ${page === 1 ? 'disabled' : ''}>Previous</button>`;
                for (let i = 1; i <= data.totalPages; i++) {
                    html += `<button onclick="loadMentors(${i}, '${search}')" ${i === page ? 'class="active"' : ''}>${i}</button>`;
                }
                html += `<button onclick="loadMentors(${page + 1}, '${search}')" ${page === data.totalPages ? 'disabled' : ''}>Next</button>`;
                html += '</div>';
            }

            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading mentors:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h4>Error loading mentors</h4></div>';
    }
}

// Load Rooms
async function loadRooms(page = 1, search = '', status = '') {
    const container = document.getElementById('roomsTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading rooms...</p></div>';

    try {
        const response = await fetch(`api/admin_rooms.php?page=${page}&limit=10&search=${search}&status=${status}`);
        const data = await response.json();

        if (data.success) {
            let html = '<table><thead><tr>';
            html += '<th>Room ID</th><th>Location</th><th>Rent</th><th>Available From</th><th>Status</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.rooms.forEach(room => {
                const statusClass = room.status === 'available' ? 'status-available' : 'status-not-available';
                html += `<tr>
                    <td>${room.room_id}</td>
                    <td>${room.room_location}</td>
                    <td>৳${room.room_rent}</td>
                    <td>${room.available_from}</td>
                    <td><span class="status-badge ${statusClass}">${room.status}</span></td>
                    <td>
                        <button class="btn btn-secondary" onclick="toggleRoomStatus('${room.room_id}', '${room.status}')" style="padding: 6px 12px; margin-right: 5px;">
                            <i class="fas fa-sync"></i>
                        </button>
                        <button class="btn btn-danger" onclick="deleteRoom('${room.room_id}')" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';

            // Add pagination
            if (data.totalPages > 1) {
                html += '<div class="pagination">';
                html += `<button onclick="loadRooms(${page - 1}, '${search}', '${status}')" ${page === 1 ? 'disabled' : ''}>Previous</button>`;
                for (let i = 1; i <= data.totalPages; i++) {
                    html += `<button onclick="loadRooms(${i}, '${search}', '${status}')" ${i === page ? 'class="active"' : ''}>${i}</button>`;
                }
                html += `<button onclick="loadRooms(${page + 1}, '${search}', '${status}')" ${page === data.totalPages ? 'disabled' : ''}>Next</button>`;
                html += '</div>';
            }

            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h4>Error loading rooms</h4></div>';
    }
}

// Load Products
async function loadProducts() {
    const container = document.getElementById('productsTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading products...</p></div>';

    try {
        const response = await fetch('api/admin_products.php');
        const data = await response.json();

        if (data.success) {
            let html = '<table><thead><tr>';
            html += '<th>Image</th><th>Product Name</th><th>Category</th><th>Price</th><th>Seller</th><th>Status</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.products.forEach(product => {
                const statusClass = product.status === 'available' ? 'status-available' :
                    product.status === 'sold' ? 'status-not-available' : 'status-pending';
                html += `<tr>
                    <td><img src="${product.image_path}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" alt="${product.product_name}"></td>
                    <td>${product.product_name}</td>
                    <td>${product.category || 'N/A'}</td>
                    <td>৳${product.price}</td>
                    <td>${product.username || 'Unknown'}</td>
                    <td><span class="status-badge ${statusClass}">${product.status}</span></td>
                    <td>
                        <button class="btn btn-danger" onclick="deleteProduct(${product.id})" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading products:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h4>Error loading products</h4></div>';
    }
}

// Load Lost & Found
async function loadLostFound() {
    const container = document.getElementById('lostFoundTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading items...</p></div>';

    try {
        const response = await fetch('api/admin_lostandfound.php?type=items');
        const data = await response.json();

        if (data.success) {
            let html = '<table><thead><tr>';
            html += '<th>Image</th><th>Category</th><th>Found Place</th><th>Date</th><th>Contact</th><th>Status</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.items.forEach(item => {
                const statusClass = item.claim_status == 1 ? 'status-not-available' : 'status-available';
                const statusText = item.claim_status == 1 ? 'Claimed' : 'Unclaimed';
                const date = new Date(item.date_time).toLocaleDateString();
                html += `<tr>
                    <td><img src="${item.image_path}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" alt="${item.category}"></td>
                    <td>${item.category}</td>
                    <td>${item.foundPlace}</td>
                    <td>${date}</td>
                    <td>${item.contact_info}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td>
                        ${item.claim_status == 0 ? `<button class="btn btn-success" onclick="resolveItem(${item.id})" style="padding: 6px 12px; margin-right: 5px;">
                            <i class="fas fa-check"></i> Resolve
                        </button>` : ''}
                        <button class="btn btn-danger" onclick="deleteLostItem(${item.id})" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading lost & found:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h4>Error loading items</h4></div>';
    }
}

// Load Jobs
async function loadJobs() {
    const container = document.getElementById('jobsTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading jobs...</p></div>';

    try {
        const response = await fetch('api/jobs.php?all_jobs=1');
        const data = await response.json();

        if (data.success && data.jobs.length > 0) {
            let html = '<table><thead><tr>';
            html += '<th>Title</th><th>Company</th><th>Location</th><th>Category</th><th>Type</th><th>Posted By</th><th>Status</th><th>Applications</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.jobs.forEach(job => {
                const statusClass = job.status === 'active' ? 'status-available' : 'status-not-available';
                const postedDate = new Date(job.created_at).toLocaleDateString();
                html += `<tr>
                    <td>${job.title}</td>
                    <td>${job.company || 'N/A'}</td>
                    <td>${job.location}</td>
                    <td>${job.category}</td>
                    <td>${job.job_type}</td>
                    <td>${job.poster_name || 'Admin'} (${job.poster_type || 'admin'})</td>
                    <td><span class="status-badge ${statusClass}">${job.status}</span></td>
                    <td>${job.application_count || 0}</td>
                    <td>
                        <button class="btn btn-secondary" onclick="toggleJobStatus(${job.id}, '${job.status}')" style="padding: 6px 12px; margin-right: 5px;">
                            <i class="fas fa-sync"></i>
                        </button>
                        <button class="btn btn-danger" onclick="deleteJob(${job.id})" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-briefcase"></i><h4>No Jobs Posted</h4><p>There are currently no job listings.</p></div>';
        }
    } catch (error) {
        console.error('Error loading jobs:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h4>Error loading jobs</h4></div>';
    }
}

// Toggle Job Status
async function toggleJobStatus(jobId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'closed' : 'active';

    try {
        const response = await fetch('api/jobs.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: jobId,
                status: newStatus
            })
        });
        const data = await response.json();

        if (data.success) {
            showToast('Job status updated', 'success');
            loadJobs();
        } else {
            showToast(data.error || 'Failed to update job', 'error');
        }
    } catch (error) {
        console.error('Error updating job:', error);
        showToast('Failed to update job', 'error');
    }
}

// Delete Job
async function deleteJob(jobId) {
    if (!confirm('Are you sure you want to delete this job?')) {
        return;
    }

    try {
        const response = await fetch(`api/jobs.php?id=${jobId}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.success) {
            showToast('Job deleted successfully', 'success');
            loadJobs();
        } else {
            showToast(data.error || 'Failed to delete job', 'error');
        }
    } catch (error) {
        console.error('Error deleting job:', error);
        showToast('Failed to delete job', 'error');
    }
}

// Load Drivers
async function loadDrivers() {
    const container = document.getElementById('driversTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading drivers...</p></div>';

    try {
        const response = await fetch('api/admin_shuttle.php');
        const data = await response.json();

        if (data.success) {
            let html = '<table><thead><tr>';
            html += '<th>Driver ID</th><th>Name</th><th>Contact Number</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.drivers.forEach(driver => {
                html += `<tr>
                    <td>${driver.d_id}</td>
                    <td>${driver.d_name}</td>
                    <td>${driver.d_contactNo}</td>
                    <td>
                        <button class="btn btn-danger" onclick="deleteDriver('${driver.d_id}')" style="padding: 6px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading drivers:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h4>Error loading drivers</h4></div>';
    }
}

// Load Sessions
async function loadSessions() {
    const container = document.getElementById('sessionsTableContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading sessions...</p></div>';

    try {
        const response = await fetch('api/admin_sessions.php');
        const data = await response.json();

        if (data.success && data.sessions.length > 0) {
            let html = '<table><thead><tr>';
            html += '<th>Session ID</th><th>Student</th><th>Mentor</th><th>Date</th><th>Time</th><th>Method</th><th>Status</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            data.sessions.forEach(session => {
                const statusClass = session.status === 'Pending' ? 'status-pending' :
                    session.status === 'Approved' ? 'status-approved' : 'status-not-available';
                html += `<tr>
                    <td>${session.session_id}</td>
                    <td>${session.user_name || 'N/A'}</td>
                    <td>${session.mentor_name || 'N/A'}</td>
                    <td>${session.session_date}</td>
                    <td>${session.session_time}</td>
                    <td>${session.communication_method}</td>
                    <td><span class="status-badge ${statusClass}">${session.status}</span></td>
                    <td>
                        ${session.status === 'Pending' ? `
                            <button class="btn btn-success" onclick="updateSessionStatus(${session.session_id}, 'Approved')" style="padding: 6px 12px; margin-right: 5px;">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-danger" onclick="updateSessionStatus(${session.session_id}, 'Rejected')" style="padding: 6px 12px;">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        ` : '<span class="status-badge">' + session.status + '</span>'}
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-calendar-check"></i><h4>No Session Requests</h4><p>There are currently no mentorship session requests.</p></div>';
        }
    } catch (error) {
        console.error('Error loading sessions:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-calendar-check"></i><h4>No Session Requests</h4><p>There are currently no mentorship session requests.</p></div>';
    }
}

// Load Analytics
async function loadAnalytics() {
    try {
        const response = await fetch('api/admin_analytics.php?type=overview');
        const data = await response.json();

        if (data.success) {
            document.getElementById('totalProducts').textContent = data.stats.total_products || 0;
            document.getElementById('totalLostItems').textContent = data.stats.total_lost_items || 0;
            document.getElementById('totalDrivers').textContent = data.stats.total_drivers || 0;
            document.getElementById('rentedRooms').textContent = data.stats.rented_rooms || 0;
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}

// CRUD Operations

// Edit User
function editUser(user) {
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUserName').value = user.username;
    document.getElementById('editUserEmail').value = user.email;
    document.getElementById('editUserMobile').value = user.mobilenumber;
    openModal('editUserModal');
}

// Delete User
async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`api/admin_users.php?id=${userId}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.success) {
            showToast('User deleted successfully', 'success');
            loadUsers();
        } else {
            showToast(data.error || 'Failed to delete user', 'error');
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        showToast('Failed to delete user', 'error');
    }
}

// Delete Mentor
async function deleteMentor(mentorId) {
    if (!confirm('Are you sure you want to delete this mentor? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`api/admin_mentors.php?id=${mentorId}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.success) {
            showToast('Mentor deleted successfully', 'success');
            loadMentors();
        } else {
            showToast(data.error || 'Failed to delete mentor', 'error');
        }
    } catch (error) {
        console.error('Error deleting mentor:', error);
        showToast('Failed to delete mentor', 'error');
    }
}

// Toggle Room Status
async function toggleRoomStatus(roomId, currentStatus) {
    const newStatus = currentStatus === 'available' ? 'not-available' : 'available';

    try {
        const response = await fetch('api/admin_rooms.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_status',
                room_id: roomId,
                status: newStatus
            })
        });
        const data = await response.json();

        if (data.success) {
            showToast('Room status updated', 'success');
            loadRooms();
        } else {
            showToast(data.error || 'Failed to update room', 'error');
        }
    } catch (error) {
        console.error('Error updating room:', error);
        showToast('Failed to update room', 'error');
    }
}

// Delete Room
async function deleteRoom(roomId) {
    if (!confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`api/admin_rooms.php?room_id=${roomId}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.success) {
            showToast('Room deleted successfully', 'success');
            loadRooms();
        } else {
            showToast(data.error || 'Failed to delete room', 'error');
        }
    } catch (error) {
        console.error('Error deleting room:', error);
        showToast('Failed to delete room', 'error');
    }
}

// Delete Product
async function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }

    try {
        const response = await fetch(`api/admin_products.php?id=${productId}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.success) {
            showToast('Product deleted successfully', 'success');
            loadProducts();
        } else {
            showToast(data.error || 'Failed to delete product', 'error');
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        showToast('Failed to delete product', 'error');
    }
}

// Resolve Lost Item
async function resolveItem(itemId) {
    try {
        const response = await fetch('api/admin_lostandfound.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'resolve',
                id: itemId
            })
        });
        const data = await response.json();

        if (data.success) {
            showToast('Item marked as resolved', 'success');
            loadLostFound();
        } else {
            showToast(data.error || 'Failed to update item', 'error');
        }
    } catch (error) {
        console.error('Error resolving item:', error);
        showToast('Failed to resolve item', 'error');
    }
}

// Delete Lost Item
async function deleteLostItem(itemId) {
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }

    try {
        const response = await fetch(`api/admin_lostandfound.php?id=${itemId}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.success) {
            showToast('Item deleted successfully', 'success');
            loadLostFound();
        } else {
            showToast(data.error || 'Failed to delete item', 'error');
        }
    } catch (error) {
        console.error('Error deleting item:', error);
        showToast('Failed to delete item', 'error');
    }
}

// Delete Driver
async function deleteDriver(driverId) {
    if (!confirm('Are you sure you want to delete this driver?')) {
        return;
    }

    try {
        const response = await fetch(`api/admin_shuttle.php?d_id=${driverId}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.success) {
            showToast('Driver deleted successfully', 'success');
            loadDrivers();
        } else {
            showToast(data.error || 'Failed to delete driver', 'error');
        }
    } catch (error) {
        console.error('Error deleting driver:', error);
        showToast('Failed to delete driver', 'error');
    }
}

// Event Listeners
function setupEventListeners() {
    // User search
    let userSearchTimeout;
    document.getElementById('userSearch').addEventListener('input', function (e) {
        clearTimeout(userSearchTimeout);
        userSearchTimeout = setTimeout(() => {
            loadUsers(1, e.target.value);
        }, 500);
    });

    // Mentor search
    let mentorSearchTimeout;
    document.getElementById('mentorSearch').addEventListener('input', function (e) {
        clearTimeout(mentorSearchTimeout);
        mentorSearchTimeout = setTimeout(() => {
            loadMentors(1, e.target.value);
        }, 500);
    });

    // Room search and filter
    let roomSearchTimeout;
    document.getElementById('roomSearch').addEventListener('input', function (e) {
        clearTimeout(roomSearchTimeout);
        roomSearchTimeout = setTimeout(() => {
            const status = document.getElementById('roomStatusFilter').value;
            loadRooms(1, e.target.value, status);
        }, 500);
    });

    document.getElementById('roomStatusFilter').addEventListener('change', function (e) {
        const search = document.getElementById('roomSearch').value;
        loadRooms(1, search, e.target.value);
    });

    // Edit user form
    document.getElementById('editUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const userData = {
            id: document.getElementById('editUserId').value,
            username: document.getElementById('editUserName').value,
            email: document.getElementById('editUserEmail').value,
            mobilenumber: document.getElementById('editUserMobile').value
        };

        try {
            const response = await fetch('api/admin_users.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
            const data = await response.json();

            if (data.success) {
                showToast('User updated successfully', 'success');
                closeModal('editUserModal');
                loadUsers();
            } else {
                showToast(data.error || 'Failed to update user', 'error');
            }
        } catch (error) {
            console.error('Error updating user:', error);
            showToast('Failed to update user', 'error');
        }
    });

    // Add driver form
    document.getElementById('addDriverForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const driverData = {
            d_id: document.getElementById('newDriverId').value,
            d_name: document.getElementById('newDriverName').value,
            d_contactNo: document.getElementById('newDriverContact').value,
            d_password: document.getElementById('newDriverPassword').value
        };

        try {
            const response = await fetch('api/admin_shuttle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(driverData)
            });
            const data = await response.json();

            if (data.success) {
                showToast('Driver added successfully', 'success');
                closeModal('addDriverModal');
                document.getElementById('addDriverForm').reset();
                loadDrivers();
            } else {
                showToast(data.error || 'Failed to add driver', 'error');
            }
        } catch (error) {
            console.error('Error adding driver:', error);
            showToast('Failed to add driver', 'error');
        }
    });
}

// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function openAddDriverModal() {
    openModal('addDriverModal');
}

// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} toast-icon"></i>
        <div class="toast-message">${message}</div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Update Session Status
async function updateSessionStatus(sessionId, status) {
    try {
        const response = await fetch('api/admin_sessions.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                session_id: sessionId,
                status: status
            })
        });
        const data = await response.json();

        if (data.success) {
            showToast(`Session ${status.toLowerCase()} successfully`, 'success');
            loadSessions();
        } else {
            showToast(data.error || 'Failed to update session', 'error');
        }
    } catch (error) {
        console.error('Error updating session:', error);
        showToast('Failed to update session', 'error');
    }
}

// Close modal when clicking outside
window.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
    // Close notification dropdown when clicking outside
    const dropdown = document.getElementById('notificationDropdown');
    const notificationIcon = document.getElementById('notificationIcon');
    if (dropdown && notificationIcon && !dropdown.contains(e.target) && !notificationIcon.contains(e.target)) {
        dropdown.classList.remove('active');
    }
});

// Notification Functions
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('active');
}

async function loadNotifications() {
    try {
        const response = await fetch('api/admin_analytics.php?type=overview');
        const data = await response.json();

        if (data.success) {
            const notifications = [];

            // Generate notifications based on pending items
            if (data.stats.pending_sessions > 0) {
                notifications.push({
                    type: 'session',
                    icon: 'fa-calendar-check',
                    message: `${data.stats.pending_sessions} pending mentorship session${data.stats.pending_sessions > 1 ? 's' : ''} awaiting approval`,
                    time: 'Just now',
                    unread: true
                });
            }

            if (data.stats.pending_claims > 0) {
                notifications.push({
                    type: 'claim',
                    icon: 'fa-search',
                    message: `${data.stats.pending_claims} unclaimed lost & found item${data.stats.pending_claims > 1 ? 's' : ''}`,
                    time: 'Recent',
                    unread: true
                });
            }

            // Add recent activity notifications
            if (data.recent_activity && data.recent_activity.length > 0) {
                data.recent_activity.slice(0, 3).forEach(activity => {
                    notifications.push({
                        type: 'user',
                        icon: 'fa-user',
                        message: activity.description || `${activity.action_type} on ${activity.target_type}`,
                        time: formatTimeAgo(new Date(activity.created_at)),
                        unread: false
                    });
                });
            }

            // Update badge count
            const unreadCount = notifications.filter(n => n.unread).length;
            const badge = document.getElementById('notificationBadge');
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'block' : 'none';

            // Render notifications
            renderNotifications(notifications);
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

function renderNotifications(notifications) {
    const container = document.getElementById('notificationList');

    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications</p>
            </div>
        `;
        return;
    }

    let html = '';
    notifications.forEach(notification => {
        html += `
            <div class="notification-item ${notification.unread ? 'unread' : ''}">
                <div class="notification-icon-small ${notification.type}">
                    <i class="fas ${notification.icon}"></i>
                </div>
                <div class="notification-content">
                    <p>${notification.message}</p>
                    <span>${notification.time}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function markAllAsRead() {
    const items = document.querySelectorAll('.notification-item.unread');
    items.forEach(item => item.classList.remove('unread'));

    const badge = document.getElementById('notificationBadge');
    badge.textContent = '0';
    badge.style.display = 'none';

    showToast('All notifications marked as read', 'success');
}

function formatTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
}
