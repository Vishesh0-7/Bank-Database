<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch branches for dropdown
$branches = $conn->query("SELECT BranchId, Name FROM branch ORDER BY Name");

// Fetch employees for manager dropdown
$managers = $conn->query("SELECT SSN, Name FROM employee ORDER BY Name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee</title>
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        input:focus, select:focus {
            outline: none;
            border-color: #2563eb;
            ring: 2px solid #2563eb;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .back-btn {
            color: white;
            text-decoration: none;
        }

        .form-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>Add New Employee</h2>
        <div></div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="form-header">
                <h2>New Employee Details</h2>
            </div>

            <form action="process_add_employee.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="ssn">SSN:</label>
                        <input type="text" id="ssn" name="ssn" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>

                    <div class="form-group">
                        <label for="branch_id">Branch:</label>
                        <select id="branch_id" name="branch_id" required>
                            <option value="">Select Branch</option>
                            <?php while ($branch = $branches->fetch_assoc()): ?>
                                <option value="<?php echo $branch['BranchId']; ?>">
                                    <?php echo htmlspecialchars($branch['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="manager_ssn">Manager:</label>
                        <select id="manager_ssn" name="manager_ssn">
                            <option value="">Select Manager</option>
                            <?php while ($manager = $managers->fetch_assoc()): ?>
                                <option value="<?php echo $manager['SSN']; ?>">
                                    <?php echo htmlspecialchars($manager['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Login Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Login Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Add Employee</button>
            </form>
        </div>
    </div>
</body>
</html>