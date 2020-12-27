
CREATE TABLE `users` (
    `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `ip` varchar(25),
    `date` timestamp,
    `path` text,
    `method` varchar(10),
    `response_code` int,
    `response_size` int,
    `user_agent` text
);
