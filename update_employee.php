<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get employee details
if (isset($_GET['ssn'])) {
    $ssn = $_GET['ssn'];
    $stmt = $conn->prepare("SELECT e.*, el.Username 
                           FROM Employee e 
                           LEFT JOIN employee_login el ON e.SSN = el.SSN 
                           WHERE e.SSN = ?");
    $stmt->bind_param("i", $ssn);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();

    if (!$employee) {
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Fetch branches for dropdown
$branches = $conn->query("SELECT BranchId, Name FROM branch ORDER BY Name");

// Fetch employees for manager dropdown
$managers = $conn->query("SELECT SSN, Name FROM employee WHERE SSN != $ssn ORDER BY Name");

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Update employee table
        $stmt = $conn->prepare("UPDATE employee SET 
                              Name = ?, 
                              PhoneNumber = ?, 
                              StartDate = ?, 
                              BranchID = ?, 
                              ManagerSSN = ? 
                              WHERE SSN = ?");
        $manager_ssn = empty($_POST['manager_ssn']) ? null : $_POST['manager_ssn'];
        $stmt->bind_param("sissii", 
            $_POST['name'],
            $_POST['phone'],
            $_POST['start_date'],
            $_POST['branch_id'],
            $manager_ssn,
            $ssn
        );
        $stmt->execute();

        // Update login if password is changed
        if (!empty($_POST['password'])) {
            $stmt = $conn->prepare("UPDATE employee_login SET Password = ? WHERE SSN = ?");
            $stmt->bind_param("si", $_POST['password'], $ssn);
            $stmt->execute();
        }

        $conn->commit();
        $_SESSION['success'] = "Employee updated successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating employee: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employee</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            line-height: 1.6;
        }
        .navbar {
            background: #2563eb;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 1rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            color: white;
        }
        .btn-primary {
            background: #2563eb;
        }
        .back-btn {
            color: white;
            text-decoration: none;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>Update Employee</h2>
        <div></div>
    </nav>

    <div class="container">
        <div class="card">
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['Name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['PhoneNumber']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($employee['StartDate']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="branch_id">Branch:</label>
                        <select id="branch_id" name="branch_id" required>
                            <?php 
                            $branches->data_seek(0);
                            while ($branch = $branches->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $branch['BranchId']; ?>" 
                                    <?php echo ($branch['BranchId'] == $employee['BranchID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($branch['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="manager_ssn">Manager:</label>
                        <select id="manager_ssn" name="manager_ssn">
                            <option value="">None</option>
                            <?php 
                            $managers->data_seek(0);
                            while ($manager = $managers->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $manager['SSN']; ?>" 
                                    <?php echo ($manager['SSN'] == $employee['ManagerSSN']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($manager['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current):</label>
                    <input type="password" id="password" name="password">
                </div>

                <button type="submit" class="btn btn-primary">Update Employee</button>
            </form>
        </div>
    </div>
</body>
</html>