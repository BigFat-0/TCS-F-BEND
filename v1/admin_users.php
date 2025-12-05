<?php
// v1/admin_users.php
require_once 'db_connect.php';
require_once 'admin_header.php';

$message = '';

// Handle Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($email && $phone && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, phone_number, password_hash, role) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$email, $phone, $hash, $role]);
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
            <form method="post" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                <div>
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone_number" class="form-control" required>
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
