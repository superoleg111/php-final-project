-- passwords:
-- for admin: admin123
-- for alice: alice123
-- for bob: bob123
INSERT INTO users (id, name, email, password, role)
VALUES (1, 'Admin', 'admin@example.com', '$2y$10$pM4QYE9n.b5AoaUVfU78f.8cI7r1OLSI8XZ1rEdS1vxv.Wooh9At.', 'admin'),
       (2, 'Alice', 'alice@example.com', '$2b$12$j17ZTvWUmOPGtHxB6gdTSeNp9Qoo00QUqDIbyxZ8JXLCxAmTnZUrK', 'user'),
       (3, 'Bob', 'bob@example.com', '$2b$12$OWS4CSpb3W5j0F0qvk6RqeBEr0fvEsGBUI0jfpHvmAU/8p5jTjvnu', 'user');

INSERT INTO directories (id, user_id, name, parent_id, created_at)
VALUES (1, 2, 'Alice Root', NULL, NOW()),
       (2, 3, 'Bob Root', NULL, NOW());
