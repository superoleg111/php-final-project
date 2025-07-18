-- Seed users (with role column)
INSERT INTO `users` (`name`, `email`, `password`, `role`)
VALUES ('Alice', 'alice@example.com', '$2y$10$e0NRH.qzWZq0Z2wPX3cKp.O5hM/6xv2k9RZVuU/LPvO6GfE1q9eE6', 'user'),
       ('Bob', 'bob@example.com', '$2y$10$7sYQ5Hg6hqoP5Zr.EWHUCe.5vOZl0PNpoYoGaCBf4Rtc6biZ8Hqza', 'admin');

-- 2) One seed file (example.txt) — it will be id=2
INSERT INTO `files` (`id`, `user_id`, `filename`, `stored_name`, `mime_type`, `size`)
VALUES (2, 1, 'example.txt', 'seed_example.txt', 'text/plain', 20);

-- 3) Public share of the *existing* PDF (id=1) and the new example (id=2)
INSERT INTO `file_shares` (`file_id`, `token`)
VALUES (1, 'publictoken123'),
       (2, 'publictoken456');

-- 4) User‐to‐user access: Bob (user_id=2) gave Alice (user_id=1) access to example.txt (file_id=2)
INSERT INTO `file_user_access` (`file_id`, `user_id`)
VALUES (2, 1);
