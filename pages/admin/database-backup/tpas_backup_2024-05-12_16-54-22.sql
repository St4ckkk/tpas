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
  PRIMARY KEY (`assistantId`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO assistants VALUES("2","Keyan Andy","Delgado","keyanandydelgado@gmail.com","09262408442","861407","2024-04-25 21:09:36","0","","$2y$10$ws6xuSP49gkgnIf0T1gKrO41rjiIifbOm.H092C8rjenvQj0mlcTC","fcd33f875963c0d1233e8d943b344124.png");
INSERT INTO assistants VALUES("3","Keyan Andy","Delgado Two","keyanandydelgadotwo@gmail.com","09613247861","602732","2024-05-10 18:18:59","0","","$2y$10$hMnI6GN4FxUoAkS6o6YtvurarjNeiqGQWxzMPYS0XZkQn9SXNcnPK","../uploaded_files/6e54bc349674d6deba65f32823814868.jpg");

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
) ENGINE=InnoDB AUTO_INCREMENT=542 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO logs VALUES("540","123123","qweqwe","assistant","2024-05-12 08:59:22");
INSERT INTO logs VALUES("541","123123","qweqwe","assistant","2024-05-12 08:59:25");

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

INSERT INTO reminders VALUES("9","HOTDOG","qweqwe","2024-05-01 00:00:00","","doctor","1","2","2024-05-01 00:13:40","2024-05-01 00:13:40");
INSERT INTO reminders VALUES("11","CLEAN MY DESK","qwkenoqwe","2024-05-02 00:00:00","","doctor","1","1","2024-05-01 00:20:07","2024-05-01 00:20:07");
INSERT INTO reminders VALUES("12","knq;nq","qwe lf","2024-05-01 00:00:00","","patient","14","3","2024-05-01 00:22:37","2024-05-01 00:22:37");
INSERT INTO reminders VALUES("14","fuck you bitch","PUTANGINAMO","2024-05-01 00:00:00","2","doctor","1","4","2024-05-01 00:35:39","2024-05-01 00:35:39");

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tb_patients VALUES("22","","Keyan Andy","Delgado","","tpas052202@gmail.com","","09280693642","","$2y$10$sc/zr/Oq9e2LViOFUxmzLe36tYZlWFUU4BcJvDma7sdAygDJjyp86","Verified","MkuadCTbZO+jHmEUYvN11lpuS3hjamVrV0tqZmQ1cHc3M25RZHc9PQ==","2024-05-11 23:20:57","2024-05-11 23:21:16","0","","");

