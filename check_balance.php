<?php
session_start();
include 'db_connect.php'; // Include database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch username from session
$username = $_SESSION['username'];

// Fetch customer SSN from Cust_Login table
$query = "SELECT SSN FROM Cust_Login WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $ssn = $user['SSN'];

    // Fetch account number associated with the customer
    $query2 = "SELECT AccountNumber FROM AccountCustomer WHERE CustomerSSN = ?";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("s", $ssn);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($result2->num_rows > 0) {
        $account = $result2->fetch_assoc();
        $accountNumber = $account['AccountNumber'];

        // Fetch the balance from accountbalancehistory
        $query3 = "SELECT Balance FROM accountbalancehistory WHERE AccountNumber = ?";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param("i", $accountNumber);
        $stmt3->execute();
        $stmt3->bind_result($balance);
        $stmt3->fetch();
        $stmt3->close();
    } else {
        echo "No account found for the customer.";
        exit();
    }
} else {
    echo "Customer not found.";
    exit();
}

// Fetch customer name for the navbar
$stmt = $conn->prepare("SELECT Name FROM Customer C JOIN Cust_Login L ON C.SSN = L.SSN WHERE L.Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$customerName = $result->fetch_assoc()['Name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Balance</title>
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
        
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .dashboard-container {
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .balance-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .balance-amount {
            font-size: 2.5rem;
            color: #2563eb;
            margin: 1rem 0;
            font-weight: bold;
        }
        .back-btn {
            display: block;
            width: 100%;
            padding: 0.75rem;
            margin-top: 1rem;
            background: #6b7280;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        
        <a href="customer_dashboard.php" class="logout-btn">← Back to Dashboard</a>
    </nav>

    <div class="dashboard-container">
        <div class="balance-card">
            <h2>Account Balance</h2>
            <?php if (isset($balance)): ?>
                <p class="balance-amount">$<?php echo number_format($balance, 2); ?></p>
            <?php else: ?>
                <p>Unable to retrieve your account balance.</p>
            <?php endif; ?>
        </div>
        
    </div>
</body>
</html>