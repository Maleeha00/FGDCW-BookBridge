-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2025 at 08:55 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fgdcw_bookbridge`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `book_name` varchar(100) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `book_no` varchar(20) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `available_quantity` int(11) NOT NULL DEFAULT 0,
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `book_name`, `author`, `book_no`, `publisher`, `category`, `available_quantity`, `total_quantity`, `created_at`, `updated_at`) VALUES
(151, 'Problem solving with C++ (1)', 'Walter Savitch', '1170-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(152, 'Problem solving with C++ (2)', 'Walter Savitch', '1170-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(153, 'Network Management Principles and Practice', 'Mani Subramanian', '1616-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(154, 'Computer Fundamentals Concepts, Systems & Applications', 'Pradeep K.Sinha, Priti Sinha', '1375-SF', 'Manish Jain BPB publications', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(155, 'Digital Fundamentals', 'Thomas L.Floyd', '1172-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(156, 'Differential equations with boundary-value problems', 'Dennis G.Zill, Michael R.Cullen', '1183-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(157, 'C++ programming: from problem analysis to program design', 'D.S. Malik', '1176-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(158, 'Software Engineering', 'Ian Sommerville', '1588-SF', NULL, 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(159, 'Software Engineering', 'Ian Sommerville', '1587-SF', NULL, 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(160, 'Operating System Concepts', 'Abraham Silberschatz, Peter Baer Galvin, Greg Gagne', '1588-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(161, 'Operating System Concepts', 'Abraham Silberschatz, Peter Baer Galvin, Greg Gagne', '1589-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(162, 'Operating System Concepts', 'Abraham Silberschatz, Peter Baer Galvin, Greg Gagne', '1590-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(163, 'Computer Networks', 'Andrew S. Tanenbaum, Nick Feamster, David Wetherall', '1597-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(164, 'Computer security principles and practice', 'William Stallings, Lawrie Brown', '1791-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(165, 'Developing cloud native applications in azure using .net core', 'Rekha Kodali, Dr. Gopala Krishna Behara, Sankara Narayanan Govindarajulu', '1804-SF', 'BPB publications, India', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(166, 'Beginning java mvc 1.0', 'Peter Spath', '1803-SF', 'Apress', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(167, 'Programming Android', 'Zigurd Medniecks, Laird Dornin, G. Blake Meike, Masumi Nakamura', '1806-SF', 'O\'Reilly', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(168, 'Programming Android', 'Zigurd Medniecks, Laird Dornin, G. Blake Meike, Masumi Nakamura', '1859-SF', 'O\'Reilly', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(169, 'Pro android 2', 'Sayed Hashimi, Satya Komatineni, Dave Maclean', '1807-SF', 'Apress', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(170, 'Introduction to computer theory', 'Daniel I.A. Cohen', '1391-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(171, 'Applying UML and patterns', 'Craig Larman', '1392-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(172, 'Information technology project management', 'Kathy Schwalbe', '1617-SF', 'Cengage Learning', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(173, 'Programming with C', 'C M Aslam, T A Qureshi', '664-SF', 'Aikman Book Corporation, Urdu Bazar, Lahore', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(174, 'Computer Science', NULL, '636-SF', 'Aikman Book Corporation, Urdu Bazar, Lahore', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:13:58'),
(175, 'Guide book to Computer Science', 'Chaudhry Muhammad Aslam, Muhammad Tauqeer Ahmed Qureshi', '800-SF', 'Aikman Book Corporation, Urdu Bazar, Lahore', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(176, 'C++ from control structures through objects', 'Tony Gaddis', '1513-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(177, 'C++ from control structures through objects', 'Tony Gaddis', '1514-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(178, 'Modern operating systems (Vol 1)', 'Andrew S. Tanenbaum, Herbert Bos', '1551-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(179, 'Modern operating systems (2)', 'Andrew S. Tanenbaum, Herbert Bos', '1551-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(180, 'Object-Oriented and classical software engineering', 'Stephen R. Schach', '1552-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(181, 'CCNA Routing and switching study guide (1)', 'Todd Lammle', '1557-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(182, 'CCNA Routing and switching study guide (2)', 'Todd Lammle', '1557-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(183, 'How to program', '', '1168-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(184, 'Internet and world wide web how to program', 'P.J. Deital, H.M. Deitel', '1165-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(185, 'XML how to program', 'Paul Deital, Deital Nieto, Lin and Sandhu', '1179-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(186, 'Computer Networking', 'James F.Kurose, Keith W. Ross', '1555-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(187, 'Fundamentals of computer system & disk operating system', 'Ch. Muhammad Aslam', '766-SF', 'Aikman Book Corporation, Urdu Bazar, Lahore', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(188, 'Understanding pointers in C', 'Yashavant P. Kanetkar', '754-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(189, 'Operating Systems', 'Harvey M. Dietel', '718-SF', 'Addison-Wesley publishing company', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(190, 'Introductory Quantum Mechanics', 'Richard l. Liboff', '789-SF', 'M/S Combine Printers, Lahore', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(191, 'Vector Analysis', 'Dr. S.M. Yusuf', '804-SF', 'Ilmi kitab khana', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(192, 'Key to Vector Analysis', 'Dr. S.M. Yusuf', '805-SF', 'Ilmi kitab khana', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(193, 'Computer Age', 'Imran Majeed, Naveed Majeed', '637-SF', 'Ilmi Book House, Urdu Bazar, Lahore', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(194, 'Virtual LANs Construction, Implementation, and Management', 'Gilbert Held', '628-D', 'John Wiley & Sons, Inc.', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(195, 'Digital Control Systems Theory, Hardware, Software', 'Constantine H. Houpis, Gary B. Lamont', '479-D', 'McGraw-Hill, Inc.', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(196, 'Digital Control Systems Theory, Hardware, Software', 'Constantine H. Houpis, Gary B. Lamont', '480-D', 'McGraw-Hill, Inc.', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(197, 'Programming with Java IDL', 'Geoffrey Lewis, Steven Barber, Ellen Siegel', '627-SF', 'Wiley computer publishing', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(198, 'Problem Solving using Pascal Algorithm development and programming concepts', 'Romualdas Skvarcius', '771-SF', NULL, 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(199, 'Discovering Computers', 'Shelly, Cashman, Vermaat', '926-SF', NULL, 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(200, 'Computer Science PITB notes for Class XI', 'Dr. Hamayun M. Mian, Dr. Sohail Aslam', '757-SF', 'Punjab information technology board', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(201, 'Computer Science PITB notes for Class XI', 'Dr. Hamayun M. Mian, Dr. Sohail Aslam', '731-SF', 'Punjab information technology board', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(202, 'Introduction to Databases (MS Access)', '', '737-SF', 'Aikman Book Corporation, Urdu Bazar, Lahore', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(203, 'Jadeed manual for Turbo C', 'Imran Zafar Butt, Maqsood Iqbal Nasir, Muzammail Mehmood, Kamran Ahmad Siddiqui', '740-SF', 'Jadeed educational services', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(204, 'Computer Textbook for Class XI', '', '745-SF', 'Punjab Textbook Lahore', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(205, 'Introduction to Mathematical Statistics', 'Robert V. Hogg, Allen T. Craig', '742-D', 'Macmillan publishing Co., Inc.', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(206, 'Fundamentals of Database Systems', 'Ramez Elmasri', '1378-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(207, 'Computer Applications in Business', 'Tasleem Mustafa,Tariq Mahmood, Ahsan Raza Sattar, Imran Saeed', NULL, 'Kitab Markaz,Faisalabad', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(208, 'Data Science from Scratch', 'Joel Grus', '1607-SF', 'O\'Reilly', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(209, 'Database Systems', 'Carlos Coronel, Steven Morris', '1606-SF', 'Cengage UK', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(210, 'Management Information System', 'Kenneth C.Laudon, Jane P. Laudon', '1579-SF', 'Pearson', 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(211, 'Management Information System', 'Kenneth C.Laudon, Jane P. Laudon', '1580-SF', 'Pearson', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(212, 'Management Information System', 'Kenneth C.Laudon, Jane P. Laudon', '1581-SF', 'Pearson', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(213, 'Java:How to program early objects', 'Paul Dietal,Harvey Dietal', '1584-SF', 'Pearson', 'Information Technology', 4, 4, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(214, 'Java:How to program early objects', 'Paul Dietal,Harvey Dietal', '1585-SF', 'Pearson', 'Information Technology', 4, 4, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(215, 'Java:How to program early objects', 'Paul Dietal,Harvey Dietal', '1780-SF', 'Pearson', 'Information Technology', 4, 4, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(216, 'Java:How to program early objects', 'Paul Dietal,Harvey Dietal', '1586-SF', 'Pearson', 'Information Technology', 4, 4, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(217, 'Database Systems', 'Carlos Coronal, Steven Morris', '1772-SF', 'Cenage UK', 'Information Technology', 0, 0, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(218, 'C# 4.0:The complete reference', 'Herbelt Schildt', '1609-SF', 'McGraw-Hill', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(219, 'Beginning Android 2', 'Mark L.Murphy', '1614-SF', 'Apress', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(220, 'A Brain Friendly Guide: Head first PHP & MySQL', 'Lynn Beighley & Michael Morrison', '1773-SF', 'O\'Reilly', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-08-20 16:21:05'),
(221, 'Automate the Boring Stuff with Python', 'AI Sweigart', '1774-SF', 'William Pollock', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(222, 'Designing Enterprise Applications', 'Inderjeet Singh', '1771-SF', 'Addison-wesley', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(223, 'Database Systems', 'Thomas M.Connolly , Carolyne.Begg', '1550-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(224, 'Object Oriented Programming in C++', 'Robert Lafore', '1553-SF', 'Sams Publishing', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(225, 'Automata Theory Languages and Computation', 'John E.Hopcroft, Rajeev Motwani, Jeffrey D.Ullman', '1547-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(226, 'Machine Learning', 'Tom M.Mitchell', '1582-SF', 'Carnegie', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(227, 'Machine Learning', 'Tom M.Mitchell', '1583-SF', 'Carnegie', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(228, 'Object Oriented Programming in C++', 'Robert Lafore', '749-SF', 'Waite Group Press', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(229, 'Learn Windows 95 in a Day', 'Scott M.Fuller ,Kevin D.Pagan', '788-SF', 'BPB Publications', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(230, 'Computer Science', 'J.Glenn Brookshear, Dennis Brylow', '1166-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(231, 'Pattern Recognition and Machine Learning', 'Christopher M.Bishop', '1603-SF', 'Springer', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(232, 'Pattern Recognition and Machine Learning', 'Christopher M.Bishop', '1604-SF', 'Springer', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(233, 'The 8051 Microcontroller', 'Scott Mackenzie , Raphael C.-W.Phan', '1178-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(234, 'Computer Fundamentals', 'Pradeep K.Sinha, Priti Sinha', '1376-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(235, 'Artificial Intelligence', 'George F lugar', '1591-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(236, 'Introduction to Machine learning', 'Ethem Alpaydin', '1601-SF', 'Library of Congress', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(237, 'Introduction to Machine learning', 'Ethem Alpaydin', '1602-SF', 'Library of Congress', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(238, 'Assembly Language For x86 Processors', 'Kip R.Irvine', '1379-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(239, 'Computer Today with Basic', 'Donald H.Sanders', '1167-SF', 'McGraw-Hill', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(240, 'Database Systems (volume 1)', 'Carlos Coronel, Steven Morris', '1606-SF', 'Cengage', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(241, 'Database Systems (volume 2)', 'Carlos Coronel, Steven Morris', NULL, 'Cengage', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(242, 'Microsoft Access 7.0a For Windows 95', 'Timothy J.O\'Leary, Linda I.O\'Leary', NULL, 'McGraw-Hill', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(243, 'Microsoft Word97', '', '770-SF', 'Hamdard Kutab khana', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(244, 'Discovering Computers 2005', 'Gary B.Shelly , Thomas J.Cashman , Misty E. Vermaat', '926-SF', 'Shelly Casman Series', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(245, 'Computer Science', 'Taleem Mustafa , Tariq Masood , Imran Saeed , Abid Masood', '989-SF', 'Kitab Markaz,Faisalabad', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(246, 'Problem Solving using Pascal Algorithm Development and Programming Concepts', 'Romualdas Skvarcius', '771-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(247, 'C++ Program Design', 'James P.Cohoon, Jack W. Davidson', '626-D', 'Thomas Casson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(248, 'Introductory Statistics', 'Thomas H. Wonnacott, Ronaldo J. Wonnacott', '802-D', 'NBF , islamabad', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(249, 'Computer Architecture', 'James M.Feldman, Charles T.Retter', '436-D', 'McGraw-Hill', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(250, 'Computer Architecture', 'James M.Feldman, Charles T.Retter', '437-D', 'McGraw-Hill', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(251, 'Fortran for Today & Tomorrow', 'Michael H.Pressman', '547-D', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(252, 'Computer Essentials', 'Timothy J.O\'Leary, Linda I.O\'Leary', '485-D', 'McGraw-Hill', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(253, 'Principles of Information Security', 'Michael E. Whitman,Herbert J.Mattord', '1767-SF', 'Cengage Learning', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(254, 'Software Requirements', 'Karl Wiegers,Joy Beatty', '1613-SF', 'Microsoft Press', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(255, 'The Intel Microprocessors', 'Barry B. Brey', '1174-SF', 'Pearson Education', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(256, 'Fundamentals of Data Structure in C++', 'Ellis Horowitz, Sartaj Sahni, Dinesh P. Mehta', '1578-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(257, 'The Practice of System and Network Administration', 'Thomas A. Limoncelli, Christina J. Hogan, Strata R. Chalup', '1573-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(258, 'The Practice of System and Network Administration', 'Thomas A. Limoncelli, Christina J. Hogan, Strata R. Chalup', '1779-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(259, 'The Practice of System and Network Administration', 'Thomas A. Limoncelli, Christina J. Hogan, Strata R. Chalup', '1574-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(260, 'Fundamentals of Data Structure in C++', 'Ellis Horowitz, Sartaj Sahni, Dinesh P. Mehta', '1577-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(261, 'Fundamentals of Data Structure in C++', 'Ellis Horowitz, Sartaj Sahni, Dinesh P. Mehta', '1383-SF', NULL, 'Information Technology', 3, 3, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(262, 'Software Requirements', 'Karl Wiegers,Joy Beatty', '1790-SF', 'Microsoft Press', 'Information Technology', 2, 2, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(263, 'Software Quality Assurance', 'Daniel Galin', '1769-SF', 'Pearson', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(264, 'Mastering Enterprise JavaBeans', 'Ed Roman, Rima Patel Sriganesh, Gerald Brose', '1618-SF', 'Wiley Publishing,Inc', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(265, 'Python Crash Course', 'Eric Matthes', '1775-SF', 'William Pollock', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(266, 'Introduction to Computers', 'Peter Norton', '1389-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(267, 'Software Engineering', 'Roger S. Pressman, Bruce R. Maxim', '1180-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(268, 'Biomechanics Concepts and Computation', 'Cees Oomens, Marcel Brekelmans, Frank Baaijens', '1434-SF', 'Cambridge UK', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(269, 'Discovering Computers 2005 a Gateway to Information', 'Gary B. Shelly, Thomas J. Cashman, Misty E. Vermaat', '971-SF', NULL, 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(270, 'Data Communications and Networking', 'Behrouz A. Forouzan, Catherine Coombs,Sophia Chung Fegan', '723-SF', 'McGraw-Hill', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(271, 'C++ How to Program (Vol 1)', 'Paul Deitel, Harvey Deitel', '1554-SF', 'Pearsons NY', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(272, 'C++ How to Program (Vol 2)', 'Paul Deitel, Harvey Deitel', '1554-SF', 'Pearsons NY', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(273, 'C++ How to Program(Vol 3)', 'Paul Deitel, Harvey Deitel', '1554-SF', 'Pearsons NY', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(274, 'C++ How to Program (Vol 4)', 'Paul Deitel, Harvey Deitel', '1554-SF', 'Pearsons NY', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(275, 'Data and Computer Communications', 'William Stallings', '1556-SF', 'Pearson Education', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(276, 'Objective Questions Computer Studies', 'Arshad Javed, Matullah Hashmi ,T.A.Qureshi', '638-SF', 'Aikman Book Corporation', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(277, 'Mastering Practical Writing', 'S.H.Burton', '845-D', 'National Book Foundation', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(278, 'Introducing Systems Analysis', 'Steve Skidmore, Brenda Wroe', '787-SF', 'BPB Publications', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(279, 'Illustrated WordPerfect 5.1', 'Jordan Gold', '783-SF', 'BPB Publications', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(281, 'Visual Programming Using Visual Basic', 'Tariq Mahmood, Tasleem Mustafa, Imran Saeed ,Ahsan Raza Sattar', '881-D', 'IT Series Publications', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(282, 'Disaster Recovery Planning', 'Jon William Toigo', '624-D', 'John Wiley &Sons,Inc', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(283, 'Developer/2000: Build Forms II', 'Pascal Gibert', '782-SF', 'Oracle Corporation', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(284, 'Introduction to Computers', 'Peter Norton', '752-SF', 'McGraw-Hill', 'Information Technology', 1, 1, '2025-06-16 19:12:46', '2025-06-16 19:12:46'),
(285, 'Badminton', 'Fred Brundle', '2475', 'Teach yourself books', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(286, 'Laws of the game', NULL, '2478', 'Penguin books Ltd, England', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(287, 'Body contouring and conditioning through movement', 'Janeet Wessel, Chritine Macintyre', '16-D', 'Allyn and Bacon, Inc.', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(288, 'Olympic games, athletics rules and techniques', 'Dr. A. Waheed Mughal, Muhammad Khalid Mahmood', '1197-SF', NULL, 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(289, 'Table Tennis', 'Harold Myers', '2476', 'British library cataloguing in publication data', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(290, 'Health and physical education for 2nd year', NULL, '115-D', 'Ghulam hussain and sons, publishers, Urdu Bazar, Lahore', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(291, 'Olympic games 1984', 'Tomb Mcnab', '2477', 'Knight books Hodder & Stoughton', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(292, 'The new Hunza health plan', 'Renee Taylor', '874', 'Award books', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(293, 'Buster Crabbe\'s energistics', NULL, '848', 'Playboy press', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(294, 'Hoyle\'s rules of games', NULL, '1351', 'A signet book from new american library', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(295, 'Foundations of sports coaching', 'Paul E. Robinson', '1522-SF', 'Routledge Taylor & Francis Group', 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(296, 'Foundations of sports coaching', 'Paul E. Robinson', '1523-SF', 'Routledge Taylor & Francis Group', 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(297, 'The oxford companion to sports and games', 'John Arlott', '1194', 'Great britian by oxford university press', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(298, 'Teaching children gymnastics', 'Peter H. Werner, Lori H. Williams, Tina J. Hall', '1431-SF', NULL, 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(299, '101 Games for trainers', 'Bob Pike with Christopher Busse', '1529-SF', 'HRD press Inc.', 'HPE', 0, 2, '2025-06-16 19:26:14', '2025-08-20 02:37:55'),
(300, '101 Games for trainers', 'Bob Pike with Christopher Busse', '1528-SF', 'HRD press Inc.', 'HPE', 1, 2, '2025-06-16 19:26:14', '2025-08-20 02:40:22'),
(301, 'Emergency care in athletic training', 'Keith Gorse, Robert Blanc, Francis Feld, Matthew Radelet', '1398-SF', 'F.A. davis company', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(302, 'The new rules of marketing & PR', NULL, '1405-SF', NULL, 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(303, 'Strategic sports event management olymic edition', 'Guy Masterman', '1424-SF', 'TNQ books and Journals, Chennai, India', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(304, 'Study guide for memmler\'s Structure and function of the human body', 'Kerry L. Hull', '1423-SF', 'Wolters Kluwer', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(305, 'Behind human error', 'David D. Woods, Sidney Dekker, Richard Cook, Leila Johannesen, Nadine', '1428-SF', 'Asghgate e-Book', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(306, 'Test management and evaluation', 'Prof. Shafqat Rasul, Prof. Mahjabeen Shafqat', '1397-SF', 'Sharif sons, Ghosia Chowk, Lahore', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(307, 'Best practice protocols for physiuqe assessment in sport', 'Patria A. Hume, Deborah A. Kerr, Timothy R. Ackland', '1436-SF', 'Springer', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(308, 'Essentials of sports nutrition', 'Fred Brouns, Cherestar', '1401-SF', 'John Wiley & Sons, LTD', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(309, 'Principles and practice of maternal critical care', 'Sharon Einav, Carolyn F.Weiniger, Ruth Landau', '1521-SF', 'Springer nature Switzerland', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(310, 'Talent identification and development in sport', 'Angel Julia Abbott', '1444-SF', NULL, 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(311, 'Talent identification and development in sport', 'Angel Julia Abbott', '1714-SF', NULL, 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(312, 'Functional anatomy for sport and exercise', 'Clare E. Milner', '1396-SF', 'Routledge Taylor & Francis Group', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(313, 'Foundations of Physical education, exercise science, and sport', 'Jennifer L. Walton-Fisette, Deborah A. Wuest', '1445-SF', 'McGraw Hill education', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(314, 'The future of physical education', 'Anthony Laker, George Sage', '1404-SF', 'Routledge Taylor & Francis Group', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(315, 'The physiological basis of physical education and athletics', 'Edward L. Fox, Donald K. Mathews', '2480', 'CBS college publishing Sauners college publishing', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(316, 'The sports management toolkit', 'Paul Emery', '1524-SF', 'Routledge Taylor & Francis Group', 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(317, 'The sports management toolkit', 'Paul Emery', '1525-SF', 'Routledge Taylor & Francis Group', 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(318, 'Particles, sources, and fields', 'Julian Schwinger', '1440-SF', 'Advanced book program Perseus Books reading, Massachusetts', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(319, 'Philosophy of education (Dimensions of philosophy)', 'Nel Noddings', '1419-SF', 'Westview press, A member of Perseus Books', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(320, 'Fundamentals of Biomechanics', 'Nihat Ozkaya, Dawn Leger, David Goldsheyder, Margareta Nordin', '1435-SF', 'Springer international publishing switzerland', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(321, 'Infrastructure Redux', 'Nausheen H. Anwar', '1425-SF', 'Palgrave Macmillan', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(322, 'Beyond traditional peacekeeping', 'Donald C. F. Daniel, Bradd C. Hayes', '1426-SF', 'Palgrave Macmillan', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(323, 'Sports Biomechanics', 'Anthony Blazevich', '1415-SF', 'Bloomsburry Sport', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(324, 'Video games and storytelling', 'Souvik Mukherjee', '1409-SF', 'Palgrave Macmillan', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(325, 'Physical education futures', 'David Kirk', '1418-SF', 'Routledge Taylor & Francis Group', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(326, 'Public Management reform', 'Christopher Pollitt, Geert Bouckaert', '1443-SF', 'Oxford university press', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(327, 'Prevention practice and health promotion', 'Catherine Rush Thompson', '1400-SF', NULL, 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(328, 'Teachers guide for physical education for girls in high school', NULL, '2-D', 'California office of state printing', 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(329, 'Teachers guide for physical education for girls in high school', NULL, '3-D', 'California office of state printing', 'HPE', 2, 2, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(330, 'Philosophy of physical education & recreation', 'Prof. Jafar Hussain Jag', '1394-SF', 'Jag\'s publications, urdu bazar Lahore', 'HPE', 3, 3, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(331, 'Philosophy of physical education & recreation', 'Prof. Jafar Hussain Jag', '1526-SF', 'Jag\'s publications, urdu bazar Lahore', 'HPE', 3, 3, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(332, 'Philosophy of physical education & recreation', 'Prof. Jafar Hussain Jag', '1527-SF', 'Jag\'s publications, urdu bazar Lahore', 'HPE', 3, 3, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(333, 'Play it again', 'Asterie Baker Provenzo, Eugene F. Provenzo, JR', '17-D', 'Prentice-Hall, Inc.', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(334, 'Lifetime fitness and wellness', 'Melvin H. Williams', '478-D', 'Wm. C. Brown Communications, Inc.', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(335, 'Between past and future', 'Hannah Arendt', '1570-SF', 'The viking press, New York', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(336, 'Reforming education and changing schools', 'Richard Bowe, Stephen J. Ball, Anne Gold', '1414-SF', 'Routledge library editions', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(337, 'A wellness way of life', 'Gwen Robbins, Debbie Powers, Sharon Burgess', '481-D', 'Wm. C. Brown Communications, Inc.', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(338, 'Sport, Racism and social media', 'Neil farrington, Lee Hall, Daniel Kilvington, John Price, Amir Saeed', '2018-SF', 'Routledge Taylor & Francis Group', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(339, 'Musuem communication and social media', 'Kirsten Drotner, Kim Christian Schroder', '1417-SF', 'Routledge Taylor & Francis Group, Research in museum studies', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(340, 'Physical education essential issues', 'Ken Green, Ken Hardman', '1402-SF', 'Sage publications', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(341, 'Physical agents in rehabilitation', 'Michelle H. Camerson', '1399-SF', NULL, 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(342, 'صحت و جسمانی', NULL, '1784', 'United anarkali Lahore', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(343, 'پاکستان کھیل کے میدان میں', 'جمیل مرزا، قاضی ممتاز احمد', '107', 'کیکس پبلی کیشنز لمٹیڈ لندن', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(344, 'صحت و تعلیم جسمانی براۓ انٹرمیدیٹ پارٹ I', 'پروفیسر راۓ محمد کھرل', '1779', 'علمی بک ہاؤس چوک اردو بازار لاہور', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(345, 'صحت و تعلیم جسمانی', 'مسز صابرہ اعظم', '2242', 'پنجاب تیکسٹ بک بورڈ لاہور', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(346, 'جسمانی تعلیم', 'فوزیہ سلیمی', '729-SF', 'پنجاب تیکسٹ بک بورڈ لاہور', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(347, 'باسکٹ بال', 'چوھدری حشمت علی', '2240', 'محمود حسین اینڈ برادرز، صدر بازار، منٹگمری', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(348, 'تعلیمی جمناسٹکمک (حرکاتی سائنس)', 'چوھدری حشمت علی', '2239', 'محمود حسین اینڈ برادرز، صدر بازار، ساہیوال', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(349, 'کھیلوں کے قوانین', 'چوھدری غلام محمد', '2237', 'عنایت اللہ بلڈنگ انار کلی لاہور', 'HPE', 1, 1, '2025-06-16 19:26:14', '2025-06-16 19:26:14'),
(350, 'Media of mass communiation', 'John Vivian', '1208-SF', 'Pearson', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(351, 'Media of mass communiation', 'John Vivian', '1209-SF', 'Pearson', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(352, 'Media of mass communiation', 'John Vivian', '1210-SF', 'Pearson', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(353, 'Physics of human body', 'Irving P. Herman', '1406-SF', 'Springer', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(354, 'Human physiology', 'Lauralee Sherwood', '1422-SF', 'Cengage Learning', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(355, 'Gray\'s anatomy for students', 'Richard L. Drake, A. Wayne Vogl, Adam W. M. Mitchell', '1331-SF', 'Fraooq kitab ghar, Pakistan', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(356, 'Mastering the world of pschology', 'Samuel, e. wood, ellen green wood, denise boyd', '1571-SF', 'Peason', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(357, 'Instructional Models for physical education', 'Michael W.Metzler', '1199-SF', 'Routledge Taylor & Francis Group', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(358, 'The philosophy of physical education', 'Steven A. Stolz', '1198-SF', 'Routledge Taylor & Francis Group', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(359, 'Politics among Nations', 'Hans J. Morgenthau, Kenneth W. Thompson', '1212-SF', NULL, 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(360, 'Politics among Nations', 'Hans J. Morgenthau, Kenneth W. Thompson', '1213-SF', NULL, 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(361, 'Politics among Nations', 'Hans J. Morgenthau, Kenneth W. Thompson', '1211-SF', NULL, 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(362, 'Journalism for all', 'Dr, Mehdi Hasan, Dr. Abdus Salam Khurshid', '1205-SF', 'Aziz Book deopot, Urdu bazar, Lahore', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(363, 'Journalism for all', 'Dr, Mehdi Hasan, Dr. Abdus Salam Khurshid', '1206-SF', 'Aziz Book deopot, Urdu bazar, Lahore', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(364, 'Journalism for all', 'Dr, Mehdi Hasan, Dr. Abdus Salam Khurshid', '1207-SF', 'Aziz Book deopot, Urdu bazar, Lahore', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(366, 'Economics', 'Campbell R. McConnell, Stanley L. Brue, Sean M. Flynn', '1467-SF', NULL, 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(367, 'Economics', 'Campbell R. McConnell, Stanley L. Brue, Sean M. Flynn', '1468-SF', NULL, 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(368, 'Economics', 'Campbell R. McConnell, Stanley L. Brue, Sean M. Flynn', '1600-SF', NULL, 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(369, 'Instructional and entre-preneurship', 'Peter F. Drucker', '2019-SF', 'Perfect bound', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(370, 'Enterpreneurship and the privatizing of government', 'Calvin A. Kent', '791-D', 'National book foundation, Islamabad', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(371, 'Management', 'Ricky W. Griffin', '1548-SF', NULL, 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(372, 'Management', 'Stephen P. Robbins, Mary Coulter, Joseph J. Martocchio, Lori K. Long', '1377-SF', NULL, 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(373, 'The lean startup', 'Eric Ries', '1469-SF', 'Penguin random house, UK', 'Economics', 2, 2, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(374, 'The lean startup', 'Eric Ries', '1470-SF', 'Penguin random house, UK', 'Economics', 2, 2, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(375, 'Sports Law', 'Simon Gradiner', '1413-SF', 'Cavendish publishing limited', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(376, 'An introduction to international relations theory', 'Jill Steans, Lloyd Pettiford, Thomas Diez, Imad El-Anis', '1216-SF', 'Pearson', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-08-19 09:32:05'),
(377, 'Understanding mass communication', 'Melvin L. DeFleur, Everette E. Dennis', '463-D', 'Houghton mifflin company', 'Economics', 2, 2, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(378, 'Understanding mass communication', 'Melvin L. DeFleur, Everette E. Dennis', '464-D', 'Houghton mifflin company', 'Economics', 2, 2, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(379, 'Grasping the democratic peace', 'Bruce Russett', '1299-SF', 'Princeton university press, new jersey', 'Economics', 2, 2, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(380, 'Grasping the democratic peace', 'Bruce Russett', '1300-SF', 'Princeton university press, new jersey', 'Economics', 2, 2, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(381, 'International politics', 'Robert J. Art, Robert Jervis', '1286-SF', 'Pearson', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(382, 'Basic mechanics', 'Susan J. Hall', '1395-SF', 'McGraw hill education', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(383, 'صحت و تعلیم جسمانی', 'مسز صابرہ اعظم', '1781', 'پبلشرز یونائٹڈ، انارکلی، لاہور', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(384, 'صحت و تعلیم جسمانی', 'مسز صابرہ اعظم', '1782', 'پبلشرز یونائٹڈ، انارکلی، لاہور', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(385, 'صحت و تعلیم جسمانی', 'مسز صابرہ اعظم', '1785', 'پبلشرز یونائٹڈ، انارکلی، لاہور', 'Economics', 3, 3, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(386, 'صحت و تعلیم جسمانی (پارٹ I)', 'پروفیسر راۓ محمد کھرل', '3034', 'علمی بک ہاؤس چوک اردو بازار', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(387, 'صحت و تعلیم جسمانی (پارٹ II)', 'پروفیسر راۓ محمد کھرل', '3035', 'النور ایجوکیشنل پبلشرز اردو', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(388, 'ہیلتھ اینڈ فشزیکل ایجوکیشن (صحت و تعلیم جسمانی)', 'مسز صابرہ اعظم، مسٹر ای-یو-خان، ونگ کمانڈر حمید علی سوفی', '41', 'لاہور آرٹ پریس، انارکلی، لاہور', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(389, 'ہیلتھ اینڈ فشزیکل ایجوکیشن', 'مسز صابرہ اعظم، چوھدری بشیر احمد، محمد رفیق، اے یو خان، سید محمد یوسف رضوی', '2238', 'پنجاب ٹیکسٹ بک بورڈ، لاہور', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(390, 'فلاسفیکل بیسز آف فشزیکل ایجوکیشن', 'ڈاکٹر اے وحید مغل', '1195-SF', NULL, 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(391, 'کھیلوں کے قوانین', 'ڈاکٹر اے وحید مغل', '1196-SF', 'نعیم کمپوزرز، اسلام آ باد', 'Economics', 1, 1, '2025-06-16 19:37:43', '2025-06-16 19:37:43'),
(392, 'Programmed learning aid for introduction sociology', 'Paul B. Horton, Robert L. Horton, Roger H. Hermanson', '29-D', 'Learning systems company', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(393, 'The social experience', 'James W. Vander Zanden', '458-D', 'McGraw hill publishing company', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(394, 'Mass communications', 'Wulber Schramm', '781-D', NULL, 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(395, 'Effective business communication', 'Herta. A. Murphy, Herbert W. Hildebrandt, Jane P. Thomas', '1515-SF', NULL, 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(396, 'Effective business communication', 'Herta. A. Murphy, Herbert W. Hildebrandt, Jane P. Thomas', '1516-SF', NULL, 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(397, 'Business communication today', 'Bovee, Thill, Schatzman', '834-D', 'Pearson', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(398, 'An introduction International relations theory', 'Jill Steans, Lloyd Pettiford, Thomas Diez, Imad El-Anis', '1214-SF', 'Pearson', 'Economics', 1, 2, '2025-06-16 19:48:24', '2025-08-19 10:28:28'),
(399, 'An introduction to international relations theory', 'Jill Steans, Lloyd Pettiford, Thomas Diez, Imad El-Anis', '1215-SF', 'Pearson', 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(400, 'International politics', 'Robert J. Art, Robert Jervis', '1284-SF', 'Pearson', 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(401, 'International politics', 'Robert J. Art, Robert Jervis', '1285-SF', 'Pearson', 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(402, 'International relations', 'Norman D. Palmer, Howard C. Perkins', '764-D', NULL, 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(403, 'Business communication today', 'Bovee, Thill', '1517-SF', NULL, 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-08-19 09:27:32'),
(404, 'Business communication today', 'Bovee, Thill', '1518-SF', NULL, 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(405, 'Islamic economics alternatives', 'Jomo K.S.', '1438-SF', 'Palgrave Macmillan', 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(406, 'Islamic economics alternatives', 'Jomo K.S.', '1416-SF', 'Palgrave Macmillan', 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(407, 'The dynamic of mass communication', 'Joseph R. Dominick', '445-D', 'Macmillan-Hill, Inc.', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(408, 'Sociology', 'Paul B. Horton, Chester L. Hunt', '1531-SF', NULL, 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(409, 'Sociology', 'Paul B. Horton, Chester L. Hunt', '1532-SF', NULL, 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(410, 'Sociology', 'John J. Macionis', '1598-SF', 'Pearson', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(411, 'Social order in Pakistani society', 'Dr. M. S. Baqai', '818-D', 'National book foundation', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(412, 'Mass communication and social media', 'Kirsten Drotner, Kim Christian Schroder', '1507-SF', 'Routledge Taylor & Francis Group', 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(413, 'Mass communication and social media', 'Kirsten Drotner, Kim Christian Schroder', '1508-SF', 'Routledge Taylor & Francis Group', 'Economics', 2, 2, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(414, 'The social context of AIDS', 'Joan Huber, Beth E. Schneider', '376-D', 'Sage publications', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(415, 'Understanding social inequality', 'Hubert M. Blalock, Jr.', '404-D', 'Sage publications', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(416, 'Social work processes', 'Beulah Roberts Compton, Burt Galaway', '41-D', 'The dorsey press', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(417, 'Sociology a brief introduction', 'Richard T. Schaefer', '1615-SF', 'McGraw hill education', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(418, 'Sociology and school knowledge', 'Geoff Whitty', '1433-SF', 'Methuen & Co. Ltd', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(419, 'Environmental Sociology', 'John Hannigan', '1432-SF', 'Routledge Taylor & Francis Group', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(420, 'Social Problems', 'Kenneth J. Neubeck', '462-D', 'McGraw-Hill, Inc.', 'Economics', 1, 1, '2025-06-16 19:48:24', '2025-06-16 19:48:24'),
(421, 'The Reluctant Fundamentalist', 'Mohsin Hamid', '1619-SF', 'Kitab mahal (PVT.) LTD. KM library series', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(422, 'Philip Sidney An apology for poetry', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1761-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(423, 'Samuel Johnson Preface to Shakespeare', 'Prof. Munawar Ali Malik', '1762-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(424, 'Feminist library theory A reader', 'Mary Eagleton', '1345-SF', 'Wiley-Blackwell publishing Ltd.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(425, 'Teaching and Learning in the language classroom', 'Tricia Hedge', '2009-SF', 'Oxford university press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(426, 'Qualitative reaserch', 'David Silverman', '1702-SF', 'Sage publications Ltd.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(427, 'The norton anthology of theory and criticism (Vol 1)', 'William E. Cain, Laurie A. Frinke, Barbara E. Johnson, John McGowan, Jeffrey J. Williams', '1366-SF', 'W. W Norton & company, Inc.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(428, 'The norton anthology of theory and criticism (Vol 2)', 'William E. Cain, Laurie A. Frinke, Barbara E. Johnson, John McGowan, Jeffrey J. Williams', '1366-SF', 'W. W Norton & company, Inc.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(429, 'The norton anthology of theory and criticism (Vol 3)', 'William E. Cain, Laurie A. Frinke, Barbara E. Johnson, John McGowan, Jeffrey J. Williams', '1366-SF', 'W. W Norton & company, Inc.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(430, 'Literary theory an Introduction', 'Terry Eagleton', '1322-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(431, 'Catherine Belsey Critical practice', 'Prof. Munawar Ali Malik, Prof. Shehryar Khan', '1865-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(432, 'Research Design', 'John W. Creswell, J. David Creswell', '1701-SF', 'Sage publications Ltd.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(433, 'A Mercy a novel', 'Tonny Morrison', '1746-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(434, 'The age of kali Indian travels & encounters', 'William Dalrymple', '1565-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(435, 'The Fox', 'Lawerence, David Herbert', '1289-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(436, 'Arthur Miler Death of a salesman', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1750-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(437, 'Arthur Miler Death of a salesman', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1754-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(438, 'A farewell to arms', 'Ernest Hemingway', '1243-SF', 'ILQA publications', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-17 12:18:25'),
(439, 'Bapsi Sidhwa Ice-Candy man', 'Prof. Munawar Ali Malik, Syed Muhammad Ali', '1621-SF', 'Kitab mahal (PVT.) LTD. KM library series', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(440, 'The age of race', 'Dr. Shafique Ali Khan', '2683', 'Royal book company, Victoria road, Karachi', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(441, 'Oxford practice grammar', 'John Eastwood', '1077-SF', 'Oxford university press', 'English', 3, 3, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(442, 'Oxford practice grammar', 'John Eastwood', '883-SF', 'Oxford university press', 'English', 3, 3, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(443, 'Oxford practice grammar', 'John Eastwood', '882-SF', 'Oxford university press', 'English', 3, 3, '2025-06-16 19:55:38', '2025-06-16 19:55:38');
INSERT INTO `books` (`id`, `book_name`, `author`, `book_no`, `publisher`, `category`, `available_quantity`, `total_quantity`, `created_at`, `updated_at`) VALUES
(444, 'Principles of language learning and teaching', 'H. Douglas Brown', '844-D', 'National book foundation, Islamabad, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(445, 'The cambridge encyclopedia of language', 'David Crystal', '812-D', 'National book foundation, Umair printers, Lahore, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(446, 'Eugene O\'Neil Long day\'s journey into night', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1749-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(447, 'Eugene O\'Neil Long day\'s journey into night', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1749-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(448, 'Thomas Hardy The mayor of casterbridge', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1242-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(449, 'Selected essays', 'Francis Bacon', '1246-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(450, 'The mill on the floss', 'George Eliot', '1238-SF', 'Kitab mahal (PVT.) LTD. KM library series', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(451, 'The mill on the floss', 'George Eliot', '1239-SF', 'Kitab mahal (PVT.) LTD. KM library series', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(452, 'Burnt shadows', 'Prof. S.P. Sen Gupta', '1662-SF', 'Famous products, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(453, 'Aristotle\'s Poetics', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1763-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(454, 'William Wordsworth Preface to the lyrical ballads', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1760-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(455, 'Matthew Arnold Culture and Anarchy', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1764-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(456, 'Joseph Andrews', 'Henny Fielding', '1356-SF', 'Famous products, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(457, 'Focus on comprehension', 'Peter Ellison', '1192-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(458, 'Shuggie Bain', 'Douglas Stuart', '2016-SF', 'Grove press, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(459, 'Shooting an elephant', 'George Orwell', '1759-SF', 'Penguin Books', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(460, 'The merchant of venice', 'William Shakespeare', '1012-SF', 'Vishv Vijay Pvt. Ltd.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(461, 'Julius Caesar', 'William Shakespeare', '1015-SF', 'Vishv Vijay Pvt. Ltd.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(462, 'The Namesake', 'Jhumpa Lahiri', '1741-SF', 'A mariner book houghton mifflin company boston, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(463, 'Waiting for Godot', 'Samuel Beckett', '1657-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(464, 'Philosophy the basics', 'Nigel Warburton', '1738-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(465, 'Believe me', 'Tahereh Mafi', '1715-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(466, 'Six plays', 'Luigi Pirandello', '2012-SF', 'Calder publications', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(467, 'Feminist literary criticism', 'Mary Eagleton', '2013-SF', 'Longman Inc. New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(468, 'And the mountains echoed', 'Khaled Hosseini', '1743-SF', 'Penguin Books', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(469, 'The cousins', 'Karen M. Mcmanus', '912-D', 'Penguin random house Delacorte press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(470, 'Beloved a novel', 'Tonni Morrison', '1745-SF', 'Vintage books a division of random house, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(471, 'Gulliver\'s Travels', 'Jonathan Swift', '1251-SF', 'Harper Collins publishers Ltd', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(472, 'Thomas Hardy The mayor of casterbridge', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1240-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(473, 'The god of small things', 'Arundhati Roy', '1700-SF', 'Indian ink publishing Co Pvt Ltd', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(474, 'Great Expectations', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1756-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(475, 'Azadi', 'Arundhati Roy', '1681-SF', 'Penguin Books', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(476, 'As good as dead', 'Holly Jackson', '1737-SF', 'Harper Collins publishers Ltd', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(477, 'Ice-candy man', 'Bapsi Sidhwa', '1656-SF', 'Ilqa publications, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(478, 'Norton Anthology of american literature (Vol 1)', 'Nina Baym, Ronald Gottesman, Laurence B. Holland, David Kalstone, Francis Murphy, Hershel Parker, Wi', '252-D', 'Norton & company, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(479, 'Robert Frost Selected poems', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1752-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(480, 'Robert Frost Selected poems', 'Prof. Munawar Ali Malik, Prof. Dr. H. Zia-ul-Islam Chaudhry', '1641-SF', 'Kitab mahal (PVT.) LTD. KM library series', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(481, 'English for undergraduates', 'D.H. Howe, T.A. Kirkpatrick, D.L. Kirkpatrick', '880-SF', 'Oxford university press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(482, 'English for undergraduates', 'D.H. Howe, T.A. Kirkpatrick, D.L. Kirkpatrick', '881-SF', 'Oxford university press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(483, 'What\'s in a word?', 'Samuela Eckstut, Karen Sorensen', '477-D', 'Longman publishing group UK Ltd', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(484, 'English elements', 'Guest & Eshuys', '570-SF', 'Jacaranda Wiley Ltd, Milton', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(485, 'A short history of english literature', 'Ifor Evans', '596', 'Penguin Books Ltd, England', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(486, 'Great Expectations', 'Charles Dickens', '1807', 'Pan books Ltd, London', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(487, 'A selection of english poetry', 'Milton, Wordsworth, Keats Browning, Robert Frost', '654-SF', 'Prop. College book depot, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(488, 'Good english', 'G. H. Thornton, Kathleen Baron', '600', 'Teach yourself books Hodder and Stoughton', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(489, '30 days to a more powerful vocabulary', 'Dr. Wilfred Funk & Norman Lewis', '2117', 'Pocket books, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-08-19 09:27:29'),
(490, 'College Essays', 'Martin', '3203', 'Cooper publishers, Bombay', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(491, 'Dictionary of Proverbs', 'Kam Chuan Aik', '667-D', 'Federal publications', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(492, 'Selected poems', 'T.S. Eliot', '175-D', 'Faber and Faber limited, London', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(493, 'Writing to communicate', 'Carl Geoller', '876', 'A mentor book, New American Library, Times mirror, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(494, 'Harvest of the Sun', 'E.v. Thompson', '1203', 'Pan books Ltd, London', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(495, 'Three for tomorrow', 'Robert silverberg, Roger Zelazny, James Blish', '760', 'Lowe and brydone, Ltd., London', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(496, 'Word power made easy', 'Norman Lewis', '484-SF', 'Pocket books, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(497, 'Animal Farm', 'George Orwell', '651-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(498, 'Spoken english', 'Scott Waugh', '3246', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(499, 'Bes quotations for all occassions', 'Lewis C. Henry', '3248', 'Ballantine books', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(500, 'A selection of modern english verse', NULL, '191-D', 'Polymer publication, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(501, 'Guide to english idioms', 'B.A. Phythian', '660-D', 'Federal publications', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(502, 'Dictionary of quotations', 'Dr. B. James', '162-D', 'Goodwill publishing house', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(504, 'Selected Poetry', 'Alfred Tennyson', '295-D', 'The modern library, Random house Inc. New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(505, 'Intruder in the dust', 'William Faulkner', '294-D', 'Mcgraw-Hill, Inc.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(506, 'Animal Farm', 'George Orwell', '650-SF', 'Penguin books', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(507, 'Faster reading self-taught', 'Harry Shefter', '590', 'Pocket books, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(508, 'How to do just about everything', 'Courtney Rosen', '963-SF', 'Harper Collins publishers Ltd', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(509, 'W.wordsworth\'s Selected poems', 'Bashir Hussain, Zafar Ali Rana', '3202', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(510, 'Macbeth', 'William Shakespeare', '3212', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(511, 'Proverbial english', 'M. Zaman, Naveed Akhtar', '959-SF', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(512, 'Azhar appliactions for better jobs', 'M. S. Bhatti', '3204', 'Azhar publishers, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(513, 'Common errors in english', 'Roger Thomson', '3247', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(514, 'Robert Browning Selected poems', 'Zafar Ali Rana, N. A. Akhter', '3210', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(515, 'The tales of Olga da Polga', 'Micheal Bond', '537-D', 'Houghton Mifflin company', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(516, 'Usuage and abusage', 'Eric Partridge', '787-D', 'National book foundation, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(517, 'A selection of Short stories and One-act play', NULL, '3002', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(518, 'William Shakespeare Antony and Cleopatra', 'Praveen Bhatia', '1019-SF', 'UBS Publishers distributors Pvt. Ltd.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(519, 'Perfect public learning', 'M. Saqlain Bhatti, Raza-ul-Haq Badakhshani, Muhammad Masood', '663-D', 'Azhar publisers, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(520, 'Common synonyms antonyms', 'M. Zaman, Naveed Akhtar', '665-D', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(521, 'Common idioms and phrases', 'M. Zaman, Naveed Akhtar', '659-D', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(522, 'The Essayist', 'Sheridan Baker', '12-D', 'Thomas Y. Corwell company, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(523, 'The english language in Pakistan', 'Robert J. Baumgardner', '814-D', 'National book foundation, Islamabad, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(524, 'Everyday english dialogue', 'Muhammad Masood', '662-D', 'Azhar publishers, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(525, 'The glass Menagerie', 'Tennessee Williams', '3208', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(526, 'John Miltan Selected poems', 'Zafar Ali Rana, N. A. Akhter', '3214', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(527, 'Selected essays', 'Muhammad Masood', '3239', 'Azhar publishers, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(528, 'Latest essays', NULL, '3240', 'Dogar publishers, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(529, 'Selected poems', 'Milton, Wordsworth, Keats Browning, Robert Frost', '655-SF', 'New kitab mahal publishers', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(530, 'A comprehensive study of the articles: A, An & The', 'Shahid Sarwar Kazi', '538-SF', 'Zujaj publishers, Garhi ahmad abad, Gujrat, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(531, 'Formal semantics and logic', 'Bas C. van Fraassen', '40-D', 'The Macmillan company, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(532, 'Treasury of proverbs and epigrams', NULL, '163-D', 'Avenel books, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(533, 'Invention & Design a rhetorical reader', 'Forrest D. Burt, E. Cleve Want', '51-D', 'Random house, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(534, 'The Arden Shakespeare Macbeth', 'Kenneth Muir', '746-SF', 'Methuen, London & New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(535, 'The Arden Shakespeare Macbeth', 'Kenneth Muir', '746-SF', 'Methuen, London & New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(536, 'Model english essays', 'Iqbal A Bhatti', '706', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(537, 'Conjunction & Interjection', 'M.Masood', '962-SF', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(538, 'Master your english punctuation', 'M.Masood', '956-SF', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(539, 'Master your english pronounciation', 'M.Masood', '958-SF', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(540, 'Best quotations', 'M. Tariq Qureshi', '961-SF', 'Fatima book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(541, 'History of English literature', 'Loius Cazamian, Raymond Las Vergnas', '554-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(542, 'Ideal essays and letters with stories, Comprehension, Idioms & phrases', 'M. Masood', '3215', 'Shaharyar publisher, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(543, 'The late Lily Shiel', 'Sheilah Graham', '765', 'Grosset & Dunlap, A filmways company publishers, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(544, 'High school english grammar and composition', 'Wren, Martin', '707', 'S. Chand & Company Ltd. New Delhi', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(545, 'American Literature', NULL, '543-D', 'Macmillan publishing company, Inc.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(546, 'High school english grammar and composition', 'Wren, Martin', '164-D', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(547, 'English grammar and composition', 'Chisty, R. A. Khan, S. A. Hamid', '3040', 'Polymer publication, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(548, 'How to build a better vocabulary', 'Maxwell nurnberg, Morris Rosenblum', '700', 'Fawcett popular library, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(549, 'Under the greenwood tree', 'Thomas Hrady', '35', 'Macmillan publishing company, Inc. London', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(550, 'Inside asquith\'s cabinet', 'Edward David', '762', '50 Albemarle street, London', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(551, 'Clear speech', 'Judy B. Gilbert', '262-D', 'Cambridge university press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(552, 'An anthology of living english prose', 'Muhammad Ismail Bhatti', '44', 'Ilmi kitab khana, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(553, 'The treasury of Khalil Gibran', 'Martin l. Wolf', '2102', 'The citadel press, Secaucus, New Jersey', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(554, 'Master your english proverb', 'M. Masood', '960-SF', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(555, 'Guide to patterns and usage in english', 'A. S. Hornby', '733-D', 'Oxford university press & The english language book society', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(556, 'English simple grammar & composition', 'Zia-ur-Rahman Khan', '3043', 'Markazi Kutub Khana, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(557, 'Good-Bye Mr.Chips', 'James Hilton', '1586', 'Pakistani Branch, Oxford university press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(558, '5000 proverbs', 'M. Zaman, Naveed Akhtar', '957-SF', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(559, 'Dialogue Practice', 'Muhammad Masood', '666-D', 'Rabia book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(560, 'A selection of english prose for BS students', 'Nosheen Khan, G. S. Qureshi', '945-SF', 'The Caravan book house, Lahore, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(561, 'Signs in action', 'James Sutton', '6-D', 'Reinhold publishing corporation, New York', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(562, 'A selection of modern english essays', 'Sajjad Shaikh', '3007-SF', 'The Caravan book house, Lahore, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(563, 'Good-Bye Mr.Chips', 'James Hilton', '8', 'Punjab Textbook Board, Lahore', 'English', 4, 4, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(564, 'Good-Bye Mr.Chips', 'James Hilton', '45-SF', 'Punjab Textbook Board, Lahore', 'English', 4, 4, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(565, 'Good-Bye Mr.Chips', 'James Hilton', '44-SF', 'Punjab Textbook Board, Lahore', 'English', 4, 4, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(566, 'Good-Bye Mr.Chips', 'James Hilton', '1591', 'Punjab Textbook Board, Lahore', 'English', 4, 4, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(567, 'The old man and the see', 'Ernest Hemingway', '3009', 'Jonathan Cape thirty bedford square London', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(568, 'The old man and the see', 'Ernest Hemingway', '3008', 'W & S brothers Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(569, 'A workbook of english grammar and composition', 'Karamat Hussain, Akhtar-ud-din, Iftikhar Ahmad', '222', 'Jadeed book depot Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(570, 'Modern guide to english', NULL, '711', 'Pakistan book centre, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(571, 'High school english grammar and composition', 'Wren, Martin', '329', 'Shalimar publishers, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(572, 'Chamber Idioms', 'E. M Kirkpatrick, C. M. Schwarz', '668-D', 'W & R chambers Ltd Edinburgh', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(573, 'Living english structure', 'W. Stannard Allen', '553-SF', 'Longman', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(574, 'A selection of Short stories', 'Drek Hudson', '648-SF', 'Allied book corporation', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(575, 'A selection of Short stories', 'Drek Hudson', '649-SF', 'Allied book corporation', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(576, 'A selection of modern english essays', 'Sajjad Shaikh', '3038', 'The Caravan book house, Lahore, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(577, 'Oxford progressive english alternative course', 'A. S. Hornby, R. Mackin', '653', 'Oxford university press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(578, 'Ripon college composition', 'Ghulam Jilani Asghar', '603-SF', 'Noorsons, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(579, 'Polymer English Grammar', NULL, '46-SF', NULL, 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(580, 'Missing person', 'Karen Hunter Anderson, Kathleen Breugging, John Lance', '263-SF', 'Longman', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(581, 'BS english simple grammar & composition', 'Zia-ur-Rahman Khan', '1039-SF', 'Simple book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(582, 'Essentials of english grammar', 'D. Terence Langendoen', '22-D', 'Holt, Rinehart and Winston, Inc.', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(583, 'B.A. Englsih simple grammar & composition', 'Zia-ur-Rahman Khan', '3010', 'Simple book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(584, 'A textbook of English for class XI', 'S. Sabir Jamil, Muhammad Aslam Gondal, M. H Hamdani', '870-SF', 'Punjab Textbook Foundaion, Islamabad', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(585, 'A textbook of English for class XI', 'S. Sabir Jamil, Muhammad Aslam Gondal, M. H Hamdani', '871-SF', 'Punjab Textbook Foundaion, Islamabad', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(586, 'A textbook of English for class XI', 'S. Sabir Jamil, Muhammad Aslam Gondal, M. H Hamdani', '872-SF', 'Punjab Textbook Foundaion, Islamabad', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(587, 'B.Sc. Simple grammar & composition', 'Zia-ur-Rahman Khan', '912-SF', 'Simple book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(588, 'B.Sc. Simple grammar & composition', 'Zia-ur-Rahman Khan', '944-SF', 'Simple book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(589, 'A new anthology of english verse', 'Kaneez Aslam, Shuaib Bin Hasan', '1040-SF', 'The Caravan book house, Lahore, Pakistan', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(590, 'Intermediate English', NULL, '940-SF', 'Punjab Textbook Board, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(591, 'Intermediate English', NULL, '943-SF', 'Punjab Textbook Board, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(592, 'English grammar and composition with textbook supplement', 'Aslam Gondal', '613-SF', 'Ilmi book house, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(593, 'English general', 'Ch. Manzoor Ahmed', '3064', 'Dogar brothers, Urdu bazar, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(594, 'Polymer English grammar and composition for B.A. Students', 'B.A. Chisty, R. A. Khan, S. A. Hamid', '219-D', 'Polymer Publications', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(595, 'Polymer English grammar for intermediate students', 'Mukhtar Parvez, Arif Muhammad, Syed Saadat Mehdi', '3165', 'Polymer Publications', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(596, 'A practical english grammar', 'AJ Thomson, AV Martinet', '1765', 'Oxford University press', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(597, 'Diploma in english', 'S. H. Khan', '2324', 'Maktaba-e-Faridi, Urdu college Karachi', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(598, 'English grammar and composition with textbook supplement for intermediate students', 'DR. A. R. Anjum, Iftikhar Ahmad Bajwa, Nisar Ahmad Qureshi', '614-SF', 'Polymer Publications', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(599, 'Intermediate English short stories', NULL, '36-SF', 'Punjab Textbook Board, Lahore', 'English', 2, 2, '2025-06-16 19:55:38', '2025-06-16 19:55:38'),
(600, 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 'HIS-001', 'Penguin Books', 'History', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(601, 'Discovery of India', 'Jawaharlal Nehru', 'HIS-002', 'Oxford Press', 'History', 1, 1, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(602, 'Physical Geography', 'Savindra Singh', 'GEO-001', 'Prayag Pustak', 'Geography', 3, 3, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(603, 'World Atlas', 'National Geographic', 'GEO-002', 'Nat Geo', 'Geography', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(604, 'The Prince', 'Niccolò Machiavelli', 'POL-001', 'Bantam Classics', 'Political Science', 1, 1, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(605, 'Politics in Pakistan', 'Ayesha Jalal', 'POL-002', 'Vanguard Books', 'Political Science', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(606, 'Introduction to Sociology', 'Anthony Giddens', 'SOC-001', 'Polity Press', 'Sociology', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(607, 'The Social Animal', 'Elliot Aronson', 'SOC-002', 'Worth Publishers', 'Sociology', 1, 1, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(608, 'Thinking, Fast and Slow', 'Daniel Kahneman', 'PSY-001', 'Farrar, Straus', 'Psychology', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(609, 'Atomic Habits', 'James Clear', 'PSY-002', 'Avery', 'Psychology', 3, 3, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(610, 'The Lean Startup', 'Eric Ries', 'BUS-001', 'Crown Business', 'Business Studies', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(611, 'Zero to One', 'Peter Thiel', 'BUS-002', 'Virgin Books', 'Business Studies', 1, 1, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(612, 'Ways of Seeing', 'John Berger', 'ART-001', 'Penguin Classics', 'Art & Design', 1, 1, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(613, 'The Design of Everyday Things', 'Don Norman', 'ART-002', 'Basic Books', 'Art & Design', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(614, 'Sophie\'s World', 'Jostein Gaarder', 'PHI-001', 'Orion', 'Philosophy', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(615, 'The Alchemist', 'Paulo Coelho', 'PHI-002', 'HarperTorch', 'Philosophy', 3, 3, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(616, 'Campbell Biology', 'Jane B. Reece', 'BIO-001', 'Pearson', 'Biology', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(617, 'Organic Chemistry', 'Paula Yurkanis Bruice', 'CHEM-001', 'Pearson', 'Chemistry', 1, 1, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(618, 'Fundamentals of Physics', 'Halliday & Resnick', 'PHY-001', 'Wiley', 'Physics', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(619, 'Peer-e-Kamil', 'Umera Ahmed', 'URD-NOV-001', 'Ferozsons', 'Urdu Novels', 3, 3, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(620, 'The Sealed Nectar', 'Safiur Rahman Mubarakpuri', 'ISL-001', 'Darussalam', 'Islamiat', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(621, 'Calculus: Early Transcendentals', 'James Stewart', 'MATH-001', 'Cengage', 'Mathematics', 1, 1, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(622, 'Kulliyat-e-Iqbal', 'Allama Iqbal', 'URD-001', 'Sang-e-Meel', 'Urdu', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(623, 'Introduction to Pakistan Studies', 'Ikram Rabbani', 'PAK-001', 'Caravan', 'Pakistan Studies', 3, 3, '2025-08-20 16:58:42', '2025-08-20 16:58:42'),
(624, 'Operating System Concepts', 'Abraham Silberschatz', 'CS-001', 'Wiley', 'Computer Science', 2, 2, '2025-08-20 16:58:42', '2025-08-20 16:58:42');

-- --------------------------------------------------------

--
-- Table structure for table `book_requests`
--

CREATE TABLE `book_requests` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `collected` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `collected_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_requests`
--

INSERT INTO `book_requests` (`id`, `book_id`, `user_id`, `request_date`, `status`, `notes`, `collected`, `expires_at`, `approved_at`, `collected_at`) VALUES
(68, 220, 41, '2025-08-17 22:57:48', 'approved', '', 1, NULL, NULL, '2025-08-19 14:29:49'),
(89, 220, 41, '2025-08-20 02:49:03', 'rejected', '', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `book_reservations`
--

CREATE TABLE `book_reservations` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reservation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','fulfilled','cancelled','expired') NOT NULL DEFAULT 'active',
  `priority_number` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `notified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ebooks`
--

CREATE TABLE `ebooks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cover_image` varchar(255) DEFAULT NULL,
  `type` enum('Pastpapers','outlines','ebooks') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ebooks`
