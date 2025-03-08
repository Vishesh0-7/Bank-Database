<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch the customer SSN
$query = "SELECT * FROM Cust_Login WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $ssn = $user['SSN'];
} else {
    echo "No user found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accountType = $_POST['account_type'];
    $branchID = $_POST['branch_id'];
    $account_number = mt_rand(100000, 999999);
    // Add new account logic
    $query2 = "INSERT INTO Account (AccountNumber, AccountType, BranchID) VALUES (?, ?, ?)";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("isi", $account_number, $accountType, $branchID);

    if ($stmt2->execute()) {
        

        // Link account to customer
        $query3 = "INSERT INTO AccountCustomer (AccountNumber, CustomerSSN, LastAccessedDate) VALUES (?, ?, CURDATE())";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param("is", $account_number, $ssn);
        if ($stmt3->execute()) {
            echo "<script>alert('Account added successfully!'); window.location.href = 'customer_dashboard.php';</script>";
            exit();
        } else {
            echo "Error linking account: " . $stmt3->error;
        }
    } else {
        echo "Error adding account: " . $stmt2->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Account</title>
    <style>
        /* Retained styling from the previous code */
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
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h2 {
            color: #1a237e;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-submit {
            width: 100%;
            padding: 15px;
            background-color: #1a237e;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .form-submit:hover {
            background-color: #283593;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="bank-name">Bank Dashboard</div>
        <div class="user-section">
            <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
    </div>

    <div class="main-container">
        <h2>Add a New Account</h2>
        <form action="add_account.php" method="POST">
            <div class="form-group">
                <label for="account_type" class="form-label">Account Type</label>
                <select id="account_type" name="account_type" class="form-input" required>
                    <option value="" disabled selected>Select Account Type</option>
                    <option value="Savings">Savings</option>
                    <option value="Checking">Checking</option>
                    <option value="Business">Business</option>
                </select>
            </div>
            <div class="form-group">
                <label for="branch_id" class="form-label">Branch ID</label>
                <input type="number" id="branch_id" name="branch_id" class="form-input" placeholder="Enter Branch ID" required>
            </div>
            <button type="submit" class="form-submit">Add Account</button>
        </form>
    </div>
</body>
</html>
