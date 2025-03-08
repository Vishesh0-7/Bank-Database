<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['ssn'])) {
    try {
        $conn->begin_transaction();
        
        $ssn = $_GET['ssn'];
        
        // Delete from employee_login first (due to foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM employee_login WHERE SSN = ?");
        $stmt->bind_param("i", $ssn);
        $stmt->execute();
        
        // Delete from employee table
        $stmt = $conn->prepare("DELETE FROM employee WHERE SSN = ?");
        $stmt->bind_param("i", $ssn);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Employee deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting employee: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php");
exit();
?>