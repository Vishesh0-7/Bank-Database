<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get manager's SSN
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT e.SSN, e.Name as ManagerName, e.BranchID 
                       FROM employee_login el 
                       JOIN employee e ON el.SSN = e.SSN 
                       WHERE el.Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$manager = $stmt->get_result()->fetch_assoc();
$managerSSN = $manager['SSN'];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update':
                    $stmt = $conn->prepare("UPDATE employee SET 
                        Name = ?, 
                        PhoneNumber = ?, 
                        BranchID = ? 
                        WHERE SSN = ? AND ManagerSSN = ?");
                    $stmt->bind_param("siiii", 
                        $_POST['name'],
                        $_POST['phone'],
                        $_POST['branch_id'],
                        $_POST['employee_ssn'],
                        $managerSSN
                    );
                    break;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM employee_login WHERE SSN = ?");
                    $stmt->bind_param("i", $_POST['employee_ssn']);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("DELETE FROM employee WHERE SSN = ? AND ManagerSSN = ?");
                    $stmt->bind_param("ii", $_POST['employee_ssn'], $managerSSN);
                    break;
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Operation completed successfully']);
            } else {
                throw new Exception("Error executing operation");
            }
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// Get all employees under this manager (excluding the manager)
$stmt = $conn->prepare("
    SELECT e.*, b.Name as BranchName, 
           COALESCE(COUNT(c.SSN), 0) as CustomerCount,
           COALESCE(COUNT(d.DependentID), 0) as DependentCount
    FROM employee e 
    LEFT JOIN branch b ON e.BranchID = b.BranchId
    LEFT JOIN customer c ON e.SSN = c.PersonalBankerSSN
    LEFT JOIN dependents d ON e.SSN = d.EmployeeSSN
    WHERE e.ManagerSSN = ? AND e.SSN != ?
    GROUP BY e.SSN
    ORDER BY e.Name
");
$stmt->bind_param("ii", $managerSSN, $managerSSN);
$stmt->execute();
$team = $stmt->get_result();

// Get all branches for the dropdown
$branches = $conn->query("SELECT BranchId, Name FROM branch ORDER BY Name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Team</title>
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
            max-width: 1200px; 
            margin: 2rem auto; 
            padding: 0 1rem; 
        }
        .back-btn {
            color: white;
            text-decoration: none;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .manager-info {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }
        .manager-info h3 {
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .team-section {
            margin-top: 2rem;
        }
        .team-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f2f5;
            color: #1f2937;
        }
        .employee-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .employee-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .employee-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .employee-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .stat-box {
            background: white;
            padding: 0.75rem;
            border-radius: 6px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2563eb;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-warning { 
            background: #f59e0b; 
            color: white; 
        }
        .btn-danger { 
            background: #dc2626; 
            color: white; 
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
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
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-buttons {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
        }
        .no-employees {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            background: #f8fafc;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="employee_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>My Team</h2>
        <div></div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="team-section">
                <h3 class="team-header">Team Members</h3>
                
                <?php if ($team->num_rows > 0): ?>
                    <?php while ($employee = $team->fetch_assoc()): ?>
                        <div class="employee-card">
                            <div class="employee-header">
                                <h4><?php echo htmlspecialchars($employee['Name']); ?></h4>
                                <div>
                                    <button onclick="showEditModal(<?php 
                                        echo htmlspecialchars(json_encode([
                                            'ssn' => $employee['SSN'],
                                            'name' => $employee['Name'],
                                            'phone' => $employee['PhoneNumber'],
                                            'branch_id' => $employee['BranchID']
                                        ])); 
                                    ?>)" class="btn btn-warning">Edit</button>
                                    <button onclick="handleDelete(<?php echo $employee['SSN']; ?>)" 
                                            class="btn btn-danger">Delete</button>
                                </div>
                            </div>
                            <div class="employee-details">
                                <div class="stat-box">
                                    <div class="number"><?php echo $employee['CustomerCount']; ?></div>
                                    <div>Customers</div>
                                </div>
                                <div class="stat-box">
                                    <div class="number"><?php echo $employee['DependentCount']; ?></div>
                                    <div>Dependents</div>
                                </div>
                                <div class="stat-box">
                                    <div>Branch</div>
                                    <div><?php echo htmlspecialchars($employee['BranchName']); ?></div>
                                </div>
                                <div class="stat-box">
                                    <div>Phone</div>
                                    <div><?php echo htmlspecialchars($employee['PhoneNumber']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-employees">
                        <p>No team members found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="employeeModal" class="modal">
        <div class="modal-content">
            <h3>Edit Employee Details</h3>
            <form id="employeeForm" onsubmit="handleSubmit(event)">
                <input type="hidden" id="employeeSSN" name="employee_ssn">
                <input type="hidden" name="action" value="update">
                
                <div class="form-group">
                    <label for="employeeName">Name:</label>
                    <input type="text" id="employeeName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="employeePhone">Phone Number:</label>
                    <input type="tel" id="employeePhone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="branchId">Branch:</label>
                    <select id="branchId" name="branch_id" required>
                        <?php while ($branch = $branches->fetch_assoc()): ?>
                            <option value="<?php echo $branch['BranchId']; ?>">
                                <?php echo htmlspecialchars($branch['Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn btn-warning">Update</button>
                    <button type="button" onclick="closeModal()" class="btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('employeeModal');
        const form = document.getElementById('employeeForm');

        function showEditModal(employee) {
            document.getElementById('employeeSSN').value = employee.ssn;
            document.getElementById('employeeName').value = employee.name;
            document.getElementById('employeePhone').value = employee.phone;
            document.getElementById('branchId').value = employee.branch_id;
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function handleSubmit(event) {
            event.preventDefault();
            const formData = new FormData(form);

            fetch('my_team.php', {
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

        function handleDelete(ssn) {
            if (!confirm('Are you sure you want to remove this employee from your team?')) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('employee_ssn', ssn);

            fetch('my_team.php', {
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

        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>