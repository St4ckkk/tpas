-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2023 at 08:10 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nena`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appId` int(3) NOT NULL,
  `philhealthId` bigint(12) NOT NULL,
  `scheduleId` int(10) NOT NULL,
  `appSymptom` varchar(100) NOT NULL,
  `appComment` varchar(100) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'process'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appId`, `philhealthId`, `scheduleId`, `appSymptom`, `appComment`, `status`) VALUES
(94, 1234, 66, 'Cough', 'With bloood', 'process'),
(95, 123, 67, 'QJWE0QJ', 'HQIEHQIWE', 'process');

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `icDoctor` bigint(12) NOT NULL,
  `password` varchar(20) NOT NULL,
  `doctorId` int(3) NOT NULL,
  `doctorFirstName` varchar(50) NOT NULL,
  `doctorLastName` varchar(50) NOT NULL,
  `doctorAddress` varchar(100) NOT NULL,
  `doctorPhone` varchar(15) NOT NULL,
  `doctorEmail` varchar(20) NOT NULL,
  `doctorDOB` date NOT NULL,
  `doctorRole` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`icDoctor`, `password`, `doctorId`, `doctorFirstName`, `doctorLastName`, `doctorAddress`, `doctorPhone`, `doctorEmail`, `doctorDOB`, `doctorRole`) VALUES
(123, '123', 321, 'Abdul', 'Marot', 'Tuka', '09262408442', 'jimbo@gmail.com', '2023-12-04', 'Obstetrician'),
(2467, '123', 2468, 'Jimbo', 'Ulama', 'Brgy. Ambalgan Poblacion Of Sto Nino South Cotabato', '09262408442', 'admin@gmail.com', '2023-12-05', 'Pulmonologist'),
(123456789, '123', 123, 'Super', 'Admin', 'Tupi', '09262408442', 'nena@gmail.com', '2023-12-12', 'superAdmin');

-- --------------------------------------------------------

--
-- Table structure for table `doctormessages`
--

CREATE TABLE `doctormessages` (
  `messageId` int(11) NOT NULL,
  `senderId` bigint(20) DEFAULT NULL,
  `receiverId` bigint(20) DEFAULT NULL,
  `messageContent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctormessages`
--

INSERT INTO `doctormessages` (`messageId`, `senderId`, `receiverId`, `messageContent`, `timestamp`) VALUES
(31, 2467, 1234, 'qwe', '2023-12-04 18:00:18'),
(32, 123, 123, 'qweqwe', '2023-12-04 18:11:55'),
(34, 2467, 1234, 'qweqwe', '2023-12-04 19:00:32'),
(38, 2467, 1234, 'nigger', '2023-12-04 19:08:57'),
(39, 123, 123, 'nigger ako', '2023-12-04 19:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `doctorschedule`
--

CREATE TABLE `doctorschedule` (
  `scheduleId` int(11) NOT NULL,
  `scheduleDate` date NOT NULL,
  `scheduleDay` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `bookAvail` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `doctorId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `doctorschedule`
--

INSERT INTO `doctorschedule` (`scheduleId`, `scheduleDate`, `scheduleDay`, `startTime`, `endTime`, `bookAvail`, `doctorId`) VALUES
(66, '2023-12-05', 'Tuesday', '01:05:00', '12:00:00', 'notavail', 2468),
(67, '2023-12-05', 'Tuesday', '02:00:00', '05:00:00', 'notavail', 321);

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `philhealthId` bigint(12) NOT NULL,
  `password` varchar(20) NOT NULL,
  `patientFirstName` varchar(20) NOT NULL,
  `patientLastName` varchar(20) NOT NULL,
  `patientMaritialStatus` varchar(10) NOT NULL,
  `patientDOB` date NOT NULL,
  `patientGender` varchar(10) NOT NULL,
  `patientAddress` varchar(100) NOT NULL,
  `patientPhone` varchar(15) NOT NULL,
  `patientEmail` varchar(100) NOT NULL,
  `appointmentType` enum('tb','prenatal') NOT NULL DEFAULT 'tb'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`philhealthId`, `password`, `patientFirstName`, `patientLastName`, `patientMaritialStatus`, `patientDOB`, `patientGender`, `patientAddress`, `patientPhone`, `patientEmail`, `appointmentType`) VALUES
(123, '123', 'test', 'patient', '', '1996-01-17', 'female', '', '', 'test@gmail.com', 'prenatal'),
(1234, '123', 'test2', 'patient2', '', '2000-03-16', 'male', '', '', 'test2@gmail.com', 'tb');

-- --------------------------------------------------------

--
-- Table structure for table `prenatalprescription`
--

