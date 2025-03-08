<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM Employee_Login WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employeeLogin = $result->fetch_assoc();
    $employeeSSN = $employeeLogin['SSN'];

    // Get employee details
    $stmt2 = $conn->prepare("SELECT * FROM Employee WHERE SSN = ?");
    $stmt2->bind_param("s", $employeeSSN);
    $stmt2->execute();
    $employeeDetails = $stmt2->get_result()->fetch_assoc();

    // Get counts
    $customerCount = $conn->prepare("SELECT COUNT(*) as count FROM customer WHERE PersonalBankerSSN = ?");
    $customerCount->bind_param("s", $employeeSSN);
    $customerCount->execute();
    $customerTotal = $customerCount->get_result()->fetch_assoc()['count'];

    $dependentCount = $conn->prepare("SELECT COUNT(*) as count FROM dependents WHERE EmployeeSSN = ?");
    $dependentCount->bind_param("s", $employeeSSN);
    $dependentCount->execute();
    $dependentTotal = $dependentCount->get_result()->fetch_assoc()['count'];

    // Modified query to exclude self from team count
    $employeeCount = $conn->prepare("SELECT COUNT(*) as count FROM employee WHERE ManagerSSN = ? AND SSN != ?");
    $employeeCount->bind_param("ss", $employeeSSN, $employeeSSN);
    $employeeCount->execute();
    $employeeTotal = $employeeCount->get_result()->fetch_assoc()['count'];
} else {
    echo "No user found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #f0f2f5;
        }
        .navbar {
            background: #2563eb;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logout-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .dashboard-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .employee-info {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .info-item {
            padding: 0.5rem;
            background: #f8fafc;
            border-radius: 4px;
        }
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-title {
            font-size: 18px;
            color: #555;
            margin-bottom: 1rem;
        }
        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div>Welcome, <?php echo htmlspecialchars($employeeDetails['Name']); ?></div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>

    <div class="dashboard-container">
        <div class="employee-info">
            <h2>Employee Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>SSN:</strong> <?php echo htmlspecialchars($employeeSSN); ?>
                </div>
                <div class="info-item">
                    <strong>Phone:</strong> <?php echo htmlspecialchars($employeeDetails['PhoneNumber']); ?>
                </div>
                <div class="info-item">
                    <strong>Start Date:</strong> <?php echo htmlspecialchars($employeeDetails['StartDate']); ?>
                </div>
                <div class="info-item">
                    <strong>Branch ID:</strong> <?php echo htmlspecialchars($employeeDetails['BranchID']); ?>
                </div>
                <div class="info-item">
                    <strong>Manager SSN:</strong> <?php echo htmlspecialchars($employeeDetails['ManagerSSN']); ?>
                </div>
            </div>
        </div>

        <div class="cards-container">
            <div class="card" onclick="location.href='my_customers.php'">
                <h2 class="card-title">My Customers</h2>
                <p class="card-value"><?php echo $customerTotal; ?></p>
            </div>
            <div class="card" onclick="location.href='my_dependents.php'">
                <h2 class="card-title">My Dependents</h2>
                <p class="card-value"><?php echo $dependentTotal; ?></p>
            </div>
            <div class="card" onclick="location.href='my_team.php'">
                <h2 class="card-title">My Team</h2>
                <p class="card-value"><?php echo $employeeTotal; ?></p>
            </div>
        </div>
    </div>
</body>
</html>