<?php
session_start();
header('Content-Type: application/json');

// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch jobs
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $myJobs = isset($_GET['my_jobs']) && isset($_SESSION['user_id']);
    $myApplications = isset($_GET['my_applications']) && isset($_SESSION['user_id']);
    
    if ($id) {
        // Get single job with poster info
        $sql = "SELECT j.*, 
                       COALESCE(u.username, a.admin_name) as poster_name,
                       CASE WHEN j.posted_by_user_id IS NOT NULL THEN 'user' ELSE 'admin' END as poster_type,
                       (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as application_count
                FROM jobs j
                LEFT JOIN users u ON j.posted_by_user_id = u.id
                LEFT JOIN admins a ON j.posted_by_admin_id = a.admin_id
                WHERE j.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result->fetch_assoc();
        
        if ($job) {
            // Check if current user has applied
            if (isset($_SESSION['user_id'])) {
                $appCheck = $conn->prepare("SELECT status FROM job_applications WHERE job_id = ? AND user_id = ?");
                $appCheck->bind_param("ii", $id, $_SESSION['user_id']);
                $appCheck->execute();
                $appResult = $appCheck->get_result();
                $job['user_applied'] = $appResult->num_rows > 0;
                if ($job['user_applied']) {
                    $job['user_application_status'] = $appResult->fetch_assoc()['status'];
                }
                $appCheck->close();
            }
            echo json_encode(['success' => true, 'job' => $job]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Job not found']);
        }
        $stmt->close();
    } elseif ($myJobs) {
        // Get jobs posted by current user
        $userId = $_SESSION['user_id'];
        $sql = "SELECT j.*, 
                       (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as application_count
                FROM jobs j 
                WHERE j.posted_by_user_id = ?
                ORDER BY j.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $jobs = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'jobs' => $jobs]);
        $stmt->close();
    } elseif ($myApplications) {
        // Get applications by current user
        $userId = $_SESSION['user_id'];
        $sql = "SELECT ja.*, j.title, j.company, j.location, j.salary, j.status as job_status,
                       COALESCE(u.username, a.admin_name) as poster_name
                FROM job_applications ja
                JOIN jobs j ON ja.job_id = j.id
                LEFT JOIN users u ON j.posted_by_user_id = u.id
                LEFT JOIN admins a ON j.posted_by_admin_id = a.admin_id
                WHERE ja.user_id = ?
                ORDER BY ja.applied_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $applications = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'applications' => $applications]);
        $stmt->close();
    } else {
        // Get all jobs (or only active for regular users)
        $allJobs = isset($_GET['all_jobs']) && isset($_SESSION['admin_id']);
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        
        $sql = "SELECT j.*, 
                       COALESCE(u.username, a.admin_name) as poster_name,
                       CASE WHEN j.posted_by_user_id IS NOT NULL THEN 'user' ELSE 'admin' END as poster_type,
                       (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as application_count
                FROM jobs j
                LEFT JOIN users u ON j.posted_by_user_id = u.id
                LEFT JOIN admins a ON j.posted_by_admin_id = a.admin_id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Only filter by active status if not admin viewing all jobs
        if (!$allJobs) {
            $sql .= " AND j.status = 'active'";
        }
        
        if ($category) {
            $sql .= " AND j.category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($search) {
            $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR j.company LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        $sql .= " ORDER BY j.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $jobs = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'jobs' => $jobs]);
        $stmt->close();
    }
}

// POST - Create new job
elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $conn->real_escape_string($data['title'] ?? '');
    $description = $conn->real_escape_string($data['description'] ?? '');
    $company = $conn->real_escape_string($data['company'] ?? '');
    $location = $conn->real_escape_string($data['location'] ?? '');
    $jobType = $conn->real_escape_string($data['job_type'] ?? 'part-time');
    $category = $conn->real_escape_string($data['category'] ?? '');
    $salary = $conn->real_escape_string($data['salary'] ?? '');
    $daysPerWeek = isset($data['days_per_week']) ? (int)$data['days_per_week'] : null;
    $requirements = $conn->real_escape_string($data['requirements'] ?? '');
    $contactEmail = $conn->real_escape_string($data['contact_email'] ?? '');
    $contactPhone = $conn->real_escape_string($data['contact_phone'] ?? '');
    $expiresAt = !empty($data['expires_at']) ? $data['expires_at'] : null;
    
    $postedByUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $postedByAdminId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
    
    $sql = "INSERT INTO jobs (title, description, company, location, job_type, category, salary, 
                              days_per_week, requirements, contact_email, contact_phone, 
                              posted_by_user_id, posted_by_admin_id, expires_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssisssiis", $title, $description, $company, $location, $jobType, 
                      $category, $salary, $daysPerWeek, $requirements, $contactEmail, 
                      $contactPhone, $postedByUserId, $postedByAdminId, $expiresAt);
    
    if ($stmt->execute()) {
        $jobId = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'Job posted successfully', 'job_id' => $jobId]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to post job: ' . $stmt->error]);
    }
    $stmt->close();
}

// PUT - Update job
elseif ($method === 'PUT') {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $jobId = isset($data['id']) ? (int)$data['id'] : 0;
    
    // Check ownership
    $checkSql = "SELECT posted_by_user_id, posted_by_admin_id FROM jobs WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $jobId);
    $checkStmt->execute();
    $job = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
    
    if (!$job) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit();
    }
    
    // Check if user owns this job or is admin
    $isOwner = (isset($_SESSION['user_id']) && $job['posted_by_user_id'] == $_SESSION['user_id']) ||
               (isset($_SESSION['admin_id']) && ($job['posted_by_admin_id'] == $_SESSION['admin_id'] || true));
    
    if (!$isOwner) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not authorized to edit this job']);
        exit();
    }
    
    $title = $conn->real_escape_string($data['title'] ?? '');
    $description = $conn->real_escape_string($data['description'] ?? '');
    $company = $conn->real_escape_string($data['company'] ?? '');
    $location = $conn->real_escape_string($data['location'] ?? '');
    $jobType = $conn->real_escape_string($data['job_type'] ?? 'part-time');
    $category = $conn->real_escape_string($data['category'] ?? '');
    $salary = $conn->real_escape_string($data['salary'] ?? '');
    $daysPerWeek = isset($data['days_per_week']) ? (int)$data['days_per_week'] : null;
    $requirements = $conn->real_escape_string($data['requirements'] ?? '');
    $status = $conn->real_escape_string($data['status'] ?? 'active');
    
    $sql = "UPDATE jobs SET title=?, description=?, company=?, location=?, job_type=?, 
            category=?, salary=?, days_per_week=?, requirements=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssisii", $title, $description, $company, $location, $jobType,
                      $category, $salary, $daysPerWeek, $requirements, $status, $jobId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Job updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update job']);
    }
    $stmt->close();
}

// DELETE - Delete job
elseif ($method === 'DELETE') {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    
    $jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Check ownership
    $checkSql = "SELECT posted_by_user_id, posted_by_admin_id FROM jobs WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $jobId);
    $checkStmt->execute();
    $job = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
    
    if (!$job) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit();
    }
    
    // Check if user owns this job or is admin
    $isOwner = (isset($_SESSION['user_id']) && $job['posted_by_user_id'] == $_SESSION['user_id']) ||
               isset($_SESSION['admin_id']);
    
    if (!$isOwner) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not authorized to delete this job']);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $jobId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete job']);
    }
    $stmt->close();
}

$conn->close();
