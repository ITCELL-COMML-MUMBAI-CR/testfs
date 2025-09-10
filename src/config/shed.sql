-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2025 at 11:34 AM
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
-- Database: `u473452443_sampark`
--

-- --------------------------------------------------------

--
-- Table structure for table `shed`
--

CREATE TABLE `shed` (
  `ShedID` int(11) NOT NULL,
  `Zone` varchar(10) NOT NULL,
  `Division` varchar(10) NOT NULL,
  `Terminal` varchar(255) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shed`
--

INSERT INTO `shed` (`ShedID`, `Zone`, `Division`, `Terminal`, `Name`, `Type`) VALUES
(1011, 'CR', 'BB', 'KLMG', 'Kalamboli', NULL),
(1012, 'CR', 'BB', 'KYN', 'Kalyan', NULL),
(1013, 'CR', 'BB', 'NGTN', 'Nagothane', NULL),
(1014, 'CR', 'BB', 'NGSM', 'New Mulund', NULL),
(1015, 'CR', 'BB', 'PEN', 'Pen', NULL),
(1016, 'CR', 'BB', 'ROHA', 'Roha', NULL),
(1017, 'CR', 'BB', 'TPND', 'Taloja Panchanand', NULL),
(1018, 'CR', 'BB', 'TMBY', 'Trombay', NULL),
(1019, 'CR', 'BB', 'TAPG', 'Turbhe ', NULL),
(1020, 'CR', 'BB', 'URAN', 'Uran', NULL),
(1021, 'CR', 'BB', 'IGP', 'Igatpuri(Titoli Yard)', NULL),
(1022, 'CR', 'BSL', 'AK', 'Akola', NULL),
(1023, 'CR', 'BSL', 'BD', 'Badnera ', NULL),
(1024, 'CR', 'BSL', 'BGN', 'Borgaon', NULL),
(1025, 'CR', 'BSL', 'BSGS', 'Bhusawal', NULL),
(1026, 'CR', 'BSL', 'DHI', 'Dhule', NULL),
(1027, 'CR', 'BSL', 'JL', 'Jalgaon', NULL),
(1028, 'CR', 'BSL', 'KMN', 'Khamgaon ', NULL),
(1029, 'CR', 'BSL', 'KNW', 'Khandwa', NULL),
(1030, 'CR', 'BSL', 'KW', 'Kherwadi', NULL),
(1031, 'CR', 'BSL', 'LS', 'Lasalgaon', NULL),
(1032, 'CR', 'BSL', 'MKU', 'Malkapur', NULL),
(1033, 'CR', 'BSL', 'MMR', 'Manmad', NULL),
(1034, 'CR', 'BSL', 'NGN', 'Nandgaon', NULL),
(1035, 'CR', 'BSL', 'NK', 'Nasik Road', NULL),
(1036, 'CR', 'BSL', 'NR', 'Niphad', NULL),
(1037, 'CR', 'BSL', 'PS', 'Paras', NULL),
(1038, 'CR', 'BSL', 'SAV', 'Savda', NULL),
(1039, 'CR', 'BSL', 'DVL', 'Devlali', NULL),
(1040, 'CR', 'BSL', 'AAK', 'Ankai kila', NULL),
(1041, 'CR', 'NGP', 'Ajni', 'Ajni', NULL),
(1042, 'CR', 'NGP', 'BPQ', 'Ballarshah', NULL),
(1043, 'CR', 'NGP', 'BZU', 'Betul', NULL),
(1044, 'CR', 'NGP', 'BTBR', 'Butibori ', NULL),
(1045, 'CR', 'NGP', 'CD', 'Chandrapur', NULL),
(1046, 'CR', 'NGP', 'CKNI', 'Chikni Road', NULL),
(1047, 'CR', 'NGP', 'DMN', 'Dhamangaon', NULL),
(1048, 'CR', 'NGP', 'GGS', 'Ghughus', NULL),
(1049, 'CR', 'NGP', 'HGT', 'Hinganghat', NULL),
(1050, 'CR', 'NGP', 'KSWR', 'Kalmeshwar', NULL),
(1051, 'CR', 'NGP', 'KAYR', 'Kayar', NULL),
(1052, 'CR', 'NGP', 'PMKT', 'Pimpalkutti', NULL),
(1053, 'CR', 'NGP', 'PLO', 'Pulgaon', NULL),
(1054, 'CR', 'NGP', 'RAJR', 'Rajur', NULL),
(1055, 'CR', 'NGP', 'RNGS', 'Rajur (NEW)', NULL),
(1056, 'CR', 'NGP', 'TAE', 'Tadali', NULL),
(1057, 'CR', 'NGP', 'WANI', 'Wani', NULL),
(1058, 'CR', 'NGP', 'WR', 'Wardha', NULL),
(1059, 'CR', 'NGP', 'WNGS', 'Wani New', NULL),
(1060, 'CR', 'PUNE', 'BRMT', 'Baramati ', NULL),
(1061, 'CR', 'PUNE', 'GRMT', 'Gudmarket', NULL),
(1062, 'CR', 'PUNE', 'LNN', 'Lonand', NULL),
(1063, 'CR', 'PUNE', 'MRJ', 'Miraj', NULL),
(1064, 'CR', 'PUNE', 'SLI', 'Sangli', NULL),
(1065, 'CR', 'PUNE', 'SSV', 'Sasvad Road', NULL),
(1066, 'CR', 'PUNE', 'STR', 'Satara', NULL),
(1067, 'CR', 'PUNE', 'FSG', 'Phursungi', NULL),
(1068, 'CR', 'PUNE', 'ANG', 'Ahmednagar', NULL),
(1069, 'CR', 'PUNE', 'BAP', 'Belapur', NULL),
(1070, 'CR', 'PUNE', 'DD', 'Daund', NULL),
(1071, 'CR', 'PUNE', 'KPG', 'Kopergaon', NULL),
(1072, 'CR', 'PUNE', 'RRI', 'Rahuri', NULL),
(1073, 'CR', 'PUNE', 'YL', 'Yeola', NULL),
(1074, 'CR', 'PUNE', 'VL', 'Vilad', NULL),
(1075, 'CR', 'PUNE', 'NYDO', 'Narayandoho', NULL),
(1076, 'CR', 'PUNE', 'SGND', 'Shir Gonda Road', NULL),
(1077, 'CR', 'PUNE', 'KK', 'kharki(only for militry traffic)', NULL),
(1078, 'CR', 'SUR', 'ARAG', 'Arag', NULL),
(1079, 'CR', 'SUR', 'KVK', 'Kavthe Mahakal', NULL),
(1080, 'CR', 'SUR', 'BGVN', 'Bhigvan', NULL),
(1081, 'CR', 'SUR', 'TJSP', 'Taj Sultanpur ', NULL),
(1082, 'CR', 'SUR', 'SUR', 'Solapur', NULL),
(1083, 'CR', 'SUR', 'WADI', 'Wadi', NULL),
(1084, 'CR', 'SUR', 'KWV', 'Kurduwadi', NULL),
(1085, 'CR', 'SUR', 'LUR', 'Latur', NULL),
(1086, 'CR', 'SUR', 'UMD', 'Osmanabad', NULL),
(1087, 'CR', 'SUR', 'PVR', 'Pandharpur', NULL),
(1088, 'CR', 'SUR', 'BTW', 'Barsi Town', NULL),
(1089, 'CR', 'SUR', 'BALE', 'Bale ', NULL),
(1090, 'CR', 'BB', 'BPTG/ BPTV', 'Mumbai Port Trust Railway', NULL),
(1091, 'CR', 'BB', 'BIRD', 'Bhiwandi Roaad', NULL),
(1092, 'CR', 'BSL', 'BAU', 'Burhanpur', NULL),
(1093, 'CR', 'BSL', 'CSN', 'Chalisgaon', NULL),
(1094, 'CR', 'BSL', 'RV', 'Raver', NULL),
(1095, 'CR', 'BSL', 'KBSN', 'Kasbesukene', NULL),
(1096, 'CR', 'BSL', 'KJ', 'kajgaon', NULL),
(1097, 'CR', 'NGP', 'MTY', 'Multai', NULL),
(1098, 'CR', 'NGP', 'PAR', 'Pandurna', NULL),
(1099, 'CR', 'NGP', 'HRG', 'Hirdagarh', NULL),
(1100, 'CR', 'PUNE', 'CCH', 'Chinchwad', NULL),
(1101, 'CR', 'PUNE', 'LONI', 'Loni', NULL),
(1102, 'CR', 'PUNE', 'KRD', 'Karad', NULL),
(1103, 'CR', 'SUR', 'TLT', 'Tilati', NULL),
(1104, 'CR', 'NGP', 'DELI', 'Deoli', NULL),
(1105, 'CR', 'NGP', 'MJY', 'Marmjhiri', NULL),
(1106, 'CR', 'NGP', 'KRSP', 'Khirsadoh', NULL),
(1107, 'CR', 'NGP', 'PUX', 'Parasia', NULL),
(1108, 'CR', 'BB', 'CRTK', 'TURBHE CONTAINER DEPOT', NULL),
(1109, 'CR', 'BB', 'JSWD', 'M/S JSW STEEL LTD.', NULL),
(1110, 'CR', 'BB', 'JSWV', 'M/S JSW STEEL COATED PRODUCTS LTD.', NULL),
(1111, 'CR', 'BB', 'NSKG', 'NAVAL SIDING KARANJA, URAN CITY', NULL),
(1112, 'CR', 'BB', 'PATP', 'ADANI AGRO LOGISTICS LTD', NULL),
(1113, 'CR', 'BB', 'PNCS', 'M/S NAVKAR CORP. LTD', NULL),
(1114, 'CR', 'BB', 'PPDP', 'M/S PNP MARITIME SERVICES LTD', NULL),
(1115, 'CR', 'BB', 'TVSG', 'RASTRIYA CHEMICAL AND FERT.SIDING THAL VAISHET.', NULL),
(1116, 'CR', 'BB', 'DRTA', 'DRONAGIRI RAIL TERMINAL', NULL),
(1117, 'CR', 'BB', 'MBPP', 'M/S BHARAT PETROLEUM CORPORATION LTD. SIDING AT URAN', NULL),
(1118, 'CR', 'BB', 'CWJC', 'Central Warehousing Corporation', NULL),
(1119, 'CR', 'BB', 'MIOJ', 'IOT INFRASTRUCTURE AND ENERGY SERVICES LTD', NULL),
(1120, 'CR', 'BB', 'BCCK', 'M/S BULK CEMENT CORPORATION LTD. SDG KALAMBOLI', NULL),
(1121, 'CR', 'BB', 'KFCG', 'FOOD CORPN. OF INDIA SDG, KALAMBOLI EXCHANGE YARD', NULL),
(1122, 'CR', 'BB', 'KSAG', 'STEEL AUTHORITY OF INDIA LTD. SDG, KALAMBOLI EXC', NULL),
(1123, 'CR', 'BB', 'KTIG', 'TISCO SIDING, KALAMBOLI EXCHANGE YARD', NULL),
(1124, 'CR', 'BB', 'MILK', 'M/S CENTRAL WAREHOUSING CORPORATION', NULL),
(1125, 'CR', 'BB', 'VSPG', 'VISAKHA PATNAM STEEL PROJECT SDG, KALAMBOLI', NULL),
(1126, 'CR', 'BB', 'BRSG', 'BHARAT PETROLEUM CORPORATION SIDING, TROMBAY', NULL),
(1127, 'CR', 'BB', 'FZSG', 'RASTRIYA CHEMICAL SIDING, TROMBAY', NULL),
(1128, 'CR', 'BB', 'TTPS', 'TATA THERMAL POWER STATION SIDING, TROMBAY', NULL),
(1129, 'CR', 'BB', 'VOSG', 'HINDUSTAN PETROLEUM CORPORATION SIDING, TROMBAY', NULL),
(1130, 'CR', 'BB', 'VIR#', 'WELSPUN MAXSTEEL. LTD.', NULL),
(1131, 'CR', 'BB', 'NRSG', 'National Rayon Corporation', NULL),
(1132, 'CR', 'BB', 'LNL', 'Concrete India Ltd.', NULL),
(1133, 'CR', 'BB', 'CCIK', 'Cotton Corporation of India', NULL),
(1134, 'CR', 'BB', 'TPHG', 'TATA POWER HOUSE SIDING TROMBAY', NULL),
(1135, 'CR', 'BSL', 'BESG', 'MSEB SIDING PARAS', NULL),
(1136, 'CR', 'BSL', 'BPCP', 'BPCL SDG. PANEVADI', NULL),
(1137, 'CR', 'BSL', 'BSSG', 'RESERVE PETROL DEPOT ASC BURMAH SHELL SDG, BHUSAV', NULL),
(1138, 'CR', 'BSL', 'DMSG', 'DEVLALI MILITARY SIDING, DEVLALI', NULL),
(1139, 'CR', 'BSL', 'MFSG', 'MAHARASTRA STATE ELECT BOARD SDG, BHUSAVAL', NULL),
(1140, 'CR', 'BSL', 'MQSG', 'MSEB THERMAL POWER STATION SDG, ODHA', NULL),
(1141, 'CR', 'BSL', 'NMSG', 'NEPA LIMITED SIDING', NULL),
(1142, 'CR', 'BSL', 'OCSB', 'ORIENT CEMENT SIDING BHADLI', NULL),
(1143, 'CR', 'BSL', 'RPLW', 'M/S RATANINDIA POWER LTD', NULL),
(1144, 'CR', 'BSL', 'GDSG', 'Grain Depot Siding (FCI) MANMAD', NULL),
(1145, 'CR', 'BSL', 'NKSG', 'Indian Secrurity press siding', NULL),
(1146, 'CR', 'BSL', 'IOC', 'POL SIDING FOR IOC LTD. SHIRUD', NULL),
(1147, 'CR', 'BSL', 'POLG', 'POL SIDING FOR IOC LTD. GAIGAON', NULL),
(1148, 'CR', 'NGP', 'DMGM', 'Dinesh OCM Makardhokara-III Gati Shakti Multi Modal CargoTerminal', NULL),
(1149, 'CR', 'NGP', 'MBCB', 'Ballarpur Colliery Siding', NULL),
(1150, 'CR', 'NGP', 'DCSG', 'East Dongr Chikhli Colliery Siding', NULL),
(1151, 'CR', 'NGP', 'GSG', 'Ghugus Old Colliery Siding', NULL),
(1152, 'CR', 'NGP', 'HLSG', 'Hindustan Lalpeth Colliery Siding', NULL),
(1153, 'CR', 'NGP', 'CGM', 'Chargaon Colliery Siding', NULL),
(1154, 'CR', 'NGP', 'MJSG', 'New Majri Colliery Siding', NULL),
(1155, 'CR', 'NGP', 'MNSG', 'Old Majri Colliery Siding', NULL),
(1156, 'CR', 'NGP', 'CSID', 'Mohan Colliery Siding, Palachouri', NULL),
(1157, 'CR', 'NGP', 'RJSG', 'Rajur Colliery Siding', NULL),
(1158, 'CR', 'NGP', 'RKSG', 'Rawanwara Khas Colliery Siding', NULL),
(1159, 'CR', 'NGP', 'UMSG', 'Umred Colliery Siding', NULL),
(1160, 'CR', 'NGP', 'MKCW', 'Kartikey Coal Washries Private Limited', NULL),
(1161, 'CR', 'NGP', 'KECM', 'Karnataka Coal Mines Siding', NULL),
(1162, 'CR', 'NGP', 'CESG', 'Associated Cement Co. Siding', NULL),
(1163, 'CR', 'NGP', 'MLSW', 'M/s Uttam value Steel Industries Ltd. (PFT)', NULL),
(1164, 'CR', 'NGP', 'MELG', 'Chandrapur Ferro Alloy Plant', NULL),
(1165, 'CR', 'NGP', 'NTPG', 'New Thermal Power Station Siding', NULL),
(1166, 'CR', 'NGP', 'MPBG', 'Madhya Pradesh Elecricity Board Siding', NULL),
(1167, 'CR', 'NGP', 'FNSG', 'Food Corporation of India Limited', NULL),
(1168, 'CR', 'NGP', 'FBSG', 'Buffer Storage Godown Siding', NULL),
(1169, 'CR', 'NGP', 'BROL', 'POL Siding for M/s BPCL', NULL),
(1170, 'CR', 'NGP', 'IOBT', 'POL Siding for IOC & BPCL', NULL),
(1171, 'CR', 'NGP', 'FFSG', 'Filling Factory Siding', NULL),
(1172, 'CR', 'NGP', 'POSG', 'Ordinance Depot Military Siding', NULL),
(1173, 'CR', 'NGP', 'AMSG', 'Air Force Siding', NULL),
(1174, 'CR', 'NGP', 'PWCL', 'M/s Sai Wardha Power Corporation Limited', NULL),
(1175, 'CR', 'NGP', 'PVIT', 'M/s Vimla Infrastructure India Limited (PFT)', NULL),
(1176, 'CR', 'NGP', 'PMEC', 'M/s GMR Warora Energy Limited Siding', NULL),
(1177, 'CR', 'NGP', 'MDIT', 'M/s Dhariwal Infrastructure Limited', NULL),
(1178, 'CR', 'NGP', 'VIPS', 'M/s Vidhrbha Industries Private Limited.', NULL),
(1179, 'CR', 'NGP', 'DLIB', 'M/s. Distribution Logistics Infrastructure Private limited', NULL),
(1180, 'CR', 'NGP', 'PCPK', 'M/s. Multi Model Logistic Park', NULL),
(1181, 'CR', 'NGP', 'EOLD', 'M/s. Nayara Energy Ltd.', NULL),
(1182, 'CR', 'NGP', 'PALB', 'M/s. Adani Logistics Ltd', NULL),
(1183, 'CR', 'NGP', 'RGTM', 'M/s Reliance Cement Co. Pvt. Ltd', NULL),
(1184, 'CR', 'NGP', 'JCSK', 'M/S JSW STEEL COATED PRODUCTS LTD.', NULL),
(1185, 'CR', 'NGP', 'NLGS', 'Nagpur MMLP Gati Shakti Multi Modal Cargo Terminl', NULL),
(1186, 'CR', 'NGP', 'FWSM', 'M/s Fuelco Washeries (India) Limited', NULL),
(1187, 'CR', 'PUNE', 'BFSG', 'Bharat Forge Comp Siding', NULL),
(1188, 'CR', 'PUNE', 'UTCU', 'M/s Ultratech cement siding', NULL),
(1189, 'CR', 'PUNE', 'GHSG', 'Food Corporation Siding, Pune', NULL),
(1190, 'CR', 'PUNE', 'PPCP', 'M/s Penna cement Industries ltd.', NULL),
(1191, 'CR', 'PUNE', 'CWHC', 'Central Warehouse siding', NULL),
(1192, 'CR', 'PUNE', 'BPAL', 'M/s BPCL private siding', NULL),
(1193, 'CR', 'PUNE', 'HPLC', 'Hindustan Petroleum Corporation’s Oil, Bhilvadi', NULL),
(1194, 'CR', 'PUNE', 'HPLG', 'Hindustan Petroleum Corporation’s Oil Terminal siding Loni', NULL),
(1195, 'CR', 'PUNE', 'SCGP', 'M/s Shree cements', NULL),
(1196, 'CR', 'PUNE', 'CMSG', 'Central Ordinance Depot', NULL),
(1197, 'CR', 'PUNE', 'CPSG', 'Ordinance Depot siding , Talegaon', NULL),
(1198, 'CR', 'PUNE', 'DASG', 'Dehu Ammunition Depot', NULL),
(1199, 'CR', 'PUNE', 'KASG', 'Ammunition factory siding', NULL),
(1200, 'CR', 'PUNE', 'KFSG', 'High Explosive Factory Siding', NULL),
(1201, 'CR', 'PUNE', 'KVSG', 'Armoured Fighting Vehicle depot Siding', NULL),
(1202, 'CR', 'SUR', 'WDSG', 'ACC Cement Siding/Wadi', NULL),
(1203, 'CR', 'SUR', 'MBSH', 'UltraTech Cement Ltd Siding/Hotgi', NULL),
(1204, 'CR', 'SUR', 'CCCT', 'Chettinad Cement Cor Ltd/Tilati', NULL),
(1205, 'CR', 'SUR', 'ZCT', 'Zuari Cement Siding/Tilati', NULL),
(1206, 'CR', 'SUR', 'HPSG', 'Indian Oil Corporation Ltd/Hirenanduru', NULL),
(1207, 'CR', 'SUR', 'PIOP', 'Indian Oil Corporation Ltd/Pakni', NULL),
(1208, 'CR', 'SUR', 'PSIA', 'Indian Oil Corporation Ltd/Akolner', NULL),
(1209, 'CR', 'SUR', 'PSNH', 'National Thermal Power Plant(NTPC)/Hotgi', NULL),
(1210, 'CR', 'SUR', 'SDSG', 'Jaypee Cement Siding, Shahabad', NULL),
(1211, 'CR', 'SUR', 'BPGH', 'M/s BPCL', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shed`
--
ALTER TABLE `shed`
  ADD PRIMARY KEY (`ShedID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shed`
--
ALTER TABLE `shed`
  MODIFY `ShedID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1212;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
