## System Requirements

* PHP **7.4+**
* MySQL / MariaDB **5.7+**
* Composer **2.0+**
* [XAMPP](https://www.apachefriends.org/) or equivalent local LAMP/WAMP stack
* Write permissions to a `storage/` folder in project root

## Installation & Setup

```bash
# 1. Clone the repository
https://github.com/superoleg111/php-final-project.git
cd php-final-project

# 2. Install PHP dependencies
composer install

# 3. Create the database in MySQL or MariaDB
CREATE DATABASE cloud_storage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Import the schema (use backup.sql)
mysql -u root -p cloud_storage < backup.sql
# Note: schema.sql is broken, do not use it.

# 5. If using XAMPP, move this project to htdocs/
# Then access it via: http://localhost/php-final-project/public
```

---

## API Endpoints

### Authentication & Profile

```http
POST   /login              # Log in (returns session cookie)
GET   /logout             # Log out
GET    /me                 # Get current user profile
POST   /reset_password     # Trigger password-reset email link
```

### User

```http
GET    /users/list            # List all users (id, name, email)
GET    /users/get/{id}        # Get one user’s details
PUT    /users/update          # Update own profile
```

### Admin (requires role='admin')

```http
GET    /admin/users/list             # List all users
GET    /admin/users/get/{id}         # Get user by id
PUT    /admin/users/update/{id}      # Update any user’s data
DELETE /admin/users/delete/{id}      # Delete a user (not self)
```

### Files

```http
GET    /files/list            # List your files
GET    /files/get/{id}        # Get file metadata
POST   /files/add             # Upload a new file
PUT    /files/rename          # Rename a file (JSON: old & new names)
DELETE /files/remove/{id}     # Delete a file by ID
```

### Directories

```http
POST   /directories/add             # Create a directory (JSON: name, parent_id)
PUT    /directories/rename          # Rename a directory (JSON: id, new_name)
GET    /directories/get/{id}        # List folder contents
DELETE /directories/delete/{id}     # Delete a directory (and subfolders)
```

### Sharing

```http
GET    /files/share/{file_id}               # List users the file is shared with
PUT    /files/share/{file_id}/{user_id}     # Share file with another user
DELETE /files/share/{file_id}/{user_id}     # Revoke access for a user
```
