<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all employees with branch details
$query = "SELECT e.*, b.Name as BranchName, m.Name as ManagerName 
          FROM Employee e 
          LEFT JOIN Branch b ON e.BranchID = b.BranchId
          LEFT JOIN Employee m ON e.ManagerSSN = m.SSN
          ORDER BY e.Name";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        .navbar h2 {
            font-size: 1.5rem;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #4b5563;
        }

        tr:hover {
            background: #f8fafc;
        }

        .action-btns a {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }

        .edit-btn {
            background: #f59e0b;
        }

        .delete-btn {
            background: #dc2626;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Admin Dashboard</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>

    <div class="container">
        <div class="header-actions">
            <h2>Employee Management</h2>
            <a href="add_employee.php" class="btn btn-primary">Add New Employee</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SSN</th>
                        <th>Phone Number</th>
                        <th>Branch</th>
                        <th>Manager</th>
                        <th>Start Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Name']); ?></td>
                            <td><?php echo htmlspecialchars($row['SSN']); ?></td>
                            <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                            <td><?php echo htmlspecialchars($row['BranchName']); ?></td>
                            <td><?php echo htmlspecialchars($row['ManagerName'] ?? 'None'); ?></td>
                            <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                            <td class="action-btns">
                                <a href="update_employee.php?ssn=<?php echo $row['SSN']; ?>" class="edit-btn">Edit</a>
                                <a href="delete_employee.php?ssn=<?php echo $row['SSN']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this employee?')" 
                                   class="delete-btn">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>