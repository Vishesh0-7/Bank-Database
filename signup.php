<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $name = $conn->real_escape_string($_POST['name']);
    $ssn = $conn->real_escape_string($_POST['ssn']);
    $house_no = $conn->real_escape_string($_POST['house_no']);
    $street = $conn->real_escape_string($_POST['street']);
    $zipcode = $conn->real_escape_string($_POST['zipcode']);
    $account_type = $conn->real_escape_string($_POST['account_type']);
    $initial_deposit = floatval($_POST['initial_deposit']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $branchid = $conn->real_escape_string($_POST['branchid']);
    
    // Validate minimum deposit
    $min_deposits = [
        'Checkings' => 20,
        'Savings' => 20,
        'Money Market Account' => 500
    ];

    if ($initial_deposit < $min_deposits[$account_type]) {
        echo json_encode(['success' => false, 'message' => "Minimum deposit for {$account_type} is {$min_deposits[$account_type]}"]);
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into customer table
        $customer_query = "INSERT INTO customer (SSN, Name, House_No, Street, ZipCode, BranchId) 
                           VALUES ('$ssn', '$name', '$house_no', '$street', '$zipcode', $branchid)";
        if (!$conn->query($customer_query)) {
            throw new Exception("Customer insert failed: " . $conn->error);
        }

        // Generate new account number
        $account_number = mt_rand(100000, 999999);

        // Insert into account table
        $account_query = "INSERT INTO account (AccountNumber, AccountType, BranchID) 
                          VALUES ('$account_number', '$account_type', $branchid)";
        if (!$conn->query($account_query)) {
            throw new Exception("Account insert failed: " . $conn->error);
        }

        // Assign a personal banker randomly from employees in the same branch
        $personal_banker_query = "SELECT SSN FROM employee WHERE BranchID = $branchid ORDER BY RAND() LIMIT 1";
        $result = $conn->query($personal_banker_query);
        if ($result->num_rows > 0) {
            $personal_banker_ssn = $result->fetch_assoc()['SSN'];
        } else {
            throw new Exception("No employee found in the selected branch to assign as personal banker.");
        }

        // Link customer to account
        $account_customer_query = "INSERT INTO accountcustomer (AccountNumber, CustomerSSN, LastAccessedDate) 
                                   VALUES ('$account_number', '$ssn', CURDATE())";
        if (!$conn->query($account_customer_query)) {
            throw new Exception("Account-Customer link failed: " . $conn->error);
        }

        // Update customer with personal banker SSN
        $update_customer_query = "UPDATE customer SET PersonalBankerSSN = '$personal_banker_ssn' WHERE SSN = '$ssn'";
        if (!$conn->query($update_customer_query)) {
            throw new Exception("Failed to assign personal banker to customer: " . $conn->error);
        }

        // Insert login credentials
        $login_query = "INSERT INTO cust_login (Username, Password, SSN) 
                        VALUES ('$username', '$password', '$ssn')";
        if (!$conn->query($login_query)) {
            throw new Exception("Login insert failed: " . $conn->error);
        }

        // Create first transaction (initial deposit)
        $transaction_query = "INSERT INTO transaction (TransactionType, TransactionDate, TransactionTime, 
                              Amount, Charge, AccountNumber) 
                              VALUES ('CD', CURDATE(), CURTIME(), '$initial_deposit', 0, '$account_number')";
        if (!$conn->query($transaction_query)) {
            throw new Exception("Transaction insert failed: " . $conn->error);
        }

        // Update AccountBalanceHistory table with initial deposit
        $balance_history_query = "INSERT INTO accountbalancehistory (AccountNumber, LastAccessedDate, Balance) 
                                  VALUES ('$account_number', CURDATE(), '$initial_deposit')";
        if (!$conn->query($balance_history_query)) {
            throw new Exception("Account balance history insert failed: " . $conn->error);
        }

        // Commit transaction
        $conn->commit();

        // Redirect to login.html on success
        header("Location: login.html?message=Signup successful!");
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
