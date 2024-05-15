DROP TABLE IF EXISTS appointments;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `scheduleId` int(11) DEFAULT NULL,
  `patientId` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `endTime` time DEFAULT NULL,
  `appointment_type` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Confirmed','Pending','Cancelled','Processing','Completed','Reschedule') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `fk_patientId` (`patientId`),
  KEY `fk_schedule` (`scheduleId`),
  CONSTRAINT `fk_patientId` FOREIGN KEY (`patientId`) REFERENCES `tb_patients` (`patientId`),
  CONSTRAINT `fk_schedule` FOREIGN KEY (`scheduleId`) REFERENCES `schedule` (`scheduleId`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS assistants;
CREATE TABLE `assistants` (
  `assistantId` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phoneNumber` varchar(15) DEFAULT NULL,
  `accountNumber` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `lock_until` datetime DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`assistantId`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO assistants VALUES("6","Keyan Andy","Delgado","keyanandydelgadotwo@gmail.com","","v9PdHP4ACRRO7OmH0HVF/lE3UmJsbVpack1MdG11QWExUlIzQ0E9PQ==","2024-05-13 08:15:40","1","","$2y$10$1ZdVog8oJeUd5Y48sADrDuc6/tCyx0WgRRirvEkF2jyEkGpMYXLMq","","2024-05-13 09:01:00");

DROP TABLE IF EXISTS doctor;
CREATE TABLE `doctor` (
  `id` int(12) NOT NULL,
  `password` varchar(20) NOT NULL,
  `doctorId` bigint(12) NOT NULL,
  `doctorFirstName` varchar(50) NOT NULL,
  `doctorLastName` varchar(50) NOT NULL,
  `doctorAddress` varchar(100) NOT NULL,
  `doctorPhone` varchar(15) NOT NULL,
  `email` varchar(20) NOT NULL,
  `doctorDOB` date NOT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `lock_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO doctor VALUES("1","123","123","Test","Admin","Tupi","09262408442","admin@gmail.com","2023-12-12","0","2024-05-11 17:30:48");
INSERT INTO doctor VALUES("2","123","1234","admin ","two","","","admin2@gmail.com","0000-00-00","0","");

DROP TABLE IF EXISTS logs;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountNumber` varchar(255) DEFAULT NULL,
  `actionDescription` text NOT NULL,
  `userType` varchar(100) NOT NULL,
  `dateTime` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_datetime` (`dateTime`)
) ENGINE=InnoDB AUTO_INCREMENT=569 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO logs VALUES("568","466340","Failed login attempt - no account found","assistant","2024-05-13 09:50:47");

DROP TABLE IF EXISTS notifications;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `userType` enum('assistant','patient','admin') NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS reminders;
CREATE TABLE `reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` datetime NOT NULL,
  `creatorId` int(11) DEFAULT NULL,
  `recipient_type` enum('assistant','patient','doctor') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS schedule;
CREATE TABLE `schedule` (
  `scheduleId` int(11) NOT NULL AUTO_INCREMENT,
  `doctorId` int(12) NOT NULL,
  `startDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `status` varchar(50) DEFAULT 'available',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`scheduleId`),
  KEY `fk_doctorId` (`doctorId`),
  CONSTRAINT `fk_doctorId` FOREIGN KEY (`doctorId`) REFERENCES `doctor` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO schedule VALUES("19","1","2024-05-15","07:00:00","17:00:00","available","2024-04-29 01:13:07");
INSERT INTO schedule VALUES("20","1","2024-05-08","07:30:00","02:30:00","not-available","2024-05-06 14:29:45");

DROP TABLE IF EXISTS system_settings;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(100) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO system_settings VALUES("1","TPAS","ebc7c8b276e75677d6947f196dfb6bcb","2024-05-12 22:24:11","2024-05-12 22:31:21");

DROP TABLE IF EXISTS tb_patients;
CREATE TABLE `tb_patients` (
  `patientId` int(11) NOT NULL AUTO_INCREMENT,
  `philhealthId` varchar(255) DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phoneno` varchar(15) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `accountStatus` varchar(255) DEFAULT 'Pending',
  `account_num` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `lock_until` time DEFAULT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`patientId`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tb_patients VALUES("26","","Keyan Andy","Delgado","","keyanandydelgado@gmail.com","","09280693642","","$2y$10$HE2sZFWoFPiIXhKYaeq8GOc3jcKn0flqECgBNNNWEd.5/iPHpGMEK","Pending","pN9YDqqdP2lOGQD1h1NpdE9MVnNhWnB2TWJBUk5jUzVTNmw3MGc9PQ==","2024-05-13 07:56:07","2024-05-13 07:56:07","0","","");

