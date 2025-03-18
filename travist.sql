-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 24 fév. 2025 à 21:59
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `travist`
--

--
-- Structure de la table `travel`
--

CREATE TABLE `travel` (
  `id` int(5) NOT NULL,
  `travel_name` varchar(25) NOT NULL,
  `people_number` int(2) NOT NULL,
  `total_price` float NOT NULL,
  `user_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `keypoints`
--

CREATE TABLE `keypoints` (
  `id` int(5) NOT NULL,
  `key_point_name` varchar(50) NOT NULL,
  `key_point_price` float NOT NULL,
  `key_point_start_date` date NOT NULL,
  `key_point_end_date` date NOT NULL,
  `key_point_cover` longblob DEFAULT NULL,
  `key_point_gps_location` varchar(50) NOT NULL,
  `city_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tags`
--
CREATE TABLE `tags` (
  `id` int(5) NOT NULL,
  `tag_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `tags`
--
CREATE TABLE `cities` (
  `id` int(5) NOT NULL,
  `city_name` varchar(25) NOT NULL,
  `city_country` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(5) NOT NULL,
  `user_name` varchar(25) NOT NULL,
  `user_email` varchar(40) NOT NULL,
  `user_password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `user_name`, `user_email`, `user_password`) VALUES
(3, 'admin', 'admin@gmail.com', '$2y$10$eT7Ig5l0anqSp5bOnI66gOxgUMNQ2hixnxVbBGEhQbNj84lTEI8We'),
(4, 'mathys', 'mat@gmail.com', '$2y$10$ek3sL/d9ZCReYX2RQUyLKuptGe75M61BwaEMEcx6p4SBChR8rjVVy'),
(5, 'JoJoB', 'jojo@gmail.com', '$2y$10$DFv.RB96.C7mpul8umwuXeZKopP5JFEjZWFs5xsSlsQm6GWqxqauC');


--
-- Structure de la table `assigned`
--

CREATE TABLE `assigned` (
  `travel_id` int(5) NOT NULL,
  `keypoint_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `tagged`
--

CREATE TABLE `tagged` (
  `keypoint_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour la table `keypoints`
--
ALTER TABLE `keypoints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_CITY` (`city_id`);

--
-- Index pour la table `travel`
--
ALTER TABLE `travel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_USER` (`user_id`);


--
-- Index pour la table `travel`
--
  ALTER TABLE `tags`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


--
-- Index pour la table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `assigned`
--
ALTER TABLE `assigned`
  ADD KEY `FK_TRAVEL` (`travel_id`),
  ADD KEY `FK_KEYPOINT_ASSIGNED` (`keypoint_id`);


--
-- Index pour la table `assigned`
--
ALTER TABLE `tagged`
  ADD KEY `FK_KEYPOINT` (`keypoint_id`),
  ADD KEY `FK_TAG` (`tag_id`);

--
-- Contraintes pour la table `travel`
--
ALTER TABLE `travel`
  ADD CONSTRAINT `FK_USER` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);


--
-- Contraintes pour la table `keypoints`
--
ALTER TABLE `keypoints`
  ADD CONSTRAINT `FK_CITY` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`);


--
-- Contraintes pour la table `assigned`
--
ALTER TABLE `assigned`
  ADD CONSTRAINT `FK_TRAVEL` FOREIGN KEY (`travel_id`) REFERENCES `travel` (`id`),
  ADD CONSTRAINT `FK_KEYPOINT_ASSIGNED` FOREIGN KEY (`keypoint_id`) REFERENCES `keypoints` (`id`);


--
-- Contraintes pour la table `tagged`
--
ALTER TABLE `tagged`
  ADD CONSTRAINT `FK_KEYPOINT` FOREIGN KEY (`keypoint_id`) REFERENCES `keypoints` (`id`),
  ADD CONSTRAINT `FK_TAG` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
