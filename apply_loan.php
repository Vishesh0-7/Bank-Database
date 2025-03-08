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

    // Fetch customer SSN and branch ID
    $query = "SELECT C.SSN, C.BranchId FROM Cust_Login L 
              INNER JOIN Customer C ON L.SSN = C.SSN 
              WHERE L.Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $customerSSN = $customer['SSN'];
        $branchId = $customer['BranchId'];

        // Retrieve form input
        $loanType = $_POST['loan_type'];
        $loanAmount = (float) $_POST['loan_amount'];
        $loanTime = (int) $_POST['loan_time'];
        $annualInterestRate = 6.5; // Example: 6.5% annual interest rate
        $monthlyInterestRate = $annualInterestRate / (12 * 100);

        // Calculate EMI
        $emi = ($loanAmount * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $loanTime)) /
               (pow(1 + $monthlyInterestRate, $loanTime) - 1);
        $emi = round($emi, 2);

        // Step 1: Insert a new loan record
        $query2 = "INSERT INTO Loan (LoanType, LoanAmount, MonthlyPayment, LoanTime, BranchID) VALUES (?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("sdiis", $loanType, $loanAmount, $emi, $loanTime, $branchId);

        if ($stmt2->execute()) {
            // Get the generated LoanNumber
            $loanNumber = $stmt2->insert_id;

            // Step 2: Insert into LoanCustomer table
            $query3 = "INSERT INTO LoanCustomer (LoanNumber, LoanType, CustomerSSN) VALUES (?, ?, ?)";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bind_param("iss", $loanNumber, $loanType, $customerSSN);

            if ($stmt3->execute()) {
                $message = "Loan application submitted successfully! Your monthly payment is $$emi.";
            } else {
                $message = "Failed to link loan with customer: " . $stmt3->error;
            }
            $stmt3->close();
        } else {
            $message = "Loan application failed: " . $stmt2->error;
        }
        $stmt2->close();
    } else {
        $message = "Customer information not found.";
    }

    $stmt->close();
}

// Fetch customer name for the navbar
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT C.Name FROM Cust_Login L JOIN Customer C ON L.SSN = C.SSN WHERE L.Username = ?");
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
    <title>Apply for Loan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #f0f2f5;
            color: #333;
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
        .loan-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 0.75rem;
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
            font-size: 1rem;
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
            font-size: 1rem;
        }
        .message {
            background: #e6f7ff;
            color: #0056b3;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            border: 1px solid #b3e0ff;
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

        <div class="loan-form">
            <h2>Apply for a Loan</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="loan_type">Loan Type:</label>
                    <select id="loan_type" name="loan_type" required>
                        <option value="Personal Loan">Personal Loan</option>
                        <option value="Home Loan">Home Loan</option>
                        <option value="Car Loan">Vehicle Loan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="loan_amount">Loan Amount:</label>
                    <input type="number" id="loan_amount" name="loan_amount" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="loan_time">Loan Time (in months):</label>
                    <input type="number" id="loan_time" name="loan_time" step="1" required>
                </div>

                <button type="submit" class="submit-btn">Submit Loan Application</button>
            </form>
        </div>
    </div>
</body>
</html>
