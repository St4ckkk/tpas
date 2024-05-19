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
  `status` enum('Confirmed','Pending','Cancelled','Completed','Request-for-reschedule','Request-for-cancel','Request-confirmed','Request-denied','Denied','On-Going') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `fk_patientId` (`patientId`),
  KEY `fk_schedule` (`scheduleId`),
  CONSTRAINT `fk_patientId` FOREIGN KEY (`patientId`) REFERENCES `tb_patients` (`patientId`),
  CONSTRAINT `fk_schedule` FOREIGN KEY (`scheduleId`) REFERENCES `schedule` (`scheduleId`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO appointments VALUES("45","26","30","Test","Patient","09280693642","testpatient1@gmail.com","2024-05-22","09:33:00","10:33:00","emergency","qweq","Confirmed","2024-05-20 02:30:34","2024-05-20 02:58:07");

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO assistants VALUES("7","Jener Kevin","Ogatis","keyanandydelgadotwo@gmail.com","09922523227","ATgh3wtCTOMPopC2rfCIvUI5ci9mZ3dmUW5RRjNLR0ROM1Y5K3c9PQ==","2024-05-13 13:36:49","0","","$2y$10$HE2sZFWoFPiIXhKYaeq8GOc3jcKn0flqECgBNNNWEd.5/iPHpGMEK","../uploaded_files/71736845ed376e6de8b44acdba03474f.png","2024-05-13 15:30:07");

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
  `profile_image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO doctor VALUES("1","123","123","Test","Admin","Tupi","09262408442","admin@gmail.com","2023-12-12","0","2024-05-17 18:14:56","../uploaded_files/2f8f096eb0f6c2f3231a7feaa56af9c2.jpg");

DROP TABLE IF EXISTS logs;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountNumber` varchar(255) DEFAULT NULL,
  `actionDescription` text NOT NULL,
  `userType` varchar(100) NOT NULL,
  `dateTime` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_datetime` (`dateTime`)
) ENGINE=InnoDB AUTO_INCREMENT=680 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO logs VALUES("638","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","registered on 2024-05-18 5:53: PM","user","2024-05-18 05:53:00");
INSERT INTO logs VALUES("639","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-18 5:53 PM","user","2024-05-18 05:53:00");
INSERT INTO logs VALUES("640","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-18 6:10 PM","user","2024-05-18 06:10:00");
INSERT INTO logs VALUES("641","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-18 6:11 PM","user","2024-05-18 06:11:00");
INSERT INTO logs VALUES("642","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-18 7:54 PM","user","2024-05-18 07:54:00");
INSERT INTO logs VALUES("643","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-18 8:00 PM","user","2024-05-18 08:00:00");
INSERT INTO logs VALUES("644","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 12:37 AM","user","2024-05-19 12:37:00");
INSERT INTO logs VALUES("645","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-19 12:47 AM","user","2024-05-19 12:47:00");
INSERT INTO logs VALUES("646","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 12:47 AM","user","2024-05-19 12:47:00");
INSERT INTO logs VALUES("647","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 12:51 AM","user","2024-05-19 12:51:00");
INSERT INTO logs VALUES("648","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-19 12:51 AM","user","2024-05-19 12:51:00");
INSERT INTO logs VALUES("649","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 1:21 AM","user","2024-05-19 01:21:00");
INSERT INTO logs VALUES("650","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","tried to book an appointment on a date they already have an appointment on 2024-05-19 1:22 AM","user","2024-05-19 01:22:00");
INSERT INTO logs VALUES("651","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-19 1:23 AM","user","2024-05-19 01:23:00");
INSERT INTO logs VALUES("652","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 8:59 AM","user","2024-05-19 08:59:00");
INSERT INTO logs VALUES("653","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-19 9:01 AM","user","2024-05-19 09:01:00");
INSERT INTO logs VALUES("654","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 8:11 PM","user","2024-05-19 08:11:00");
INSERT INTO logs VALUES("655","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 8:24 PM","user","2024-05-19 08:24:00");
INSERT INTO logs VALUES("656","","Failed login attempt - no account found","assistant","2024-05-19 20:50:35");
INSERT INTO logs VALUES("657","ATgh3wtCTOMPopC2rfCIvUI5ci9mZ3dmUW5RRjNLR0ROM1Y5K3c9PQ==","assistant logged out on 2024-05-19 8:53 PM","assistant","2024-05-19 20:53:32");
INSERT INTO logs VALUES("658","","Attempt to login with non-existent account details","unknown","2024-05-19 09:29:00");
INSERT INTO logs VALUES("659","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 9:29 PM","user","2024-05-19 09:29:00");
INSERT INTO logs VALUES("660","ATgh3wtCTOMPopC2rfCIvUI5ci9mZ3dmUW5RRjNLR0ROM1Y5K3c9PQ==","assistant logged out on 2024-05-19 9:30 PM","assistant","2024-05-19 21:30:29");
INSERT INTO logs VALUES("661","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-19 10:52 PM","user","2024-05-19 10:52:00");
INSERT INTO logs VALUES("662","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-20 12:32 AM","user","2024-05-20 12:32:00");
INSERT INTO logs VALUES("663","KO2UVh9TOb5t0OEVXT65qE5oNnVnUHNkZ2IvSTBKZGtKaURtelE9PQ==","registered on 2024-05-20 12:34: AM","user","2024-05-20 12:34:00");
INSERT INTO logs VALUES("664","KO2UVh9TOb5t0OEVXT65qE5oNnVnUHNkZ2IvSTBKZGtKaURtelE9PQ==","Successfully logged in on 2024-05-20 1:08 AM","user","2024-05-20 01:08:00");
INSERT INTO logs VALUES("665","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-20 1:10 AM","user","2024-05-20 01:10:00");
INSERT INTO logs VALUES("666","KO2UVh9TOb5t0OEVXT65qE5oNnVnUHNkZ2IvSTBKZGtKaURtelE9PQ==","Successfully logged in on 2024-05-20 1:12 AM","user","2024-05-20 01:12:00");
INSERT INTO logs VALUES("667","KO2UVh9TOb5t0OEVXT65qE5oNnVnUHNkZ2IvSTBKZGtKaURtelE9PQ==","tried to book an appointment on a date they already have an appointment on 2024-05-20 1:13 AM","user","2024-05-20 01:13:00");
INSERT INTO logs VALUES("668","KO2UVh9TOb5t0OEVXT65qE5oNnVnUHNkZ2IvSTBKZGtKaURtelE9PQ==","booked an appointment on 2024-05-20 1:15 AM","user","2024-05-20 01:15:00");
INSERT INTO logs VALUES("669","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-20 1:15 AM","user","2024-05-20 01:15:00");
INSERT INTO logs VALUES("670","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","tried to book an appointment on a time slot that is already taken 2024-05-20 1:16 AM","user","2024-05-20 01:16:00");
INSERT INTO logs VALUES("671","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-20 1:16 AM","user","2024-05-20 01:16:00");
INSERT INTO logs VALUES("672","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-20 1:21 AM","user","2024-05-20 01:21:00");
INSERT INTO logs VALUES("673","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-20 1:23 AM","user","2024-05-20 01:23:00");
INSERT INTO logs VALUES("674","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-20 1:36 AM","user","2024-05-20 01:36:00");
INSERT INTO logs VALUES("675","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-20 1:47 AM","user","2024-05-20 01:47:00");
INSERT INTO logs VALUES("676","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-20 2:19 AM","user","2024-05-20 02:19:00");
INSERT INTO logs VALUES("677","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","Successfully logged in on 2024-05-20 2:19 AM","user","2024-05-20 02:19:00");
INSERT INTO logs VALUES("678","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","booked an appointment on 2024-05-20 2:30 AM","user","2024-05-20 02:30:00");
INSERT INTO logs VALUES("679","","Attempt to login with non-existent account details","unknown","2024-05-20 05:31:00");

DROP TABLE IF EXISTS medical_documents;
CREATE TABLE `medical_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `appointment_id` (`appointment_id`),
  CONSTRAINT `medical_documents_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `tb_patients` (`patientId`),
  CONSTRAINT `medical_documents_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO medical_documents VALUES("16","30","45","CPP-Cheatsheet__Hackr (1) (3) (1).pdf","../uploaded_files/CPP-Cheatsheet__Hackr (1) (3) (1).pdf","2024-05-20 02:30:34");
INSERT INTO medical_documents VALUES("17","30","45","CPP-Cheatsheet__Hackr (1) (3) (1).pdf","../uploaded_files/CPP-Cheatsheet__Hackr (1) (3) (1).pdf","2024-05-20 02:34:01");

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO schedule VALUES("26","1","2024-05-22","07:00:00","17:00:00","available","2024-05-18 18:10:07");
INSERT INTO schedule VALUES("27","1","2024-05-21","00:57:00","12:57:00","available","2024-05-19 00:57:49");

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tb_patients VALUES("30","","Test","Patient","","testpatient1@gmail.com","Prk. Sampaguita Poblacion Of Sto Nino South Cotabato","09280693642","2024-05-22","$2y$10$Rv2kBCf3Jbvp2gaG/mluBe1YCFWADQG5A72LgJVK97.Q13SYSHeZu","Verified","tgt1hrzaiaWZlxsb9ELNjFNzWWR5MGtCL1lLMlc2bnhYa0kxWUE9PQ==","2024-05-18 17:53:06","2024-05-20 00:46:05","0","","../uploaded_files/profile_30_bf91a285b8d635212da38c58e86136ea.png");
INSERT INTO tb_patients VALUES("31","","test","patient2","","testpatient2@gmail.com","","09280693642","","$2y$10$6FOox0C/lk.M54aD5vJYmumuu0X7yhGygjN4uRsESd54TdewpDeJy","Verified","KO2UVh9TOb5t0OEVXT65qE5oNnVnUHNkZ2IvSTBKZGtKaURtelE9PQ==","2024-05-20 00:34:31","2024-05-20 01:07:33","0","","");

