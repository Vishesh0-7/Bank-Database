-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2025 at 12:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bank`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `AccountNumber` int(20) NOT NULL,
  `AccountType` varchar(20) NOT NULL,
  `BranchID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`AccountNumber`, `AccountType`, `BranchID`) VALUES
(101, 'Savings', 1),
(102, 'Checking', 2),
(103, 'Money Market Account', 3),
(104, 'Savings', 4),
(105, 'Checking', 5),
(132692, 'Savings', 2),
(389637, 'Savings', 2),
(769924, 'Checking', 2),
(877520, 'Savings', 2);

-- --------------------------------------------------------

--
-- Table structure for table `accountbalancehistory`
--

CREATE TABLE `accountbalancehistory` (
  `AccountNumber` int(11) NOT NULL,
  `LastAccessedDate` date NOT NULL,
  `Balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accountbalancehistory`
--

INSERT INTO `accountbalancehistory` (`AccountNumber`, `LastAccessedDate`, `Balance`) VALUES
(101, '2024-01-15', 5000),
(102, '2024-01-20', 1500),
(103, '2024-02-10', 3000),
(104, '2024-03-05', 2000),
(105, '2024-04-01', 4500),
(877520, '2024-12-10', 500),
(132692, '2024-12-10', 200),
(389637, '2024-12-10', 400);

-- --------------------------------------------------------

--
-- Table structure for table `accountcustomer`
--

CREATE TABLE `accountcustomer` (
  `AccountNumber` int(20) NOT NULL,
  `CustomerSSN` int(10) NOT NULL,
  `LastAccessedDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accountcustomer`
--

INSERT INTO `accountcustomer` (`AccountNumber`, `CustomerSSN`, `LastAccessedDate`) VALUES
(101, 123456789, '2024-01-15'),
(102, 234567890, '2024-01-20'),
(103, 345678901, '2024-02-10'),
(104, 456789012, '2024-03-05'),
(105, 567890123, '2024-04-01'),
(389637, 456777111, '2024-12-10'),
(769924, 456777111, '2024-12-10');

-- --------------------------------------------------------

--
-- Table structure for table `accounttype`
--

CREATE TABLE `accounttype` (
  `AccountType` varchar(20) NOT NULL,
  `Description` varchar(150) NOT NULL,
  `IntrestRate` float NOT NULL,
  `OverdraftFee` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounttype`
--

INSERT INTO `accounttype` (`AccountType`, `Description`, `IntrestRate`, `OverdraftFee`) VALUES
('Checking', 'Personal Checking Account', 0.5, 30),
('Loan Account', 'Student Savings Account', 1.5, 10),
('Money Market', 'Business Account', 1, 50),
('Savings', 'Personal Savings Account', 1.2, 25);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ID` int(11) NOT NULL,
  `Username` varchar(20) NOT NULL,
  `Password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ID`, `Username`, `Password`) VALUES
(1, 'admin', 'pass');

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `BranchId` int(10) NOT NULL,
  `Name` varchar(30) NOT NULL,
  `BuildingNo` int(11) NOT NULL,
  `Street` varchar(20) NOT NULL,
  `ZipCode` varchar(10) NOT NULL,
  `Assets` varchar(30) NOT NULL,
  `ManagerSSN` int(10) NOT NULL,
  `AsstManagerSSN` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`BranchId`, `Name`, `BuildingNo`, `Street`, `ZipCode`, `Assets`, `ManagerSSN`, `AsstManagerSSN`) VALUES
(1, 'Main Branch', 101, 'Main St', '12345', '5000000', 123456789, 234567890),
(2, 'North Branch', 202, 'North Ave', '23456', '3000000', 345678901, 456789012),
(3, 'South Branch', 303, 'South Rd', '34567', '4000000', 567890123, 678901234),
(4, 'East Branch', 404, 'East Blvd', '45678', '2000000', 789012345, 890123456),
(5, 'West Branch', 505, 'West Ln', '56789', '3500000', 901234567, 12345678);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `SSN` int(10) NOT NULL,
  `Name` varchar(30) NOT NULL,
  `House_No` int(12) NOT NULL,
  `Street` varchar(30) NOT NULL,
  `ZipCode` varchar(10) NOT NULL,
  `BranchId` int(10) NOT NULL,
  `PersonalBankerSSN` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`SSN`, `Name`, `House_No`, `Street`, `ZipCode`, `BranchId`, `PersonalBankerSSN`) VALUES