--

INSERT INTO `ebooks` (`id`, `title`, `author`, `category`, `file_path`, `file_size`, `file_type`, `description`, `uploaded_by`, `created_at`, `cover_image`, `type`) VALUES
(1, 'React Interview Questions & Answers Guide', 'Felin Predator', 'Programming', '../uploads/ebooks/684154050fc11_ULTIMATE React Interview Questions & Answers Guide.pdf', '1.78 MB', 'pdf', 'React Interview Questions & Answers Guide', NULL, '2025-06-05 08:23:33', '../uploads/ebook_covers/684f09cbcd7c4.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('suggestion','complaint','appreciation') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `id` int(11) NOT NULL,
  `issued_book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issued_books`
--

CREATE TABLE `issued_books` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('issued','returned','overdue') NOT NULL DEFAULT 'issued',
  `fine_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issued_books`
--

INSERT INTO `issued_books` (`id`, `book_id`, `user_id`, `issue_date`, `return_date`, `actual_return_date`, `status`, `fine_amount`) VALUES
(93, 220, 41, '2025-08-19 20:24:50', '2025-08-27', '2025-08-19', 'returned', 0.00),
(103, 220, 41, '2025-08-19 09:29:49', '2025-09-02', '2025-08-19', 'returned', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `ip_address`, `attempt_time`, `success`) VALUES
(411, 'iqranoureench@gmail.com', '::1', '2025-08-23 16:50:20', 1),
(412, 'iqranoureench@gmail.com', '::1', '2025-08-23 17:25:21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(270, 41, 'Your reservation for \'A Brain Friendly Guide: Head first PHP & MySQL\' has been approved. Please collect the book from the library.', 0, '2025-08-19 20:24:50'),
(286, 41, 'Your book \'A Brain Friendly Guide: Head first PHP & MySQL\' has been returned successfully.', 0, '2025-08-19 09:27:16'),
(299, 41, 'Please collect your approved book \'A Brain Friendly Guide: Head first PHP & MySQL\'. It has now been issued to you. Due date: September 2, 2025.', 0, '2025-08-19 09:29:49'),
(302, 41, 'Your book \'A Brain Friendly Guide: Head first PHP & MySQL\' has been returned successfully.', 0, '2025-08-19 09:31:59'),
(347, 41, 'Your request for \'A Brain Friendly Guide: Head first PHP & MySQL\' was rejected because all available copies have been allocated.', 0, '2025-08-20 02:50:26');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `created_at`, `expires_at`) VALUES
(41, 41, '68cf8bf396181a20170f248fa5db5a8f642d9ed5b6e813d9ccfd3c05547d7970', '2025-08-18 17:49:28', '2025-08-18 13:49:28'),
(42, 35, '7f2e2f64abbd2d1e1d33e1c32fb91903ed99b60a5653307f3e2e7054111c1195', '2025-08-18 17:51:56', '2025-08-18 13:51:56');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `fine_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_details` text DEFAULT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation_requests`
--

CREATE TABLE `reservation_requests` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `unique_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('librarian','student','faculty') NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_status` varchar(20) DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `unique_id`, `name`, `email`, `password`, `role`, `department`, `phone`, `address`, `created_at`, `approval_status`, `approved_at`, `rejected_at`, `email_verified`, `email_verification_token`) VALUES
(35, 'LIB15970', 'Librarian', 'iqranoureench@gmail.com', '$2y$10$5u2GNPjA9Mtp9vG2mnQ64uloByZV8z032B/8Q8s0VeteIHBmgplxq', 'librarian', NULL, '03095810285', NULL, '2025-08-13 23:55:09', 'approved', NULL, NULL, 0, NULL),
(41, 'STU70547', 'Maleeha Mushtaq', 'maleehamushtaq1008@gmail.com', '$2y$10$VIniCetNz5e/OultgbM01ecreeY90ucoVw9Qz9DZgweFdeKG0XK9i', 'student', 'BS IT-8', '03466855225', NULL, '2025-08-17 22:55:36', 'approved', NULL, NULL, 0, NULL),
(80, 'STU11370', 'Iqra Noureen', 'iqrachaudhry374@gmail.com', '$2y$10$kcgeGiiUxQcl9WrevetDKOsZ88nsrttiDY.bUU.x54T25GRfPTuyu', 'student', 'BS IT-2', '03456893624', NULL, '2025-08-23 15:07:23', 'approved', '2025-08-23 15:08:02', NULL, 1, NULL),
(81, 'STU86075', 'noor', '123@gmail.com', '$2y$10$eFudr4zIYfilqh3.ZxRWtu6a0I1DoppPA1hFLzCzFA5frK.DmXS42', 'student', 'BS ENG-5', '03095810285', NULL, '2025-08-23 16:50:03', 'approved', '2025-08-23 16:50:24', NULL, 0, '23c22de9a93548f9c73d5e14a15d8f7e'),
(82, 'LIB42537', 'Admin Librarian', 'admin@library.com', '$2y$10$vyQ2omZV53bsrpxdS/30uOBO4rikXBLl3FBXVJmzQQrpenTKCK4me', 'librarian', NULL, NULL, NULL, '2025-08-23 17:38:19', 'pending', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `weed_off_books`
--

CREATE TABLE `weed_off_books` (
  `id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `book_title` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `removed_by` int(11) DEFAULT NULL,
  `removed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weed_off_books`
