CREATE DATABASE IF NOT EXISTS `test`;

CREATE TABLE `test`.`user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `test`.`user` (`id`, `name`, `password`) VALUES
(1, 'Administrator', '$2y$10$igsnsusA15b9VB5Vz5ukj.eNjlNOiVyunPtz9/doo0/6nF80fL4Ha');

ALTER TABLE `test`.`user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `test`.`user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;