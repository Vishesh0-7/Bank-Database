<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get employee SSN
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT e.SSN, e.Name as EmployeeName 
                       FROM employee_login el 
                       JOIN employee e ON el.SSN = e.SSN 
                       WHERE el.Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$employeeSSN = $employee['SSN'];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $conn->prepare("INSERT INTO dependents (EmployeeSSN, Name) VALUES (?, ?)");
                    $stmt->bind_param("is", $employeeSSN, $_POST['name']);
                    break;

                case 'update':
                    $stmt = $conn->prepare("UPDATE dependents SET Name = ? WHERE DependentID = ? AND EmployeeSSN = ?");
                    $stmt->bind_param("sii", $_POST['name'], $_POST['dependent_id'], $employeeSSN);
                    break;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM dependents WHERE DependentID = ? AND EmployeeSSN = ?");
                    $stmt->bind_param("ii", $_POST['dependent_id'], $employeeSSN);
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

// Get dependents
$stmt = $conn->prepare("SELECT * FROM dependents WHERE EmployeeSSN = ? ORDER BY Name");
$stmt->bind_param("i", $employeeSSN);
$stmt->execute();
$dependents = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dependents</title>
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
            max-width: 800px; 
            margin: 2rem auto; 
            padding: 0 1rem; 
        }
        .back-btn {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .dependent-section {
            margin-top: 2rem;
        }
        .dependent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f2f5;
        }
        .dependent-header h3 {
            color: #1f2937;
            font-size: 1.25rem;
        }
        .dependent-list {
            margin-top: 1.5rem;
        }
        .dependent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: #f8fafc;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .dependent-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .btn {
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn-primary { 
            background: #2563eb; 
            color: white; 
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-warning { 
            background: #f59e0b; 
            color: white; 
        }
        .btn-warning:hover {
            background: #d97706;
        }
        .btn-danger { 
            background: #dc2626; 
            color: white; 
        }
        .btn-danger:hover {
            background: #b91c1c;
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .modal-content h3 {
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        .employee-info {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }
        .employee-info h3 {
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .employee-info p {
            color: #4b5563;
            margin-bottom: 0.25rem;
        }
        .no-dependents {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            background: #f8fafc;
            border-radius: 8px;
            margin-top: 1.5rem;
        }
        .modal-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="employee_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>My Dependents</h2>
        <div></div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="employee-info">
                <h3>Employee Details</h3>
                <p>Name: <?php echo htmlspecialchars($employee['EmployeeName']); ?></p>
                <p>SSN: <?php echo htmlspecialchars($employeeSSN); ?></p>
            </div>

            <div class="dependent-section">
                <div class="dependent-header">
                    <h3>Dependents List</h3>
                    <button onclick="showAddModal()" class="btn btn-primary">Add New Dependent</button>
                </div>
                
                <div class="dependent-list">
                    <?php if ($dependents->num_rows > 0): ?>
                        <?php while ($dependent = $dependents->fetch_assoc()): ?>
                            <div class="dependent-item">
                                <div>
                                    <p><?php echo htmlspecialchars($dependent['Name']); ?></p>
                                </div>
                                <div>
                                    <button onclick="showEditModal(<?php echo $dependent['DependentID']; ?>, '<?php echo htmlspecialchars($dependent['Name']); ?>')" 
                                            class="btn btn-warning">Edit</button>
                                    <button onclick="handleDelete(<?php echo $dependent['DependentID']; ?>)" 
                                            class="btn btn-danger">Delete</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-dependents">
                            <p>No dependents found. Click "Add New Dependent" to add one.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit -->
    <div id="dependentModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Add Dependent</h3>
            <form id="dependentForm" onsubmit="handleSubmit(event)">
                <input type="hidden" id="dependentId" name="dependent_id">
                <input type="hidden" id="action" name="action">
                
                <div class="form-group">
                    <label for="dependentName">Name:</label>
                    <input type="text" id="dependentName" name="name" required>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" onclick="closeModal()" class="btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('dependentModal');
        const form = document.getElementById('dependentForm');

        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Dependent';
            document.getElementById('action').value = 'add';
            document.getElementById('dependentId').value = '';
            document.getElementById('dependentName').value = '';
            modal.style.display = 'flex';
        }

        function showEditModal(id, name) {
            document.getElementById('modalTitle').textContent = 'Edit Dependent';
            document.getElementById('action').value = 'update';
            document.getElementById('dependentId').value = id;
            document.getElementById('dependentName').value = name;
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function handleSubmit(event) {
            event.preventDefault();
            const formData = new FormData(form);

            fetch('my_dependents.php', {
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

        function handleDelete(id) {
            if (!confirm('Are you sure you want to delete this dependent?')) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('dependent_id', id);

            fetch('my_dependents.php', {
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>