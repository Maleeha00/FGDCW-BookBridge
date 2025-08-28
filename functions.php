<?php
// Check if user is logged in and has the right role
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

// Get total number of books in library
function getTotalBooks($conn) {
    $sql = "SELECT SUM(total_quantity) as total FROM books";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();                  //fetch_assoc() ka matlab hai result ko ek associative array ki form melena.  
                                                //To ab $row['total'] me wo sum ki value store ho jaayegi jo query se mili hai.        
    return $row['total'] ? $row['total'] : 0;      //Ye ek ternary operator hai.Agar $row['total'] ki value exist karti hai (null ya empty nahi hai) to wahi value return karega.Agar wo null ho (matlab koi record hi nahi hai), to 0 return karega.
}

// Get total number of issued books
function getIssuedBooks($conn) {
    $sql = "SELECT COUNT(*) as total FROM issued_books WHERE status = 'issued' OR status = 'overdue'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}
//Ye PHP ka extension / library hai jo humein MySQL database ke sath connect karne aur queries run karne deta hai.MySQL Improved Extension.

// Get total number of users
function getTotalUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM users WHERE role != 'librarian'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;//ternary operator
}

// Get total number of pending requests (book requests + reservation requests)
function getPendingRequests($conn) { //mixed ka matlb kisi b trha ki value return kar sakta ye function
    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM book_requests WHERE status = 'pending') +
            (SELECT COUNT(*) FROM reservation_requests WHERE status = 'pending') as total
    ";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}

