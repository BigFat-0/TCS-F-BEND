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

// Fetch Users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

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
            <form method="post" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; align-items: end;">
                <div>
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div>
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone_number" class="form-control" required>
                </div>
                <div>
                    <label>Billing Address</label>
                    <input type="text" name="billing_address" class="form-control" required>
                </div>
                <div>
                    <label>Sec. Question</label>
                    <select name="security_question" class="form-control" required>
                        <option value="Mother's Maiden Name">Mother's Maiden Name</option>
                        <option value="First Pet">First Pet</option>
                        <option value="Primary School">Primary School</option>
                    </select>
                </div>
                <div>
                    <label>Sec. Answer</label>
                    <input type="password" name="security_answer" class="form-control" required>
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
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

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $user['role']; ?>">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="?toggle_role_id=<?php echo $user['id']; ?>&current_role=<?php echo $user['role']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Change role?');">
                            Switch Role
                        </a>
                        <?php if ($user['id'] != $_SESSION['user_id']): // Prevent self-delete ?>
                        <a href="?delete_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">
                            Delete
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
