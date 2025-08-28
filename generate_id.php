<?php
include 'includes/config.php';

if (isset($_GET['role'])) {
    $role = $_GET['role'];

    if ($role === 'student') {
        $prefix = "STD";
    } elseif ($role === 'faculty') {
        $prefix = "FAC";
    } else {
        echo "";
        exit;
    }

    $stmt = $conn->prepare("SELECT unique_id FROM users WHERE unique_id LIKE ? ORDER BY id DESC LIMIT 1");
    $like = $prefix . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $stmt->bind_result($lastId);
    $stmt->fetch();
    $stmt->close();

    if ($lastId) {
        $num = (int)substr($lastId, 3);
        $newNum = $num + 1;
    } else {
        $newNum = 1;
    }

    $newId = $prefix . str_pad($newNum, 2, "0", STR_PAD_LEFT);
    echo $newId;
}
?>