--

INSERT INTO `weed_off_books` (`id`, `book_id`, `book_title`, `reason`, `removed_by`, `removed_at`) VALUES
(1, 365, 'Politics of piety', 'damage', 35, '2025-08-14 00:31:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `book_reservations`
--
ALTER TABLE `book_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_book_priority` (`book_id`,`priority_number`),
  ADD KEY `idx_status_expires` (`status`,`expires_at`);

--
-- Indexes for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issued_book_id` (`issued_book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `issued_books`
--
ALTER TABLE `issued_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_time` (`identifier`,`attempt_time`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempt_time`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fine_id` (`fine_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservation_requests`
--
ALTER TABLE `reservation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_id` (`unique_id`);

--
-- Indexes for table `weed_off_books`
--
ALTER TABLE `weed_off_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `removed_by` (`removed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=625;

--
-- AUTO_INCREMENT for table `book_requests`
--
ALTER TABLE `book_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `book_reservations`
--
ALTER TABLE `book_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `issued_books`
--
ALTER TABLE `issued_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=413;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `reservation_requests`
--
ALTER TABLE `reservation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `weed_off_books`
--
ALTER TABLE `weed_off_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD CONSTRAINT `book_requests_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `book_reservations`
--
ALTER TABLE `book_reservations`
  ADD CONSTRAINT `book_reservations_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ebooks`
--
ALTER TABLE `ebooks`
  ADD CONSTRAINT `ebooks_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`issued_book_id`) REFERENCES `issued_books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issued_books`
--
ALTER TABLE `issued_books`
  ADD CONSTRAINT `issued_books_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issued_books_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`fine_id`) REFERENCES `fines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservation_requests`
--
ALTER TABLE `reservation_requests`
  ADD CONSTRAINT `reservation_requests_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `weed_off_books`
--
ALTER TABLE `weed_off_books`
  ADD CONSTRAINT `weed_off_books_ibfk_1` FOREIGN KEY (`removed_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
