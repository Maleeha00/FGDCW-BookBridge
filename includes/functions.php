<?php

function checkUserRole($requiredRole): void {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../index.php');
        exit();
    }
    
    if ($_SESSION['role'] != $requiredRole) {
        header('Location: ../index.php');
        exit();
    }
}


function getTotalBooks($conn) {
    $sql = "SELECT SUM(total_quantity) as total FROM books";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();                  
    return $row['total'] ? $row['total'] : 0;      
}


function getIssuedBooks($conn) {
    $sql = "SELECT COUNT(*) as total FROM issued_books WHERE status = 'issued' OR status = 'overdue'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}

function getTotalUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM users WHERE role != 'librarian'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;}


function getPendingRequests($conn) { 
    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM reservation_requests WHERE status = 'pending') +
            (SELECT COUNT(*) FROM book_requests WHERE status = 'pending') as total
    ";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}


function getTotalUnpaidFines($conn) {
    $sql = "SELECT SUM(amount) as total FROM fines WHERE status = 'pending'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}


function generateDueDate($days = 7) {
    $date = new DateTime();
    $date->add(new DateInterval("P{$days}D"));
    return $date->format('Y-m-d');            
}
function uploadFile($file, $targetDir = '../uploads/ebooks/') {
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = basename($file['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    
    $fileName = uniqid() . '_' . $fileName;
    $targetFilePath = $targetDir . $fileName;
    
    
    $allowedTypes = array('pdf', 'doc', 'docx', 'epub');
    if (!in_array(strtolower($fileType), $allowedTypes)) {
        return array('success' => false, 'message' => 'Only PDF, DOC, DOCX & EPUB files are allowed.');
    }
    
    
    if ($file['size'] > 50 * 1024 * 1024) {
        return array('success' => false, 'message' => 'File size should be less than 50MB.');
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return array(
            'success' => true,
            'file_path' => $targetFilePath,
            'file_name' => $fileName,
            'file_size' => formatFileSize($file['size']),
            'file_type' => $fileType
        );
    } else {
        return array('success' => false, 'message' => 'There was an error uploading your file.');
    }
}


function formatFileSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($size >= 1024 && $i < 4) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}


function sendNotification($conn, $userId, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $message);
    return $stmt->execute();
}


function formatDate($date) {
    return date('F j, Y', strtotime($date));
}


function canIssueBook($conn, $bookId) {
    $stmt = $conn->prepare("SELECT available_quantity FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $book = $result->fetch_assoc();
        return $book['available_quantity'] > 0;
    }
    
    return false;
}


function updateBookAvailability($conn, $bookId, $action = 'issue') {
    if ($action == 'issue') {
        $sql = "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ? AND available_quantity > 0";
    } else {
        $sql = "UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);
    return $stmt->execute();
}


function getUserName($conn, $userId) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        return $user['name'];
    }
    
    return 'Unknown User';
}


