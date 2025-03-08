<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user details
$query = "SELECT * FROM Cust_Login WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $ssn = $user['SSN'];

    // Fetch customer details
    $query2 = "SELECT * FROM Customer WHERE SSN = ?";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("s", $ssn);
    $stmt2->execute();
    $customer = $stmt2->get_result()->fetch_assoc();

    // Fetch account details and types
    $query3 = "SELECT A.AccountNumber, A.AccountType 
               FROM AccountCustomer AC 
               JOIN Account A ON AC.AccountNumber = A.AccountNumber 
               WHERE AC.CustomerSSN = ?";
    $stmt3 = $conn->prepare($query3);
    $stmt3->bind_param("s", $ssn);
    $stmt3->execute();
    $accounts = $stmt3->get_result();

    // Fetch loans
    $query4 = "SELECT L.LoanType, L.MonthlyPayment, L.Status 
               FROM Loan L 
               JOIN LoanCustomer LC ON LC.LoanNumber = L.LoanNumber 
               WHERE LC.CustomerSSN = ?";
    $stmt4 = $conn->prepare($query4);
    $stmt4->bind_param("s", $ssn);
    $stmt4->execute();
    $loans = $stmt4->get_result();
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
    <title>Customer Dashboard</title>
    <style>
        /* Styles retained from the original */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f0f2f5;
        }

        .top-bar {
            width: 100%;
            background-color: #1a237e;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bank-name {
            font-size: 24px;
            font-weight: bold;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-button {
            background-color: #ff3366;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-button:hover {
            background-color: #e60000;
        }

        .main-container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .user-info, .loans-info, .accounts-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .user-info h2, .loans-info h2, .accounts-info h2 {
            color: #1a237e;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .action-button {
            padding: 15px 25px;
            background-color: #1a237e;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .action-button:hover {
            transform: translateY(-2px);
            background-color: #283593;
        }

        @media screen and (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                text-align: center;
                gap: 10px;
                padding: 15px;
            }

            .user-section {
                flex-direction: column;
            }

            .main-container {
                width: 95%;
                padding: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="bank-name">Bank Dashboard</div>
        <div class="user-section">
            <span>Welcome, <?php echo htmlspecialchars($customer['Name']); ?></span>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
    </div>

    <div class="main-container">
        <div class="user-info">
            <h2>Customer Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">SSN</div>
                    <div class="info-value"><?php echo htmlspecialchars($customer['SSN']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($customer['House_No'] . ' ' . $customer['Street'] . ', ' . $customer['ZipCode']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Types Section -->
        <div class="accounts-info">
            <h2>Account Information</h2>
            <?php if ($accounts->num_rows > 0): ?>
                <div class="info-grid">
                    <?php while ($account = $accounts->fetch_assoc()): ?>
                        <div class="info-item">
                            <div class="info-label">Account Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($account['AccountNumber']); ?></div>
                            <div class="info-label">Account Type</div>
                            <div class="info-value"><?php echo htmlspecialchars($account['AccountType']); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No accounts found.</p>
            <?php endif; ?>
        </div>

        <div class="loans-info">
            <h2>Loan Information</h2>
            <?php if ($loans->num_rows > 0): ?>
                <div class="info-grid">
                    <?php while ($loan = $loans->fetch_assoc()): ?>
                        <div class="info-item">
                            <div class="info-label">Loan Type</div>
                            <div class="info-value"><?php echo htmlspecialchars($loan['LoanType']); ?></div>
                            <div class="info-label">Monthly Payment</div>
                            <div class="info-value"><?php echo htmlspecialchars($loan['MonthlyPayment']); ?></div>
                            <div class="info-label">Status</div>
                            <div class="info-value"><?php echo htmlspecialchars($loan['Status']); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No loans found.</p>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <button class="action-button" onclick="location.href='check_balance.php'">Check Balance</button>
            <button class="action-button" onclick="location.href='new_transaction.php'">New Transaction</button>
            <button class="action-button" onclick="location.href='apply_loan.php'">Apply for Loan</button>
            <button class="action-button" onclick="location.href='add_account.php'">Add Another Account</button>
        </div>
    </div>
</body>
</html>
