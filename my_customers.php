<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT SSN FROM employee_login WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$employeeSSN = $stmt->get_result()->fetch_assoc()['SSN'];

$query = "SELECT c.*, z.City, z.State 
          FROM customer c 
          LEFT JOIN zipcode z ON c.ZipCode = z.Zipcode 
          WHERE c.PersonalBankerSSN = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employeeSSN);
$stmt->execute();
$customers = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_action'])) {
    try {
        $conn->begin_transaction();
        
        $loanNumber = $_POST['loan_number'];
        $action = $_POST['loan_action'];
        $status = ($action === 'approve') ? 'APPROVED' : 'REJECTED';
        
        $stmt = $conn->prepare("UPDATE loan SET Status = ? WHERE LoanNumber = ? AND Status = 'IN PROGRESS'");
        $stmt->bind_param("si", $status, $loanNumber);
        
        if ($stmt->execute()) {
            if ($action === 'reject') {
                $stmt = $conn->prepare("DELETE FROM loancustomer WHERE LoanNumber = ?");
                $stmt->bind_param("i", $loanNumber);
                $stmt->execute();
            }
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => "Loan " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully"]);
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Customers</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .navbar {
            background: #2563eb;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
        }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .customer-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        .customer-details { display: none; margin-top: 1rem; }
        .active .customer-details { display: block; }
        .tabs {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .tab.active {
            border-bottom: 2px solid #2563eb;
            color: #2563eb;
        }
        .loan-action {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 0.5rem;
        }
        .approve { background: #10B981; color: white; }
        .reject { background: #EF4444; color: white; }
        .back-btn {
            color: white;
            text-decoration: none;
        }
        .transaction-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        .loan-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="employee_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>My Customers</h2>
        <div></div>
    </nav>

    <div class="container">
        <?php while ($customer = $customers->fetch_assoc()): 
            $stmt = $conn->prepare("
                SELECT a.*, ab.Balance, ab.LastAccessedDate 
                FROM account a 
                JOIN accountcustomer ac ON a.AccountNumber = ac.AccountNumber 
                LEFT JOIN accountbalancehistory ab ON a.AccountNumber = ab.AccountNumber 
                WHERE ac.CustomerSSN = ?
                ORDER BY ab.LastAccessedDate DESC LIMIT 1
            ");
            $stmt->bind_param("i", $customer['SSN']);
            $stmt->execute();
            $account = $stmt->get_result()->fetch_assoc();

            $stmt = $conn->prepare("
                SELECT t.* 
                FROM transaction t 
                JOIN accountcustomer ac ON t.AccountNumber = ac.AccountNumber 
                WHERE ac.CustomerSSN = ? 
                ORDER BY t.TransactionDate DESC, t.TransactionTime DESC LIMIT 5
            ");
            $stmt->bind_param("i", $customer['SSN']);
            $stmt->execute();
            $transactions = $stmt->get_result();

            $stmt = $conn->prepare("
                SELECT l.* 
                FROM loan l
                JOIN loancustomer lc ON l.LoanNumber = lc.LoanNumber
                WHERE lc.CustomerSSN = ? AND l.Status = 'IN PROGRESS'
            ");
            $stmt->bind_param("i", $customer['SSN']);
            $stmt->execute();
            $loans = $stmt->get_result();
        ?>
            <div class="customer-card">
                <div onclick="toggleDetails(this.parentElement)">
                    <h3><?php echo htmlspecialchars($customer['Name']); ?></h3>
                    <p>SSN: <?php echo htmlspecialchars($customer['SSN']); ?></p>
                    <p>Address: <?php echo htmlspecialchars($customer['House_No'] . ' ' . $customer['Street'] . ', ' . $customer['City'] . ', ' . $customer['State']); ?></p>
                </div>
                
                <div class="customer-details">
                    <div class="tabs">
                        <div class="tab active" onclick="switchTab(this, 'account-info-<?php echo $customer['SSN']; ?>')">Account Info</div>
                        <div class="tab" onclick="switchTab(this, 'transactions-<?php echo $customer['SSN']; ?>')">Recent Transactions</div>
                        <div class="tab" onclick="switchTab(this, 'loans-<?php echo $customer['SSN']; ?>')">Loans</div>
                    </div>

                    <div id="account-info-<?php echo $customer['SSN']; ?>" class="tab-content">
                        <h4>Account Details</h4>
                        <?php if ($account): ?>
                            <div style="margin-top: 10px;">
                                <p>Account Number: <?php echo htmlspecialchars($account['AccountNumber']); ?></p>
                                <p>Account Type: <?php echo htmlspecialchars($account['AccountType']); ?></p>
                                <p>Balance: $<?php echo htmlspecialchars($account['Balance']); ?></p>
                                <p>Last Accessed: <?php echo htmlspecialchars($account['LastAccessedDate']); ?></p>
                            </div>
                        <?php else: ?>
                            <p>No account information available</p>
                        <?php endif; ?>
                    </div>

                    <div id="transactions-<?php echo $customer['SSN']; ?>" class="tab-content" style="display:none">
                        <h4>Recent Transactions</h4>
                        <?php if ($transactions->num_rows > 0): ?>
                            <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                <div class="transaction-item">
                                    <p>Date: <?php echo htmlspecialchars($transaction['TransactionDate'] . ' ' . $transaction['TransactionTime']); ?></p>
                                    <p>Type: <?php echo htmlspecialchars($transaction['TransactionType']); ?></p>
                                    <p>Amount: $<?php echo htmlspecialchars($transaction['Amount']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No recent transactions</p>
                        <?php endif; ?>
                    </div>

                    <div id="loans-<?php echo $customer['SSN']; ?>" class="tab-content" style="display:none">
                        <h4>Loan Applications (In Progress)</h4>
                        <?php if ($loans->num_rows > 0): ?>
                            <?php while ($loan = $loans->fetch_assoc()): ?>
                                <div class="loan-item">
                                    <p>Loan Number: <?php echo htmlspecialchars($loan['LoanNumber']); ?></p>
                                    <p>Type: <?php echo htmlspecialchars($loan['LoanType']); ?></p>
                                    <p>Amount: $<?php echo htmlspecialchars($loan['LoanAmount']); ?></p>
                                    <p>Monthly Payment: $<?php echo htmlspecialchars($loan['MonthlyPayment']); ?></p>
                                    <div style="margin-top: 10px;">
                                        <button onclick="handleLoanAction(<?php echo $loan['LoanNumber']; ?>, 'approve')" class="loan-action approve">
                                            Approve
                                        </button>
                                        <button onclick="handleLoanAction(<?php echo $loan['LoanNumber']; ?>, 'reject')" class="loan-action reject">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No pending loan applications</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function toggleDetails(card) {
            card.querySelector('.customer-details').style.display = 
                card.querySelector('.customer-details').style.display === 'none' ? 'block' : 'none';
        }

        function switchTab(tab, contentId) {
            const parent = tab.closest('.customer-details');
            parent.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            parent.querySelectorAll('.tab').forEach(t => {
                t.classList.remove('active');
            });
            document.getElementById(contentId).style.display = 'block';
            tab.classList.add('active');
            event.stopPropagation();
        }

        function handleLoanAction(loanNumber, action) {
            if (!confirm(`Are you sure you want to ${action} this loan?`)) return;

            const formData = new FormData();
            formData.append('loan_number', loanNumber);
            formData.append('loan_action', action);

            fetch('my_customers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                alert('Error processing request');
            });
        }
    </script>
</body>
</html>