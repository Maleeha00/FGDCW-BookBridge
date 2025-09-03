<?php
session_start();
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
include_once '../includes/config.php';
if (!isset($_GET['fine_id']) || !is_numeric($_GET['fine_id'])) {
    die("Invalid Fine ID.");
}
$fineId = $_GET['fine_id'];
$userId = $_SESSION['user_id'];

$sql = "SELECT f.id as fine_id, f.amount, f.reason, b.book_name, u.name as student_name, u.unique_id as student_roll_no, u.class as student_class FROM fines f JOIN users u ON f.user_id = u.id JOIN issued_books ib ON f.issued_book_id = ib.id JOIN books b ON ib.book_id = b.id WHERE f.id = ? AND f.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $fineId, $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Fine not found.");
}
$data = $result->fetch_assoc();


$collegeName = "FG DEGREE COLLEGE (W) KHARIAN CANTT";
$bankName = "National Bank of Pakistan (NBP)";
$accountNumber = "3045600785";
$branchName = "Kharian Cantt (Branch Code: 0351)";
$issueDate = date("M d, Y");
$dueDate = date("M d, Y", strtotime("+7 days"));
$amountInWords = numberToWords($data['amount']); 
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Challan</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; }
        .challan-container { display: flex; justify-content: space-between; }
        .challan-copy { border: 2px solid #000; padding: 15px; width: 32%; float: left; margin-right: 1%; box-sizing: border-box;}
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .header h4, .header p { margin: 2px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .details-table td { padding: 5px; border: 1px solid #ccc; font-size: 11px; }
        .fee-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .fee-table th, .fee-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .amount { text-align: right; }
        .copy-title { text-align: center; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="challan-container">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <?php $copyFor = ($i == 0) ? "Bank Copy" : (($i == 1) ? "College Copy" : "Student Copy"); ?>
            <div class="challan-copy">
               
                 <div class="header"><h4><?php echo $collegeName; ?></h4><p>Library Fine Challan</p></div>
                 <div class="copy-title"><?php echo $copyFor; ?></div>
                 <p><strong>Bank:</strong> <?php echo $bankName; ?>, <?php echo $branchName; ?></p>
                 <p><strong>A/C:</strong> <?php echo $accountNumber; ?></p>
                 <table class="details-table">
                    <tr><td>Challan #</td><td>LIB-<?php echo $data['fine_id']; ?></td></tr>
                    <tr><td>Issue Date</td><td><?php echo $issueDate; ?></td></tr>
                    <tr><td>Due Date</td><td><?php echo $dueDate; ?></td></tr>
                    <tr><td>Roll No</td><td><?php echo htmlspecialchars($data['student_roll_no']); ?></td></tr>
                    <tr><td>Student Name</td><td><?php echo htmlspecialchars($data['student_name']); ?></td></tr>
                    <tr><td>Class</td><td><?php echo htmlspecialchars($data['student_class']); ?></td></tr>
                 </table>
                 <table class="fee-table">
                     <thead><tr><th>Fee Description</th><th class="amount">Amount (PKR)</th></tr></thead>
                     <tbody><tr><td>Library Fine for: <br><i>"<?php echo htmlspecialchars($data['book_name']); ?>"</i></td><td class="amount"><?php echo number_format($data['amount'], 2); ?></td></tr></tbody>
                     <tfoot><tr style="font-weight:bold;"><td>Total Amount</td><td class="amount"><?php echo number_format($data['amount'], 2); ?></td></tr></tfoot>
                 </table>
                 <p><strong>Amount in Words:</strong> Rupees <?php echo $amountInWords; ?> Only.</p>
            </div>
        <?php endfor; ?>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("challan-LIB-{$fineId}.pdf", ["Attachment" => true]);

?>