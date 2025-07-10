1. Copy `.env.example` to `.env` and configure your credentials.
2. Create the database:
   ```sql
   CREATE DATABASE cloud_storage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
3. Import the schema:
   mysql -u root -p cloud_storage < schema.sql
