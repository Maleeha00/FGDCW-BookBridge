<?php
include_once '../includes/header.php';
checkUserRole('librarian');
$message = '';
$messageType = '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR unique_id LIKE ? OR class LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
}

if (!empty($role)) {
    $sql .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

$sql .= " ORDER BY name";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<h1 class="page-title">Manage Users</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>
    
    <div class="d-flex">
        <form action="" method="GET" class="d-flex">
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <input type="text" name="search" placeholder="Search users..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <select name="role" class="form-control">
                    <option value="">All Roles</option>
                    <option value="student" <?php echo $role == 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="faculty" <?php echo $role == 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                    <option value="librarian" <?php echo $role == 'librarian' ? 'selected' : ''; ?>>Librarian</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</div>

<div class="table-container" style="margin-top:30px";>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Unique ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>class</th>
                <th>Phone</th>
                <th>Registered On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <span class="unique-id-badge"><?php echo htmlspecialchars($user['unique_id']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                            switch ($user['role']) {
                                case 'student':
                                    echo '<span class="badge badge-primary">Student</span>';
                                    break;
                                case 'faculty':
                                    echo '<span class="badge badge-success">Faculty</span>';
                                    break;
                                case 'librarian':
                                    echo '<span class="badge badge-warning">Librarian</span>';
                                    break;
                                default:
                                    echo htmlspecialchars($user['role']);
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['class']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="user_details.php?id=<?php echo $user['id']; ?>" style="margin-bottom:10px"; class="btn btn-sm btn-primary">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                            
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
include_once '../includes/footer.php';
?>