// Get total unpaid fines
function getTotalUnpaidFines($conn) {
    $sql = "SELECT SUM(amount) as total FROM fines WHERE status = 'pending'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}

// Generate due date for book issue (14 days from now by default)
function generateDueDate($days = 7) {
    $date = new DateTime();  //DateTime PHP ki ek class hai jo dates aur times ke sath kaam karne ke liye use hoti hai.
    $date->add(new DateInterval("P{$days}D"));//Agar $days = 7 hai to "P7D" ka interval banega aur current date me 7 din add ho jaayenge.
    return $date->format('Y-m-d');            //ye line updated date ko ek readable format (Y-m-d) me return karti hai.
}

//Calculate fine amount based on days overdue
/*function calculateFine($dueDate, $returnDate, $finePerDay = 1.00) {
$due = new DateTime($dueDate);
    $return = new DateTime($returnDate);
    $diff = $return->diff($due);
    
   if ($return > $due) {
        return $diff->days * $finePerDay;
 }
    
    return 0;
 }
*/
// Upload file and return path
function uploadFile($file, $targetDir = '../uploads/ebooks/') {
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = basename($file['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Generate unique file name to prevent overwriting
    $fileName = uniqid() . '_' . $fileName;
    $targetFilePath = $targetDir . $fileName;
    
    // Allow only certain file formats
    $allowedTypes = array('pdf', 'doc', 'docx', 'epub');
    if (!in_array(strtolower($fileType), $allowedTypes)) {
        return array('success' => false, 'message' => 'Only PDF, DOC, DOCX & EPUB files are allowed.');
    }
    
    // Check file size (limit to 50MB)
    if ($file['size'] > 50 * 1024 * 1024) {
        return array('success' => false, 'message' => 'File size should be less than 50MB.');
    }
    
    // Upload file
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

// Format file size
function formatFileSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($size >= 1024 && $i < 4) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

// Send notification to user
function sendNotification($conn, $userId, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");//yahan ek SQL query prepare ki gayi hai jo notifications table me naya record insert karegi.? placeholders hain (parameters baad me bind kiye jaayenge).
    $stmt->bind_param("is", $userId, $message);
    return $stmt->execute();
}

// Format date for display
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Check if a book can be issued (available quantity > 0)
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

// Update book availability when issued or returned
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

// Get user name by ID
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

// Get book book_name by ID
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

// NEW RESERVATION REQUEST FUNCTIONS

// Create a reservation request (student submits request)
function createReservationRequest($conn, $bookId, $userId, $notes = '') {
    // Check if user already has a pending reservation request for this book
    $stmt = $conn->prepare("
        SELECT id FROM reservation_requests 
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
        SELECT id FROM book_reservations 
        WHERE book_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->bind_param("ii", $bookId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return array('success' => false, 'message' => 'You already have an active reservation for this book.');
    }
    
    // Create reservation request
    $stmt = $conn->prepare("
        INSERT INTO reservation_requests (book_id, user_id, notes)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $bookId, $userId, $notes);
    
    if ($stmt->execute()) {
        $bookbook_name = getBookbook_name($conn, $bookId);
        $message = "Your reservation request for '{$bookbook_name}' has been submitted and is pending approval.";
        sendNotification($conn, $userId, $message);
        
        return array(
            'success' => true, 
            'message' => "Reservation request submitted successfully! You will be notified once it's reviewed."
        );
    } else {
        return array('success' => false, 'message' => 'Error creating reservation request.');
    }
}

// Approve reservation request (librarian approves and creates actual reservation)
function approveReservationRequest($conn, $requestId) {
    // Get request details
    $stmt = $conn->prepare("
        SELECT rr.*, b.book_name, u.name as user_name 
        FROM reservation_requests rr
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
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update request status to approved
        $stmt = $conn->prepare("UPDATE reservation_requests SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        
        // Create actual reservation
        $reservationResult = createBookReservation($conn, $request['book_id'], $request['user_id'], $request['notes']);
        
        if (!$reservationResult['success']) {
            throw new Exception($reservationResult['message']);
        }
        
        // Send notification to user
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

//Reject reservation request
function rejectReservationRequest($conn, $requestId) {
    // Get request details
    $stmt = $conn->prepare("
        SELECT rr.*, b.book_name, u.name as user_name 
        FROM reservation_requests rr
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
    
    // Update request status to rejected
    $stmt = $conn->prepare("UPDATE reservation_requests SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    
    if ($stmt->execute()) {
        // Send notification to user
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



// Create a book reservation (called after approval)
function createBookReservation($conn, $bookId, $userId, $notes = '') {
    // Check if user already has an active reservation for this book
    $stmt = $conn->prepare("
        SELECT id FROM book_reservations 
        WHERE book_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->bind_param("ii", $bookId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return array('success' => false, 'message' => 'You already have an active reservation for this book.');
    }
    
    // Get next priority number
    $stmt = $conn->prepare("
        SELECT COALESCE(MAX(priority_number), 0) + 1 as next_priority 
        FROM book_reservations 
        WHERE book_id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $priorityNumber = $row['next_priority'];
    
    // Set expiration date (7 days from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    // Create reservation
    $stmt = $conn->prepare("
        INSERT INTO book_reservations (book_id, user_id, priority_number, expires_at, notes)
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

// Cancel a book reservation
function cancelBookReservation($conn, $reservationId, $userId) {
    $stmt = $conn->prepare("
        UPDATE book_reservations 
        SET status = 'cancelled' 
        WHERE id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->bind_param("ii", $reservationId, $userId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // Reorder priorities for remaining reservations
        reorderReservationPriorities($conn, $reservationId);
        return array('success' => true, 'message' => 'Reservation cancelled successfully.');
    } else {
        return array('success' => false, 'message' => 'Reservation not found or cannot be cancelled.');
    }
}

// Reorder reservation priorities after cancellation
function reorderReservationPriorities($conn, $cancelledReservationId) {
    // Get the cancelled reservation details
    $stmt = $conn->prepare("
        SELECT book_id, priority_number 
        FROM book_reservations 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $cancelledReservationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $cancelled = $result->fetch_assoc();
        
        // Update priorities for reservations with higher priority numbers
        $stmt = $conn->prepare("
            UPDATE book_reservations 
            SET priority_number = priority_number - 1 
            WHERE book_id = ? AND priority_number > ? AND status = 'active'
        ");
        $stmt->bind_param("ii", $cancelled['book_id'], $cancelled['priority_number']);
        $stmt->execute();
    }
}

// Process reservations when a book is returned - ENHANCED VERSION
function processBookReservations($conn, $bookId) {
    // Get the next reservation in queue
    $stmt = $conn->prepare("
        SELECT r.*, u.name as user_name, u.email as user_email, b.book_name as book_book_name
        FROM book_reservations r
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
        
        // Start transaction for automatic book issue
        $conn->begin_transaction();
        
        try {
            // Mark reservation as fulfilled
            $stmt = $conn->prepare("
                UPDATE book_reservations 
                SET status = 'fulfilled', notified_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $reservation['id']);
            $stmt->execute();
            
            // Automatically issue the book to the reserved user
            $returnDate = generateDueDate(14); // 14 days from now
            
            $stmt = $conn->prepare("
                INSERT INTO issued_books (book_id, user_id, return_date)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iis", $bookId, $reservation['user_id'], $returnDate);
            $stmt->execute();
            
            // Update book availability (decrease by 1 since it's now issued)
            updateBookAvailability($conn, $bookId, 'issue');
            
            // Send notification to user about automatic issue
            $message = "Great news! The book '{$reservation['book_book_name']}' you reserved has been automatically issued to you. Due date: " . date('F j, Y', strtotime($returnDate)) . ". Please collect it from the library.";
            sendNotification($conn, $reservation['user_id'], $message);
            
            // Reorder remaining reservations
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
            
            // Fallback to just notification if auto-issue fails
            $stmt = $conn->prepare("
                UPDATE book_reservations 
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


// Get user's reservations
function getUserReservations($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT r.*, b.book_name, b.author, b.available_quantity
        FROM book_reservations r
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

// Get book reservation queue
function getBookReservationQueue($conn, $bookId) {
    $stmt = $conn->prepare("
        SELECT r.*, u.name as user_name, u.email as user_email
        FROM book_reservations r
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

// Clean expired reservations
function cleanExpiredReservations($conn) {
    // Get expired reservations
    $stmt = $conn->prepare("
        SELECT id, book_id, user_id, priority_number
        FROM book_reservations 
        WHERE status = 'active' AND expires_at < NOW()
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $expiredReservations = array();
    while ($row = $result->fetch_assoc()) {
        $expiredReservations[] = $row;
    }
    
    // Mark as expired and reorder priorities
    foreach ($expiredReservations as $reservation) {
        $stmt = $conn->prepare("
            UPDATE book_reservations 
            SET status = 'expired' 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $reservation['id']);
        $stmt->execute();
        
        // Reorder priorities
        reorderReservationPriorities($conn, $reservation['id']);
        
        // Send notification to user
        $bookbook_name = getBookbook_name($conn, $reservation['book_id']);
        $message = "Your reservation for '{$bookbook_name}' has expired. You can create a new reservation if the book is still unavailable.";
        sendNotification($conn, $reservation['user_id'], $message);
    }
    
    return count($expiredReservations);
}



//agay nahe kiah stopppp







// NEW FUNCTION: Auto-process reservations when books become available
function autoProcessReservationsOnAvailability($conn, $bookId) {
    // Check if there are any active reservations for this book
    $stmt = $conn->prepare("
        SELECT COUNT(*) as reservation_count 
        FROM book_reservations 
        WHERE book_id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['reservation_count'] > 0) {
        // Check how many copies are available
        $stmt = $conn->prepare("SELECT available_quantity FROM books WHERE id = ?");
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        
        $availableCopies = $book['available_quantity'];
        $processedReservations = 0;
        
        // Process reservations up to the number of available copies
        while ($availableCopies > 0 && $processedReservations < $row['reservation_count']) {
            $reservationResult = processBookReservations($conn, $bookId);
            
            if ($reservationResult['success']) {
                $processedReservations++;
                if ($reservationResult['auto_issued']) {
                    $availableCopies--; // Decrease available copies if book was auto-issued
                }
            } else {
                break; // No more reservations to process
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