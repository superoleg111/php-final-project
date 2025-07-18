<?php
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Hashed password for admin: " . $hashedPassword . PHP_EOL;