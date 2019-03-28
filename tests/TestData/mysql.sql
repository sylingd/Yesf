CREATE TABLE `user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` tinyint(1) UNSIGNED DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id`, `role`, `email`, `password`, `name`) VALUES
(1, 1, 'admin@example', '$2y$10$igsnsusA15b9VB5Vz5ukj.eNjlNOiVyunPtz9/doo0/6nF80fL4Ha', 'Administrator');

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;