<?php
session_start();
header('Content-Type: application/json');

// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

$method = $_SERVER['REQUEST_METHOD'];

// GET - Get applications for a job (for job posters)
if ($method === 'GET') {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    
    $jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
    
    if ($jobId > 0) {
        // Check if user owns this job or is admin
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
        
        $isOwner = (isset($_SESSION['user_id']) && $job['posted_by_user_id'] == $_SESSION['user_id']) ||
                   isset($_SESSION['admin_id']);
        
        if (!$isOwner) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Not authorized']);
            exit();
        }
        
        // Get applications with user info
        $sql = "SELECT ja.*, u.username, u.email, u.mobilenumber,
                       up.user_photo
                FROM job_applications ja
                JOIN users u ON ja.user_id = u.id
                LEFT JOIN user_profiles up ON ja.user_id = up.user_id
                WHERE ja.job_id = ?
                ORDER BY ja.applied_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        $applications = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'applications' => $applications]);
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Job ID required']);
    }
}

// POST - Apply for a job
elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    
    // Handle FormData (file upload) instead of JSON
    $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $coverLetter = isset($_POST['cover_letter']) ? $conn->real_escape_string($_POST['cover_letter']) : '';
    $userId = $_SESSION['user_id'];
    
    // Check if job exists and is active, get job details for email
    $checkJob = $conn->prepare("SELECT j.id, j.title, j.posted_by_user_id, j.contact_email,
                                       COALESCE(u.email, a.admin_id) as poster_email,
                                       COALESCE(u.username, a.admin_name) as poster_name
                                FROM jobs j
                                LEFT JOIN users u ON j.posted_by_user_id = u.id
                                LEFT JOIN admins a ON j.posted_by_admin_id = a.admin_id
                                WHERE j.id = ? AND j.status = 'active'");
    $checkJob->bind_param("i", $jobId);
    $checkJob->execute();
    $jobResult = $checkJob->get_result();
    
    if ($jobResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Job not found or no longer active']);
        exit();
    }
    
    $job = $jobResult->fetch_assoc();
    $checkJob->close();
    
    // Can't apply to your own job
    if ($job['posted_by_user_id'] == $userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot apply to your own job posting']);
        exit();
    }
    
    // Check if already applied
    $checkApp = $conn->prepare("SELECT id FROM job_applications WHERE job_id = ? AND user_id = ?");
    $checkApp->bind_param("ii", $jobId, $userId);
    $checkApp->execute();
    
    if ($checkApp->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'You have already applied to this job']);
        exit();
    }
    $checkApp->close();
    
    // Handle CV file upload
    $cvPath = null;
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cv_file'];
        
        // Validate file type
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        
        if (!in_array($extension, $allowedExtensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only PDF, DOC, DOCX allowed.']);
            exit();
        }
        
        // Validate file size (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File size must be less than 5MB']);
            exit();
        }
        
        // Generate unique filename
        $uploadDir = '../uploads/cv/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $newFilename = 'cv_' . $userId . '_' . $jobId . '_' . time() . '.' . $extension;
        $cvPath = 'uploads/cv/' . $newFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to upload CV file']);
            exit();
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'CV file is required']);
        exit();
    }
    
    // Get applicant info for email
    $userStmt = $conn->prepare("SELECT username, email, mobilenumber FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $applicant = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();
    
    // Insert application
    $stmt = $conn->prepare("INSERT INTO job_applications (job_id, user_id, cover_letter, cv_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $jobId, $userId, $coverLetter, $cvPath);
    
    if ($stmt->execute()) {
        // Determine recipient email - use contact_email if available, otherwise poster's email
        $recipientEmail = !empty($job['contact_email']) ? $job['contact_email'] : $job['poster_email'];
        
        // Send email to job poster with CV attachment
        if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $jobTitle = $job['title'];
            $applicantName = $applicant['username'];
            $applicantEmail = $applicant['email'];
            $applicantPhone = $applicant['mobilenumber'];
            
            // Email subject
            $subject = "New Job Application: " . $jobTitle . " - " . $applicantName;
            
            // Email body
            $body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .header { background: linear-gradient(135deg, #FF3300, #dc2626); color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .info-box { background: #f9fafb; border-radius: 8px; padding: 15px; margin: 15px 0; }
                    .label { font-weight: bold; color: #374151; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h2>New Job Application Received!</h2>
                </div>
                <div class='content'>
                    <p>Hello,</p>
                    <p>A new candidate has applied for your job posting: <strong>{$jobTitle}</strong></p>
                    
                    <div class='info-box'>
                        <p><span class='label'>Applicant Name:</span> {$applicantName}</p>
                        <p><span class='label'>Email:</span> {$applicantEmail}</p>
                        <p><span class='label'>Phone:</span> {$applicantPhone}</p>
                    </div>
                    
                    " . ($coverLetter ? "
                    <div class='info-box'>
                        <p class='label'>Cover Letter:</p>
                        <p>" . nl2br(htmlspecialchars($coverLetter)) . "</p>
                    </div>
                    " : "") . "
                    
                    <p><strong>The applicant's CV is attached to this email.</strong></p>
                    
                    <p>Best regards,<br>UIU Supplements Team</p>
                </div>
            </body>
            </html>
            ";
            
            // Email headers with attachment
            $boundary = md5(time());
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "From: UIU Supplements <noreply@uiusupplements.com>\r\n";
            $headers .= "Reply-To: {$applicantEmail}\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
            
            // Prepare message with attachment
            $message = "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $body . "\r\n\r\n";
            
            // Attach CV file
            $cvFullPath = '../' . $cvPath;
            if (file_exists($cvFullPath)) {
                $fileContent = file_get_contents($cvFullPath);
                $fileContent = chunk_split(base64_encode($fileContent));
                $fileName = basename($cvPath);
                
                $message .= "--{$boundary}\r\n";
                $message .= "Content-Type: application/octet-stream; name=\"{$fileName}\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n";
                $message .= "Content-Disposition: attachment; filename=\"{$fileName}\"\r\n\r\n";
                $message .= $fileContent . "\r\n\r\n";
            }
            
            $message .= "--{$boundary}--";
            
            // Send email
            @mail($recipientEmail, $subject, $message, $headers);
        }
        
        echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
    } else {
        // Delete uploaded file on failure
        if ($cvPath && file_exists('../' . $cvPath)) {
            unlink('../' . $cvPath);
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to submit application']);
    }
    $stmt->close();
}

// PUT - Update application status (for job posters)
elseif ($method === 'PUT') {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $applicationId = isset($data['application_id']) ? (int)$data['application_id'] : 0;
    $status = isset($data['status']) ? $data['status'] : '';
    
    // Validate status
    $validStatuses = ['pending', 'reviewed', 'accepted', 'rejected'];
    if (!in_array($status, $validStatuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit();
    }
    
    // Get application and check ownership
    $checkSql = "SELECT ja.job_id, j.posted_by_user_id, j.posted_by_admin_id 
                 FROM job_applications ja 
                 JOIN jobs j ON ja.job_id = j.id 
                 WHERE ja.id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $applicationId);
    $checkStmt->execute();
    $appResult = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
    
    if (!$appResult) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Application not found']);
        exit();
    }
    
    $isOwner = (isset($_SESSION['user_id']) && $appResult['posted_by_user_id'] == $_SESSION['user_id']) ||
               isset($_SESSION['admin_id']);
    
    if (!$isOwner) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE job_applications SET status = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $applicationId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Application status updated']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update application']);
    }
    $stmt->close();
}

// DELETE - Withdraw application
elseif ($method === 'DELETE') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    
    $applicationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $userId = $_SESSION['user_id'];
    
    // Only allow withdrawing own applications
    $stmt = $conn->prepare("DELETE FROM job_applications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $applicationId, $userId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Application withdrawn']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Application not found or not authorized']);
    }
    $stmt->close();
}

$conn->close();
