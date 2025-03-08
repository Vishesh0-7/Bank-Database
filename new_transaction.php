<?php
session_start();
include 'db_connect.php'; // Include database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];

    // Fetch customer SSN
    $query = "SELECT SSN FROM Cust_Login WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $ssn = $user['SSN'];

    // Fetch account number
    $query2 = "SELECT AccountNumber FROM AccountCustomer WHERE CustomerSSN = ?";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("s", $ssn);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $account = $result2->fetch_assoc();
    $accountNumber = $account['AccountNumber'];

    // Fetch the current balance
    $query3 = "SELECT Balance FROM accountbalancehistory WHERE AccountNumber = ?";
    $stmt3 = $conn->prepare($query3);
    $stmt3->bind_param("i", $accountNumber);
    $stmt3->execute();
    $stmt3->bind_result($currentBalance);
    $stmt3->fetch();
    $stmt3->close();

    // Retrieve and cast form input
    $transactionType = $_POST['transaction_type']; // CD (Credit) or WD (Withdrawal)
    $amount = (float) $_POST['amount'];
    $charge = isset($_POST['charge']) ? (float) $_POST['charge'] : 0.00;

    // Check for sufficient balance if withdrawal
    if ($transactionType === 'WD') {
        $totalDeduction = $amount + $charge;
        if ($currentBalance < $totalDeduction) {
            $message = "Transaction failed: Insufficient balance for withdrawal. Required: $" . number_format($totalDeduction, 2) . ", Available: $" . number_format($currentBalance, 2);
        } else {
            // Process withdrawal
            $query4 = "INSERT INTO transaction (TransactionType, TransactionDate, TransactionTime, Amount, Charge, AccountNumber) 
                       VALUES (?, CURDATE(), CURTIME(), ?, ?, ?)";
            $stmt4 = $conn->prepare($query4);
            $stmt4->bind_param("sddi", $transactionType, $amount, $charge, $accountNumber);
            if ($stmt4->execute()) {
                // Update the balance
                $newBalance = $currentBalance - $totalDeduction;
                $query5 = "UPDATE accountbalancehistory SET Balance = ?, LastAccessedDate = CURDATE() WHERE AccountNumber = ?";
                $stmt5 = $conn->prepare($query5);
                $stmt5->bind_param("di", $newBalance, $accountNumber);
                if ($stmt5->execute()) {
                    $message = "Withdrawal successful! New balance: $" . number_format($newBalance, 2);
                } else {
                    $message = "Transaction recorded, but balance update failed.";
                }
                $stmt5->close();
            } else {
                $message = "Transaction failed: " . $stmt4->error;
            }
            $stmt4->close();
        }
    } elseif ($transactionType === 'CD') {
        // Process credit
        $query4 = "INSERT INTO transaction (TransactionType, TransactionDate, TransactionTime, Amount, Charge, AccountNumber) 
                   VALUES (?, CURDATE(), CURTIME(), ?, ?, ?)";
        $stmt4 = $conn->prepare($query4);
        $stmt4->bind_param("sddi", $transactionType, $amount, $charge, $accountNumber);
        if ($stmt4->execute()) {
            // Update the balance
            $newBalance = $currentBalance + $amount - $charge;
            $query5 = "UPDATE accountbalancehistory SET Balance = ?, LastAccessedDate = CURDATE() WHERE AccountNumber = ?";
            $stmt5 = $conn->prepare($query5);
            $stmt5->bind_param("di", $newBalance, $accountNumber);
            if ($stmt5->execute()) {
                $message = "Credit successful! New balance: $" . number_format($newBalance, 2);
            } else {
                $message = "Transaction recorded, but balance update failed.";
            }
            $stmt5->close();
        } else {
            $message = "Transaction failed: " . $stmt4->error;
        }
        $stmt4->close();
    }
}

// Fetch customer name for the navbar
$username = $_SESSION['username'];
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
    <title>New Transaction</title>
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
        .transaction-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .submit-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
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
        .message {
            background: #f0f9ff;
            color: #0c4a6e;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        
        <a href="customer_dashboard.php" class="logout-btn">← Back to Dashboard</a>
    </nav>

    <div class="dashboard-container">
        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="transaction-form">
            <h2>New Transaction</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="transaction_type">Transaction Type:</label>
                    <select id="transaction_type" name="transaction_type" required>
                        <option value="CD">Credit</option>
                        <option value="WD">Withdrawal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="charge">Transaction Charge (if any):</label>
                    <input type="number" id="charge" name="charge" step="0.01">
                </div>

                <button type="submit" class="submit-btn">Submit Transaction</button>
            </form>
        </div>

        
    </div>
</body>
</html>