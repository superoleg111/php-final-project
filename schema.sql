CREATE TABLE `users`
(
    `id`       int(11) NOT NULL AUTO_INCREMENT,
    `name`     varchar(255) NOT NULL,
    `email`    varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role`     varchar(20)  NOT NULL DEFAULT 'user',
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `directories`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `user_id`    int(11) NOT NULL,
    `name`       varchar(255) NOT NULL,
    `parent_id`  int(11) DEFAULT NULL,
    `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY          `user_id` (`user_id`),
    KEY          `parent_id` (`parent_id`),
    CONSTRAINT `directories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `directories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `directories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `files`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `user_id`      int(11) NOT NULL,
    `directory_id` int(11) DEFAULT NULL,
    `filename`     varchar(255) NOT NULL,
    `stored_name`  varchar(255) NOT NULL,
    `mime_type`    varchar(100)          DEFAULT NULL,
    `size`         bigint(20) DEFAULT NULL,
    `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY            `user_id` (`user_id`),
    KEY            `directory_id` (`directory_id`),
    CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `files_ibfk_2` FOREIGN KEY (`directory_id`) REFERENCES `directories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `file_user_access`
(
    `file_id`    int(11) NOT NULL,
    `user_id`    int(11) NOT NULL,
    `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`file_id`, `user_id`),
    KEY          `user_id` (`user_id`),
    CONSTRAINT `file_user_access_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
    CONSTRAINT `file_user_access_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_resets`
(
    `email`      varchar(255) NOT NULL,
    `token`      varchar(255) NOT NULL,
    `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`email`, `token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
