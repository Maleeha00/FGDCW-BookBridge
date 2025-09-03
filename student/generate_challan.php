<?php
session_start();
include_once '../includes/config.php'; 


if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to generate a challan.");
}

if (!isset($_GET['fine_id']) || !is_numeric($_GET['fine_id'])) {
    die("Invalid Fine ID.");
}

$fineId = $_GET['fine_id'];
$userId = $_SESSION['user_id'];


$sql = "
    SELECT 
        f.id as fine_id, f.amount, f.reason,
        b.book_name,
        u.name as student_name, u.unique_id as student_roll_no, u.class as student_class
    FROM fines f
    JOIN users u ON f.user_id = u.id
    JOIN issued_books ib ON f.issued_book_id = ib.id
    JOIN books b ON ib.book_id = b.id
    WHERE f.id = ? AND f.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $fineId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Fine not found or you do not have permission to view it.");
}

$data = $result->fetch_assoc();

$collegeName = "FG DEGREE COLLEGE (W) KHARIAN CANTT";
$bankName = "National Bank of Pakistan (NBP)";
$accountTitle = "FGDCW Collection Account";
$accountNumber = "3045600785";
$branchName = "Kharian Cantt (Branch Code: 0351)";

$issueDate = date("M d, Y");
$dueDate = date("M d, Y", strtotime("+7 days"));

$amountInWords = numberToWords($data['amount']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
<title>Bookbridge Fine Challan</title>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
 <style>   
 body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .challan-container { display: flex; justify-content: space-around; background-color: white; padding: 20px; max-width: 1000px; margin: auto; }
        .challan-copy { border: 2px solid #000; padding: 15px; width: 30%; position: relative; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .header h4, .header p { margin: 2px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .details-table td { padding: 6px; border: 1px solid #ccc; font-size: 13px; }
        .details-table td:first-child { font-weight: bold; width: 40%; }
        .fee-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .fee-table th, .fee-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .fee-table .amount { text-align: right; }
        .total-row th, .total-row td { font-weight: bold; }
        .copy-title { text-align: center; font-weight: bold; margin-bottom: 10px; }
        .footer { font-size: 11px; margin-top: 20px; }

    .page-actions {
        position: fixed;
        top: 20px;
        left: 20px; 
        right: 20px; 
        z-index: 1000;
        display: flex;
        justify-content: space-between; 
    }

    .page-actions .btn, .page-actions .btn-download {
        display: inline-block;
        padding: 8px 15px;
        font-size: 14px;
        font-weight: bold;
        color: white;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        cursor: pointer;
        font-family: Arial, sans-serif;
    }
    
    .page-actions .print-btn {
        background-color: #007bff; /* Blue */
    }

    .page-actions .btn-download {
        background-color: #28a745; /* Green */
    }
    
   
    @media print {
        
        .page-actions {
            display: none;
        }
    }
       
        @media print {
            body { background-color: white; padding: 0; margin: 0; }
            .challan-container { max-width: 100%; }
            
            
            .print-actions {
                display: none;
            }
        }

    </style>
</head>
<body>

<div class="page-actions">
    <a href="download_challan.php?fine_id=<?php echo $fineId; ?>" class="btn-download">
        <i class="fas fa-download"></i> Download as PDF
    </a>
    <button onclick="window.print()" class="btn print-btn">
        <i class="fas fa-print"></i> Print Challan
    </button>
</div>
<div class="challan-container">
    <?php for ($i = 0; $i < 3; $i++): ?>
    <?php $copyFor = ($i == 0) ? "Bank Copy" : (($i == 1) ? "College Copy" : "Student Copy"); ?>
    <div class="challan-copy">
        <div class="header">
            <h4><?php echo htmlspecialchars($collegeName); ?></h4>
            <p>Library Fine Challan</p>
        </div>
        <div class="copy-title"><?php echo $copyFor; ?></div>
        <p style="font-size:13px;"><strong>Bank:</strong> <?php echo htmlspecialchars($bankName); ?>, <?php echo htmlspecialchars($branchName); ?></p>
        <p style="font-size:13px;"><strong>A/C:</strong> <?php echo htmlspecialchars($accountNumber); ?></p>
        <table class="details-table">
            <tr><td>Challan #</td><td>LIB-<?php echo htmlspecialchars($data['fine_id']); ?></td></tr>
            <tr><td>Issue Date</td><td><?php echo $issueDate; ?></td></tr>
            <tr><td>Due Date</td><td><?php echo $dueDate; ?></td></tr>
            <tr><td>Roll No</td><td><?php echo htmlspecialchars($data['student_roll_no']); ?></td></tr>
            <tr><td>Student Name</td><td><?php echo htmlspecialchars($data['student_name']); ?></td></tr>
            <tr><td>Class</td><td><?php echo htmlspecialchars($data['student_class']); ?></td></tr>
        </table>
        <table class="fee-table">
            <thead>
                <tr><th>Fee Description</th><th class="amount">Amount (PKR)</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Library Fine for: <br><i>"<?php echo htmlspecialchars($data['book_name']); ?>"</i></td>
                    <td class="amount"><?php echo number_format($data['amount'], 2); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total Amount</td>
                    <td class="amount"><?php echo number_format($data['amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        <p style="font-size:12px; margin-top:10px;"><strong>Amount in Words:</strong> Rupees <?php echo htmlspecialchars($amountInWords); ?> Only.</p>
        <div class="footer">
            <p>This is a computer-generated challan and does not require a signature. Please deposit before the due date to avoid cancellation.</p>
            <p style="text-align:center; margin-top:20px;">__________________<br>Depositor's Signature</p>
        </div>
    </div>
    <?php endfor; ?>
</div>

</body>
</html>