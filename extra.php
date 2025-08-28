<!--profile librarian recent activity-->

        <!-- Account Activity -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="card-body">
                <?php
                // Get recent activity for this user
                $activitySql = "
                    (SELECT 'issued_book' as type, b.book_name as item, ib.issue_date as date
                    FROM issued_books ib
                    JOIN books b ON ib.book_id = b.id
                    WHERE ib.user_id = ?
                    ORDER BY ib.issue_date DESC
                    LIMIT 3)
                    
                    UNION
                    
                    (SELECT 'returned_book' as type, b.book_name as item, ib.actual_return_date as date
                    FROM issued_books ib
                    JOIN books b ON ib.book_id = b.id
                    WHERE ib.user_id = ? AND ib.status = 'returned'
                    ORDER BY ib.actual_return_date DESC
                    LIMIT 3)
                    
                    UNION
                    
                    (SELECT 'notification' as type, LEFT(message, 50) as item, created_at as date
                    FROM notifications
                    WHERE user_id = ?
                    ORDER BY created_at DESC
                    LIMIT 3)
                    
                    ORDER BY date DESC
                    LIMIT 5
                ";
                
                $stmt = $conn->prepare($activitySql);
                $stmt->bind_param("iii", $userId, $userId, $userId);
                $stmt->execute();
                $activityResult = $stmt->get_result();
                $activities = [];
                
                if ($activityResult) {
                    while ($row = $activityResult->fetch_assoc()) {
                        $activities[] = $row;
                    }
                }
                ?>
                
                <?php if (count($activities) > 0): ?>
                    <ul class="activity-list">
                        <?php foreach ($activities as $activity): ?>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <?php if ($activity['type'] == 'issued_book'): ?>
                                        <i class="fas fa-book"></i>
                                    <?php elseif ($activity['type'] == 'returned_book'): ?>
                                        <i class="fas fa-undo"></i>
                                    <?php elseif ($activity['type'] == 'notification'): ?>
                                        <i class="fas fa-bell"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-info">
                                    <h4 class="activity-title">
                                        <?php 
                                        if ($activity['type'] == 'issued_book') {
                                            echo 'Issued Book: ' . htmlspecialchars($activity['item']);
                                        } elseif ($activity['type'] == 'returned_book') {
                                            echo 'Returned Book: ' . htmlspecialchars($activity['item']);
                                        } elseif ($activity['type'] == 'notification') {
                                            echo 'Notification: ' . htmlspecialchars($activity['item']) . '...';
                                        }
                                        ?>
                                    </h4>
                                    <div class="activity-meta">
                                        <span class="activity-time"><?php echo date('M d, Y H:i', strtotime($activity['date'])); ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center">No recent activity found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
