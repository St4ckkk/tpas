-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2023 at 08:43 AM
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
  `status` varchar(10) NOT NULL DEFAULT 'process',
  `pregnancyWeek` varchar(255) DEFAULT NULL,
  `weight` varchar(255) DEFAULT NULL,
  `bloodPressure` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(123123, '123', 1234, 'Doctor', 'NGGA', 'ngga@gmail.com', '09262408442', 'ngga@gmail.com', '2023-12-11', 'Obstetrician'),
(12345678, '123', 2468, 'Doctor', 'Kwak-Kwak', 'Poblacion, Tupi, South Cotabato', '123456789', 'kwakwak@gmail.com', '2023-12-11', 'Pulmonologist'),
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

-- --------------------------------------------------------

--
-- Table structure for table `tbappointment`
--

CREATE TABLE `tbappointment` (
  `appId` int(11) NOT NULL,
  `philhealthId` bigint(20) NOT NULL,
  `scheduleId` int(11) NOT NULL,
  `appSymptom` text NOT NULL,
  `currentMedications` varchar(255) DEFAULT NULL,
  `symptomDuration` varchar(50) DEFAULT NULL,
  `allergies` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'process',
  `additionalInfo` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `tbappointment`
--
ALTER TABLE `tbappointment`
  ADD PRIMARY KEY (`appId`),
  ADD KEY `scheduleId` (`scheduleId`),
  ADD KEY `fk_tbappointment_patient` (`philhealthId`);

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
  MODIFY `appId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `doctormessages`
--
ALTER TABLE `doctormessages`
  MODIFY `messageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `doctorschedule`
--
ALTER TABLE `doctorschedule`
  MODIFY `scheduleId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `prenatalprescription`
--
ALTER TABLE `prenatalprescription`
  MODIFY `prescriptionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tbappointment`
--
ALTER TABLE `tbappointment`
  MODIFY `appId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbprescription`
--
ALTER TABLE `tbprescription`
  MODIFY `prescriptionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `usermessages`
--
ALTER TABLE `usermessages`
  MODIFY `messageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
-- Constraints for table `tbappointment`
--
ALTER TABLE `tbappointment`
  ADD CONSTRAINT `fk_tbappointment_patient` FOREIGN KEY (`philhealthId`) REFERENCES `patient` (`philhealthId`),
  ADD CONSTRAINT `tbappointment_ibfk_1` FOREIGN KEY (`scheduleId`) REFERENCES `doctorschedule` (`scheduleId`);

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