(123456789, 'Alice Johnson', 101, 'Maple St', '12345', 1, NULL),
(234567890, 'Bob Smith', 202, 'Oak Ave', '23456', 2, 234567890),
(345678901, 'Charlie Brown', 303, 'Pine Rd', '34567', 3, 345678901),
(456777111, 'Vishesh', 281, 'Lib', '07302', 2, 234567890),
(456789012, 'Daisy Adams', 404, 'Cedar Blvd', '45678', 4, 456789012),
(567890123, 'Edward Green', 505, 'Elm Ln', '56789', 5, 567890123);

-- --------------------------------------------------------

--
-- Table structure for table `customer_update_request`
--

CREATE TABLE `customer_update_request` (
  `CustomerSSN` varchar(11) DEFAULT NULL,
  `BranchID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cust_login`
--

CREATE TABLE `cust_login` (
  `Username` varchar(20) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `SSN` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cust_login`
--

INSERT INTO `cust_login` (`Username`, `Password`, `SSN`) VALUES
('vish', '2001', 456777111);

-- --------------------------------------------------------

--
-- Table structure for table `dependents`
--

CREATE TABLE `dependents` (
  `DependentID` int(11) NOT NULL,
  `EmployeeSSN` int(11) NOT NULL,
  `Name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dependents`
--

INSERT INTO `dependents` (`DependentID`, `EmployeeSSN`, `Name`) VALUES
(1, 234567890, 'John');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `SSN` int(10) NOT NULL,
  `Name` varchar(30) NOT NULL,
  `PhoneNumber` int(12) NOT NULL,
  `StartDate` date NOT NULL,
  `BranchID` int(11) NOT NULL,
  `ManagerSSN` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`SSN`, `Name`, `PhoneNumber`, `StartDate`, `BranchID`, `ManagerSSN`) VALUES
(123456789, 'Alice Johnson', 5551234, '2020-01-01', 1, NULL),
(234567890, 'Bob Smith', 5552345, '2021-02-01', 2, 123456789),
(345678901, 'Charlie Brown', 5553456, '2022-03-01', 3, 123456789),
(456789012, 'Daisy Adams', 5554567, '2023-04-01', 4, 234567890),
(567890123, 'Edward Green', 5555678, '2024-05-01', 5, 345678901);

-- --------------------------------------------------------

--
-- Table structure for table `employee_login`
--

CREATE TABLE `employee_login` (
  `Username` varchar(20) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `SSN` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan`
--

CREATE TABLE `loan` (
  `LoanNumber` int(11) NOT NULL,
  `LoanType` varchar(20) NOT NULL,
  `LoanAmount` int(11) NOT NULL,
  `MonthlyPayment` int(11) NOT NULL,
  `BranchID` int(11) NOT NULL,
  `LoanTime` int(11) NOT NULL,
  `Status` enum('APPROVED','REJECTED','IN PROGRESS','') NOT NULL DEFAULT 'IN PROGRESS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan`
--

INSERT INTO `loan` (`LoanNumber`, `LoanType`, `LoanAmount`, `MonthlyPayment`, `BranchID`, `LoanTime`, `Status`) VALUES
(1, 'Home Loan', 250000, 1500, 1, 18, 'APPROVED'),
(2, 'Vehicle Loan', 30000, 500, 2, 12, 'IN PROGRESS'),
(3, 'Personal Loan', 15000, 400, 3, 36, 'REJECTED'),
(4, 'Home Loan', 20000, 300, 4, 15, 'APPROVED'),
(5, 'Vehicle Loan', 50000, 700, 5, 6, 'APPROVED'),
(8, 'Personal Loan', 20000, 1010, 1, 21, 'IN PROGRESS'),
(10, 'Home Loan', 150000, 4597, 2, 36, 'APPROVED'),
(11, 'Home Loan', 15000, 1043, 2, 15, 'REJECTED'),
(12, 'Personal Loan', 1000, 86, 2, 12, 'APPROVED');

-- --------------------------------------------------------

--
-- Table structure for table `loancustomer`
--

CREATE TABLE `loancustomer` (
  `LoanNumber` int(11) NOT NULL,
  `LoanType` varchar(20) NOT NULL,
  `CustomerSSN` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loancustomer`
--

INSERT INTO `loancustomer` (`LoanNumber`, `LoanType`, `CustomerSSN`) VALUES
(1, 'Home Loan', 123456789),
(2, 'Vehicle Loan', 234567890),
(3, 'Personal Loan', 345678901),
(4, 'Home Loan', 456789012),
(5, 'Vehicle Loan', 567890123),
(10, 'Home Loan', 456777111),
(12, 'Personal Loan', 456777111);

-- --------------------------------------------------------

--
-- Table structure for table `loantype`
--

CREATE TABLE `loantype` (
  `LoanType` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loantype`
--

INSERT INTO `loantype` (`LoanType`) VALUES
('Home Loan'),
('Personal Loan'),
('Vehicle Loan');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `TransactionId` int(11) NOT NULL,
  `TransactionType` varchar(2) NOT NULL,
  `TransactionDate` date NOT NULL,
  `TransactionTime` time NOT NULL,
  `Amount` float NOT NULL,
  `Charge` float NOT NULL,
  `AccountNumber` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`TransactionId`, `TransactionType`, `TransactionDate`, `TransactionTime`, `Amount`, `Charge`, `AccountNumber`) VALUES
(1, 'WD', '2024-01-10', '10:00:00', 500, 0, 101),
(2, 'CD', '2024-01-15', '11:00:00', 200, 2.5, 102),
(3, 'CD', '2024-02-01', '12:00:00', 1000, 5, 103),
(4, 'WD', '2024-02-20', '13:00:00', 300, 0, 104),
(5, 'WD', '2024-03-01', '14:00:00', 400, 3, 105),
(11, 'CD', '2024-12-10', '09:22:28', 500, 0, 877520),
(12, 'CD', '2024-12-10', '09:23:10', 200, 0, 132692),
(13, 'CD', '2024-12-10', '09:26:42', 500, 0, 389637),
(14, 'CD', '2024-12-10', '13:20:50', 100, 0, 389637),
(15, 'WD', '2024-12-10', '13:21:12', 200, 0, 389637);

-- --------------------------------------------------------

--
-- Table structure for table `transactiontype`
--

CREATE TABLE `transactiontype` (
  `TransactionType` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactiontype`
--

INSERT INTO `transactiontype` (`TransactionType`) VALUES
('CD'),
('WD');

-- --------------------------------------------------------

--
-- Table structure for table `zipcode`
--

CREATE TABLE `zipcode` (
  `Zipcode` varchar(10) NOT NULL,
  `City` varchar(20) NOT NULL,
  `State` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zipcode`
--

INSERT INTO `zipcode` (`Zipcode`, `City`, `State`) VALUES
('07302', 'Jersey City', 'NJ'),
('10001', 'New York', 'NY'),
('20001', 'Washington', 'DC'),
('30301', 'Atlanta', 'GA'),
('33101', 'Miami', 'FL'),
('48201', 'Detroit', 'MI'),
('60601', 'Chicago', 'IL'),
('77001', 'Houston', 'TX'),
('90001', 'Los Angeles', 'CA'),
('94101', 'San Francisco', 'CA');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`AccountNumber`),
  ADD KEY `account_ibfk_1` (`BranchID`),
  ADD KEY `account_ibfk_2` (`AccountType`);

--
-- Indexes for table `accountbalancehistory`
--
ALTER TABLE `accountbalancehistory`
  ADD KEY `accountbalancehistory_ibfk_1` (`AccountNumber`);

--
-- Indexes for table `accountcustomer`
--
ALTER TABLE `accountcustomer`
  ADD KEY `accountcustomer_ibfk_1` (`AccountNumber`),
  ADD KEY `accountcustomer_ibfk_2` (`CustomerSSN`);

--
-- Indexes for table `accounttype`
--
ALTER TABLE `accounttype`
  ADD PRIMARY KEY (`AccountType`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`BranchId`),
  ADD KEY `branch_ibfk_1` (`ManagerSSN`),
  ADD KEY `branch_ibfk_2` (`AsstManagerSSN`),
  ADD KEY `branch_ibfk_3` (`ZipCode`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`SSN`),
  ADD KEY `customer_ibfk_1` (`PersonalBankerSSN`),
  ADD KEY `customer_ibfk_2` (`ZipCode`);

--
-- Indexes for table `cust_login`
--
ALTER TABLE `cust_login`
  ADD PRIMARY KEY (`Username`),
  ADD UNIQUE KEY `C_SSN` (`SSN`);

--
-- Indexes for table `dependents`
--
ALTER TABLE `dependents`
  ADD PRIMARY KEY (`DependentID`),
  ADD KEY `dependents_ibfk_1` (`EmployeeSSN`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`SSN`),
  ADD KEY `employee_ibfk_1` (`ManagerSSN`);

--
-- Indexes for table `employee_login`
--
ALTER TABLE `employee_login`
  ADD PRIMARY KEY (`Username`),
  ADD UNIQUE KEY `E_SSN` (`SSN`);

--
-- Indexes for table `loan`
--
ALTER TABLE `loan`
  ADD PRIMARY KEY (`LoanNumber`),
  ADD KEY `BranchID` (`BranchID`),
  ADD KEY `LoanType` (`LoanType`);

--
-- Indexes for table `loancustomer`
--
ALTER TABLE `loancustomer`
  ADD KEY `LoanType` (`LoanType`),
  ADD KEY `loancustomer_ibfk_1` (`LoanNumber`),
  ADD KEY `loancustomer_ibfk_2` (`CustomerSSN`);

--
-- Indexes for table `loantype`
--
ALTER TABLE `loantype`
  ADD PRIMARY KEY (`LoanType`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`TransactionId`),
  ADD KEY `TransactionType` (`TransactionType`),
  ADD KEY `transaction_ibfk_1` (`AccountNumber`);

--
-- Indexes for table `transactiontype`
--
ALTER TABLE `transactiontype`
  ADD PRIMARY KEY (`TransactionType`);

--
-- Indexes for table `zipcode`
--
ALTER TABLE `zipcode`
  ADD PRIMARY KEY (`Zipcode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `BranchId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dependents`
--
ALTER TABLE `dependents`
  MODIFY `DependentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan`
--
ALTER TABLE `loan`
  MODIFY `LoanNumber` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `TransactionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `account_ibfk_1` FOREIGN KEY (`BranchID`) REFERENCES `branch` (`BranchId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_2` FOREIGN KEY (`AccountType`) REFERENCES `accounttype` (`AccountType`) ON DELETE CASCADE;

--
-- Constraints for table `accountbalancehistory`
--
ALTER TABLE `accountbalancehistory`
  ADD CONSTRAINT `accountbalancehistory_ibfk_1` FOREIGN KEY (`AccountNumber`) REFERENCES `account` (`AccountNumber`) ON DELETE CASCADE;

--
-- Constraints for table `accountcustomer`
--
ALTER TABLE `accountcustomer`
  ADD CONSTRAINT `accountcustomer_ibfk_1` FOREIGN KEY (`AccountNumber`) REFERENCES `account` (`AccountNumber`) ON DELETE CASCADE,
  ADD CONSTRAINT `accountcustomer_ibfk_2` FOREIGN KEY (`CustomerSSN`) REFERENCES `customer` (`SSN`) ON DELETE CASCADE;

--
-- Constraints for table `branch`
--
ALTER TABLE `branch`
  ADD CONSTRAINT `branch_ibfk_1` FOREIGN KEY (`ManagerSSN`) REFERENCES `employee` (`SSN`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_ibfk_2` FOREIGN KEY (`AsstManagerSSN`) REFERENCES `employee` (`SSN`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_ibfk_3` FOREIGN KEY (`ZipCode`) REFERENCES `zipcode` (`Zipcode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`PersonalBankerSSN`) REFERENCES `employee` (`SSN`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_ibfk_2` FOREIGN KEY (`ZipCode`) REFERENCES `zipcode` (`Zipcode`) ON DELETE CASCADE;

--
-- Constraints for table `cust_login`
--
ALTER TABLE `cust_login`
  ADD CONSTRAINT `cust_login_ibfk_1` FOREIGN KEY (`SSN`) REFERENCES `customer` (`SSN`) ON DELETE CASCADE;

--
-- Constraints for table `dependents`
--
ALTER TABLE `dependents`
  ADD CONSTRAINT `dependents_ibfk_1` FOREIGN KEY (`EmployeeSSN`) REFERENCES `employee` (`SSN`) ON DELETE CASCADE;

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`ManagerSSN`) REFERENCES `employee` (`SSN`) ON DELETE CASCADE;

--
-- Constraints for table `employee_login`
--
ALTER TABLE `employee_login`
  ADD CONSTRAINT `employee_login_ibfk_1` FOREIGN KEY (`SSN`) REFERENCES `employee` (`SSN`) ON DELETE CASCADE;

--
-- Constraints for table `loan`
--
ALTER TABLE `loan`
  ADD CONSTRAINT `loan_ibfk_1` FOREIGN KEY (`BranchID`) REFERENCES `branch` (`BranchId`),
  ADD CONSTRAINT `loan_ibfk_2` FOREIGN KEY (`LoanType`) REFERENCES `loantype` (`LoanType`);

--
-- Constraints for table `loancustomer`
--
ALTER TABLE `loancustomer`
  ADD CONSTRAINT `loancustomer_ibfk_1` FOREIGN KEY (`LoanNumber`) REFERENCES `loan` (`LoanNumber`) ON DELETE CASCADE,
  ADD CONSTRAINT `loancustomer_ibfk_2` FOREIGN KEY (`CustomerSSN`) REFERENCES `customer` (`SSN`) ON DELETE CASCADE,
  ADD CONSTRAINT `loancustomer_ibfk_3` FOREIGN KEY (`LoanType`) REFERENCES `loantype` (`LoanType`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`AccountNumber`) REFERENCES `account` (`AccountNumber`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`TransactionType`) REFERENCES `transactiontype` (`TransactionType`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
