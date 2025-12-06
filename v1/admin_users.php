<?php
// v1/admin_users.php
require_once 'db_connect.php';
require_once 'admin_header.php';

$message = '';

// Handle Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $billing_address = trim($_POST['billing_address']);
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($email && $phone && $password && $first_name && $last_name) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sec_hash = password_hash($security_answer, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone_number, password_hash, security_question, security_answer_hash, billing_address, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$first_name, $last_name, $email, $phone, $hash, $security_question, $sec_hash, $billing_address, $role]);
            $message = "User created successfully.";
        } catch (PDOException $e) {
            $message = "Error creating user: " . $e->getMessage();
        }
    }
}

// Handle Edit User (Billing Address & Password Reset)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $edit_id = $_POST['edit_id'];
    $edit_billing = trim($_POST['billing_address']);
    $new_password = $_POST['new_password'];

    $sql = "UPDATE users SET billing_address = ?";
    $params = [$edit_billing];

    if (!empty($new_password)) {
        $sql .= ", password_hash = ?";
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = ?";
    $params[] = $edit_id;

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        $message = "User updated successfully.";
    } else {
        $message = "Error updating user.";
    }
}

// Handle Delete User
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$_GET['delete_id']])) {
        $message = "User deleted.";
    }
}

// Handle Toggle Role
if (isset($_GET['toggle_role_id'])) {
    $id = $_GET['toggle_role_id'];
    $current_role = $_GET['current_role'];
    $new_role = ($current_role === 'staff') ? 'customer' : 'staff';
    
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    if ($stmt->execute([$new_role, $id])) {
        $message = "User role updated.";
    }
}

// Search & Sort Logic
$search = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';
$new_order = ($order === 'ASC') ? 'DESC' : 'ASC';

// Whitelist sort columns to prevent SQL injection
$allowed_sorts = ['first_name', 'last_name', 'email', 'created_at', 'total_revenue'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'created_at';
}

// Build Query
$sql = "SELECT u.*, 
        (SELECT COALESCE(SUM(actual_bill), 0) FROM bookings WHERE user_id = u.id AND status = 'completed') as total_revenue 
        FROM users u 
        WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
        ORDER BY $sort_by $order";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%", "%$search%"]);
$users = $stmt->fetchAll();

?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> User Management</h1>
    </div>

    <?php if ($message): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <!-- Create User Form -->
        <div class="stat-card" style="grid-column: span 3;">
            <h3>Add New User</h3>
            <form method="post" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; align-items: end;">
                <div><label>First Name</label><input type="text" name="first_name" class="form-control" required></div>
                <div><label>Last Name</label><input type="text" name="last_name" class="form-control" required></div>
                <div><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div><label>Phone</label><input type="text" name="phone_number" class="form-control" required></div>
                <div style="grid-column: span 2;"><label>Billing Address</label><input type="text" name="billing_address" class="form-control" required></div>
                <div>
                    <label>Sec. Question</label>
                    <select name="security_question" class="form-control" required>
                        <option value="Mother's Maiden Name">Mother's Maiden Name</option>
                        <option value="First Pet">First Pet</option>
                        <option value="Primary School">Primary School</option>
                    </select>
                </div>
                <div><label>Sec. Answer</label><input type="password" name="security_answer" class="form-control" required></div>
                <div><label>Password</label><input type="password" name="password" class="form-control" required></div>
                <div>
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="customer">Customer</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <button type="submit" name="create_user" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>

    <!-- Search Bar -->
    <form method="get" style="margin: 20px 0; display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search by Name or Email..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="flex: 1;">
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th><a href="?sort=first_name&order=<?php echo $new_order; ?>&search=<?php echo $search; ?>">Name <?php echo ($sort_by == 'first_name') ? ($order == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                    <th><a href="?sort=email&order=<?php echo $new_order; ?>&search=<?php echo $search; ?>">Email <?php echo ($sort_by == 'email') ? ($order == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                    <th>Role</th>
                    <th><a href="?sort=total_revenue&order=<?php echo $new_order; ?>&search=<?php echo $search; ?>">LTV <?php echo ($sort_by == 'total_revenue') ? ($order == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                    <th><a href="?sort=created_at&order=<?php echo $new_order; ?>&search=<?php echo $search; ?>">Joined <?php echo ($sort_by == 'created_at') ? ($order == 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo htmlspecialchars($user['role']); ?></span></td>
                    <td>$<?php echo number_format($user['total_revenue'], 2); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['billing_address'])); ?>')">Edit</button>
                        <a href="?toggle_role_id=<?php echo $user['id']; ?>&current_role=<?php echo $user['role']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Change role?');">Role</a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:white; padding:20px; border-radius:8px; width:400px;">
        <h3>Edit User</h3>
        <form method="post">
            <input type="hidden" name="edit_id" id="modal_edit_id">
            <div class="form-group">
                <label>Billing Address</label>
                <textarea name="billing_address" id="modal_billing" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label>Reset Password (leave blank to keep current)</label>
                <input type="password" name="new_password" class="form-control">
            </div>
            <div style="margin-top:10px; text-align:right;">
                <button type="button" class="btn btn-danger" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, billing) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('modal_edit_id').value = id;
    document.getElementById('modal_billing').value = billing;
}
</script>

</body>
</html>