function getBookbook_name($conn, $bookId) {
    $stmt = $conn->prepare("SELECT book_name FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $book = $result->fetch_assoc();
        return $book['book_name'];
    }
    
    return 'Unknown Book';
}
function createReservationRequest($conn, $bookId, $userId, $notes = '') {
   
    $stmt = $conn->prepare("
        SELECT id FROM book_requests 
        WHERE book_id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->bind_param("ii", $bookId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return array('success' => false, 'message' => 'You already have a pending reservation request for this book.');
    }

    // Check if user already has an active reservation for this book
    $stmt = $conn->prepare("
        SELECT id FROM fulfilled_requests 
        WHERE book_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->bind_param("ii", $bookId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return array('success' => false, 'message' => 'You already have an active request for this book.');
    }
    
    // Create reservation request
    $stmt = $conn->prepare("
        INSERT INTO book_requests (book_id, user_id, notes)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $bookId, $userId, $notes);
    
    if ($stmt->execute()) {
        $bookbook_name = getBookbook_name($conn, $bookId);
        $message = "Your reservation request for '{$bookbook_name}' has been submitted and is pending approval.";
        sendNotification($conn, $userId, $message);
        
        return array(
            'success' => true, 
            'message' => "Book request submitted successfully! You will be notified once it's reviewed."
        );
    } else {
        return array('success' => false, 'message' => 'Error creating reservation request.');
    }
}


function approveReservationRequest($conn, $requestId) {
    
    $stmt = $conn->prepare("
        SELECT rr.*, b.book_name, u.name as user_name 
        FROM book_requests rr
        JOIN books b ON rr.book_id = b.id
        JOIN users u ON rr.user_id = u.id
        WHERE rr.id = ? AND rr.status = 'pending'
    ");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return array('success' => false, 'message' => 'Reservation request not found or already processed.');
    }
    
    $request = $result->fetch_assoc();
    
    
    $conn->begin_transaction();
    
    try {
        
        $stmt = $conn->prepare("UPDATE book_requests SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        
        
        $reservationResult = createBookReservation($conn, $request['book_id'], $request['user_id'], $request['notes']);
        
        if (!$reservationResult['success']) {
            throw new Exception($reservationResult['message']);
        }
        
        
        $message = "Great news! Your reservation request for '{$request['book_name']}' has been approved. " . $reservationResult['message'];
        sendNotification($conn, $request['user_id'], $message);
        
        $conn->commit();
        
        return array(
            'success' => true,
            'message' => "Reservation request approved successfully. {$request['user_name']} has been notified."
        );
        
    } catch (Exception $e) {
        $conn->rollback();
        return array('success' => false, 'message' => 'Error approving reservation request: ' . $e->getMessage());
    }
}

function rejectReservationRequest($conn, $requestId) {
    
    $stmt = $conn->prepare("
        SELECT rr.*, b.book_name, u.name as user_name 
        FROM book_requests rr
        JOIN books b ON rr.book_id = b.id
        JOIN users u ON rr.user_id = u.id
        WHERE rr.id = ? AND rr.status = 'pending'
    ");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return array('success' => false, 'message' => 'Reservation request not found or already processed.');
    }
    
    $request = $result->fetch_assoc();
    
    
    $stmt = $conn->prepare("UPDATE book_requests SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    
    if ($stmt->execute()) {
        
        $message = "Your reservation request for '{$request['book_name']}' has been rejected. Please contact the librarian for more information.";
        sendNotification($conn, $request['user_id'], $message);
        
        return array(
            'success' => true,
            'message' => "Reservation request rejected. {$request['user_name']} has been notified."
        );
    } else {
        return array('success' => false, 'message' => 'Error rejecting reservation request.');
    }
}




function createBookReservation($conn, $bookId, $userId, $notes = '') {
    
    $stmt = $conn->prepare("
        SELECT id FROM fulfilled_requests 
        WHERE book_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->bind_param("ii", $bookId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return array('success' => false, 'message' => 'You already have an active request for this book.');
    }
    
    
    $stmt = $conn->prepare("
        SELECT COALESCE(MAX(priority_number), 0) + 1 as next_priority 
        FROM fulfilled_requests 
        WHERE book_id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $priorityNumber = $row['next_priority'];
    
    
    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    
    $stmt = $conn->prepare("
        INSERT INTO fulfilled_requests (book_id, user_id, priority_number, expires_at, notes)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiss", $bookId, $userId, $priorityNumber, $expiresAt, $notes);
    
    if ($stmt->execute()) {
        return array(
            'success' => true, 
            'message' => "Book reserved successfully! You are #$priorityNumber in the queue.",
            'priority' => $priorityNumber
        );
    } else {
        return array('success' => false, 'message' => 'Error creating reservation.');
    }
}


function cancelBookReservation($conn, $reservationId, $userId) {
    $stmt = $conn->prepare("
        UPDATE fulfilled_requests 
        SET status = 'cancelled' 
        WHERE id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->bind_param("ii", $reservationId, $userId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        
        reorderReservationPriorities($conn, $reservationId);
        return array('success' => true, 'message' => 'Reservation cancelled successfully.');
    } else {
        return array('success' => false, 'message' => 'Reservation not found or cannot be cancelled.');
    }
}


function reorderReservationPriorities($conn, $cancelledReservationId) {
    
    $stmt = $conn->prepare("
        SELECT book_id, priority_number 
        FROM fulfilled_requests 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $cancelledReservationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $cancelled = $result->fetch_assoc();
        
        
        $stmt = $conn->prepare("
            UPDATE fulfilled_requests 
            SET priority_number = priority_number - 1 
            WHERE book_id = ? AND priority_number > ? AND status = 'active'
        ");
        $stmt->bind_param("ii", $cancelled['book_id'], $cancelled['priority_number']);
        $stmt->execute();
    }
}

function processBookReservations($conn, $bookId) {
    
    $stmt = $conn->prepare("
        SELECT r.*, u.name as user_name, u.email as user_email, b.book_name as book_book_name
        FROM fulfilled_requests r
        JOIN users u ON r.user_id = u.id
        JOIN books b ON r.book_id = b.id
        WHERE r.book_id = ? AND r.status = 'active'
        ORDER BY r.priority_number ASC
        LIMIT 1
    ");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $reservation = $result->fetch_assoc();
        
        
        $conn->begin_transaction();
        
        try {
            
            $stmt = $conn->prepare("
                UPDATE fulfilled_requests 
                SET status = 'fulfilled', notified_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $reservation['id']);
            $stmt->execute();
            
            
            $returnDate = generateDueDate(14); 
            
            $stmt = $conn->prepare("
                INSERT INTO issued_books (book_id, user_id, return_date)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iis", $bookId, $reservation['user_id'], $returnDate);
            $stmt->execute();
            
            
            updateBookAvailability($conn, $bookId, 'issue');
            
            
            $message = "Great news! The book '{$reservation['book_book_name']}' you reserved has been automatically issued to you. Due date: " . date('F j, Y', strtotime($returnDate)) . ". Please collect it from the library.";
            sendNotification($conn, $reservation['user_id'], $message);
            
            
            reorderReservationPriorities($conn, $reservation['id']);
            
            $conn->commit();
            
            return array(
                'success' => true,
                'user_notified' => $reservation['user_name'],
                'message' => "Book automatically issued to {$reservation['user_name']} (reserved user). They have been notified.",
                'auto_issued' => true
            );
            
        } catch (Exception $e) {
            $conn->rollback();
            
            
            $stmt = $conn->prepare("
                UPDATE fulfilled_requests 
                SET status = 'fulfilled', notified_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $reservation['id']);
            $stmt->execute();
            
            $message = "Good news! The book '{$reservation['book_book_name']}' you reserved is now available. Please visit the library within 24 hours to collect it, or your reservation will be cancelled.";
            sendNotification($conn, $reservation['user_id'], $message);
            
            reorderReservationPriorities($conn, $reservation['id']);
            
            return array(
                'success' => true,
                'user_notified' => $reservation['user_name'],
                'message' => "Book reserved for {$reservation['user_name']}. They have been notified. (Auto-issue failed: " . $e->getMessage() . ")",
                'auto_issued' => false
            );
        }
    }
    
    return array('success' => false, 'message' => 'No active reservations found.');
}

function getUserReservations($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT r.*, b.book_name, b.author, b.available_quantity
        FROM fulfilled_requests r
        JOIN books b ON r.book_id = b.id
        WHERE r.user_id = ?
        ORDER BY r.status ASC, r.priority_number ASC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reservations = array();
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    
    return $reservations;
}


function getBookReservationQueue($conn, $bookId) {
    $stmt = $conn->prepare("
        SELECT r.*, u.name as user_name, u.email as user_email
        FROM fulfilled_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.book_id = ? AND r.status = 'active'
        ORDER BY r.priority_number ASC
    ");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $queue = array();
    while ($row = $result->fetch_assoc()) {
        $queue[] = $row;
    }
    
    return $queue;
}


function cleanExpiredReservations($conn) {
    
    $stmt = $conn->prepare("
        SELECT id, book_id, user_id, priority_number
        FROM fulfilled_requests 
        WHERE status = 'active' AND expires_at < NOW()
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $expiredReservations = array();
    while ($row = $result->fetch_assoc()) {
        $expiredReservations[] = $row;
    }
    
    
    foreach ($expiredReservations as $reservation) {
        $stmt = $conn->prepare("
            UPDATE fulfilled_requests 
            SET status = 'expired' 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $reservation['id']);
        $stmt->execute();
        
        
        reorderReservationPriorities($conn, $reservation['id']);
        
        
        $bookbook_name = getBookbook_name($conn, $reservation['book_id']);
        $message = "Your reservation for '{$bookbook_name}' has expired. You can create a new reservation if the book is still unavailable.";
        sendNotification($conn, $reservation['user_id'], $message);
    }
    
    return count($expiredReservations);
}

function autoProcessReservationsOnAvailability($conn, $bookId) {
   
    $stmt = $conn->prepare("
        SELECT COUNT(*) as reservation_count 
        FROM fulfilled_requests 
        WHERE book_id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['reservation_count'] > 0) {
        
        $stmt = $conn->prepare("SELECT available_quantity FROM books WHERE id = ?");
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        
        $availableCopies = $book['available_quantity'];
        $processedReservations = 0;
        
        
        while ($availableCopies > 0 && $processedReservations < $row['reservation_count']) {
            $reservationResult = processBookReservations($conn, $bookId);
            
            if ($reservationResult['success']) {
                $processedReservations++;
                if ($reservationResult['auto_issued']) {
                    $availableCopies--; 
                }
            } else {
                break;
            }
        }
        
        return array(
            'success' => true,
            'processed_count' => $processedReservations,
            'message' => "Processed $processedReservations reservation(s) automatically."
        );
    }
    
    return array('success' => false, 'message' => 'No reservations to process.');
}
    ?>