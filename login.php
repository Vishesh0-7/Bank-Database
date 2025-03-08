<?php
session_start();
include 'db_connect.php'; // Include database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loginType = $_POST['login_type'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($loginType == 'customer') {
        // Customer login: check username and password
        $query = "SELECT * FROM Cust_Login WHERE Username = ? AND Password = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Successful login, store username and redirect to customer dashboard
            $_SESSION['username'] = $username;
            header("Location: customer_dashboard.php"); // Redirect to the customer dashboard
            exit();
        } else {
            echo "Invalid Username or Password.";
        }

        $stmt->close();
    } elseif ($loginType == 'employee') {
        // Employee login: check username and password
        $query = "SELECT * FROM Employee_Login WHERE Username = ? AND Password = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Successful login, store username and redirect to employee dashboard
            $_SESSION['username'] = $username;
            header("Location: employee_dashboard.php"); // Redirect to the employee dashboard
            exit();
        } else {
            echo "Invalid Username or Password.";
        }
        $stmt->close();
    }   
    elseif ($loginType === 'admin') {
            $query = "SELECT * FROM Admin WHERE Username = ? AND Password = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                $_SESSION['username'] = $username;
                header("Location: admin_dashboard.php");
                exit();
            } else {
                echo "Invalid Admin credentials.";
            }
        

        $stmt->close();
        }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Bank Login</h2>
        <form action="login.php" method="POST">
            <label for="loginType">Login Type:</label>
            <select name="loginType" id="loginType" required>
                <option value="customer">Customer</option>
                <option value="employee">Employee</option>
            </select>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <a href="signup.php">Don't have an account? Sign up here</a>
    </div>
</body>
</html>