CREATE TABLE `prenatalprescription` (
  `prescriptionId` int(11) NOT NULL,
  `philhealthId` bigint(20) DEFAULT NULL,
  `medication` varchar(255) NOT NULL,
  `icDoctor` bigint(20) DEFAULT NULL,
  `dosage` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `instructions` text NOT NULL,
  `prescriptionDate` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prenatalprescription`
--

INSERT INTO `prenatalprescription` (`prescriptionId`, `philhealthId`, `medication`, `icDoctor`, `dosage`, `comment`, `instructions`, `prescriptionDate`) VALUES
(28, 123, 'NIGGER', 123, 'NIGGER', 'NIGGER', 'NIGGER', '2023-12-05');

-- --------------------------------------------------------

--
-- Table structure for table `tbprescription`
--

CREATE TABLE `tbprescription` (
  `prescriptionId` int(11) NOT NULL,
  `philhealthId` bigint(20) DEFAULT NULL,
  `icDoctor` bigint(20) DEFAULT NULL,
  `medication` varchar(255) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `comment` text NOT NULL,
  `instructions` text NOT NULL,
  `prescriptionDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbprescription`
--

INSERT INTO `tbprescription` (`prescriptionId`, `philhealthId`, `icDoctor`, `medication`, `dosage`, `comment`, `instructions`, `prescriptionDate`) VALUES
(4, 1234, 2467, 'NIGGER', 'NIGGER', 'NIGGER', 'NIGGE', '2023-12-04 18:31:43');

-- --------------------------------------------------------

--
-- Table structure for table `usermessages`
--

CREATE TABLE `usermessages` (
  `messageId` int(11) NOT NULL,
  `senderId` bigint(20) DEFAULT NULL,
  `receiverId` bigint(20) DEFAULT NULL,
  `messageContent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usermessages`
--

INSERT INTO `usermessages` (`messageId`, `senderId`, `receiverId`, `messageContent`, `timestamp`) VALUES
(15, 1234, 123456789, 'TEST', '2023-12-04 16:47:56'),
(16, 1234, 2467, 'qweqwe', '2023-12-04 17:50:58'),
(17, 123, 123, 'qweqwe', '2023-12-04 18:11:46'),
(18, 1234, 2467, 'qwe', '2023-12-04 18:57:09'),
(19, 123, 123, '123', '2023-12-04 18:57:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appId`),
  ADD UNIQUE KEY `scheduleId_2` (`scheduleId`),
  ADD KEY `patientIc` (`philhealthId`),
  ADD KEY `scheduleId` (`scheduleId`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`icDoctor`),
  ADD KEY `idx_doctorId` (`doctorId`);

--
-- Indexes for table `doctormessages`
--
ALTER TABLE `doctormessages`
  ADD PRIMARY KEY (`messageId`),
  ADD KEY `senderId` (`senderId`),
  ADD KEY `receiverId` (`receiverId`);

--
-- Indexes for table `doctorschedule`
--
ALTER TABLE `doctorschedule`
  ADD PRIMARY KEY (`scheduleId`),
  ADD KEY `doctorId` (`doctorId`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`philhealthId`);

--
-- Indexes for table `prenatalprescription`
--
ALTER TABLE `prenatalprescription`
  ADD PRIMARY KEY (`prescriptionId`),
  ADD KEY `philhealthId` (`philhealthId`),
  ADD KEY `fk_icDoctor` (`icDoctor`);

--
-- Indexes for table `tbprescription`
--
ALTER TABLE `tbprescription`
  ADD PRIMARY KEY (`prescriptionId`),
  ADD KEY `philhealthId` (`philhealthId`),
  ADD KEY `icDoctor` (`icDoctor`);

--
-- Indexes for table `usermessages`
--
ALTER TABLE `usermessages`
  ADD PRIMARY KEY (`messageId`),
  ADD KEY `senderId` (`senderId`),
  ADD KEY `receiverId` (`receiverId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `doctormessages`
--
ALTER TABLE `doctormessages`
  MODIFY `messageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `doctorschedule`
--
ALTER TABLE `doctorschedule`
  MODIFY `scheduleId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `prenatalprescription`
--
ALTER TABLE `prenatalprescription`
  MODIFY `prescriptionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tbprescription`
--
ALTER TABLE `tbprescription`
  MODIFY `prescriptionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `usermessages`
--
ALTER TABLE `usermessages`
  MODIFY `messageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_4` FOREIGN KEY (`philhealthId`) REFERENCES `patient` (`philhealthId`),
  ADD CONSTRAINT `appointment_ibfk_5` FOREIGN KEY (`scheduleId`) REFERENCES `doctorschedule` (`scheduleId`);

--
-- Constraints for table `doctormessages`
--
ALTER TABLE `doctormessages`
  ADD CONSTRAINT `doctormessages_ibfk_1` FOREIGN KEY (`senderId`) REFERENCES `doctor` (`icDoctor`),
  ADD CONSTRAINT `doctormessages_ibfk_2` FOREIGN KEY (`receiverId`) REFERENCES `patient` (`philhealthId`);

--
-- Constraints for table `doctorschedule`
--
ALTER TABLE `doctorschedule`
  ADD CONSTRAINT `doctorschedule_ibfk_1` FOREIGN KEY (`doctorId`) REFERENCES `doctor` (`doctorId`);

--
-- Constraints for table `prenatalprescription`
--
ALTER TABLE `prenatalprescription`
  ADD CONSTRAINT `fk_icDoctor` FOREIGN KEY (`icDoctor`) REFERENCES `doctor` (`icDoctor`),
  ADD CONSTRAINT `prenatalprescription_ibfk_1` FOREIGN KEY (`philhealthId`) REFERENCES `patient` (`philhealthId`);

--
-- Constraints for table `tbprescription`
--
ALTER TABLE `tbprescription`
  ADD CONSTRAINT `tbprescription_ibfk_1` FOREIGN KEY (`philhealthId`) REFERENCES `patient` (`philhealthId`),
  ADD CONSTRAINT `tbprescription_ibfk_2` FOREIGN KEY (`icDoctor`) REFERENCES `doctor` (`icDoctor`);

--
-- Constraints for table `usermessages`
--
ALTER TABLE `usermessages`
  ADD CONSTRAINT `usermessages_ibfk_1` FOREIGN KEY (`senderId`) REFERENCES `patient` (`philhealthId`),
  ADD CONSTRAINT `usermessages_ibfk_2` FOREIGN KEY (`receiverId`) REFERENCES `doctor` (`icDoctor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
