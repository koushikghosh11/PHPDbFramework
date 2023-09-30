# PHP MySQL Database Operations Library

This PHP library, **MySQLDbOps**, provides a set of classes and functions to simplify common database operations when working with MySQL databases. It offers a convenient way to connect to a MySQL database, perform CRUD (Create, Read, Update, Delete) operations, and manage database tables.

## Features

- **Database Connection**: The `DbConnection` class establishes a secure connection to a MySQL database using PDO, making it easy to connect and manage your database credentials.

- **Table Operations**: The `DbOperation` class extends `DbConnection` and provides methods for common table operations, including resetting a table, altering table settings, and setting the default table for operations.

- **Data Retrieval**: Easily retrieve data from your MySQL database with the `get` method. You can specify columns, conditions, ordering, and limits.

- **Data Insertion**: Insert data into your database with the `insert` method, which handles parameter binding and hashing of sensitive data, such as passwords.

- **Data Updating**: Update existing records in your database using the `update` method. It allows you to specify the columns to update and the conditions for the update.

- **Data Deletion**: Delete records from your database with the `delete` method, specifying the conditions for deletion.

- **Password Verification**: Verify user passwords securely using the `passwdVerify` method, which hashes and compares passwords.

- **Error Handling**: The library includes error handling for database operations, ensuring that any issues are gracefully handled and reported.

## How to Use

1. Include the `DbConnection.php` and `DbOperation.php` files in your PHP project.

2. Create an instance of the `DbOperation` class, specifying the necessary database connection details.

3. Use the provided methods to perform various database operations, such as retrieving data, inserting records, updating data, and deleting records.

```php
// Example usage:
$database = new MySQLDbOps\DbOperation('localhost', 'username', 'password', 'database_name');
$data = $database->get('column1, column2', ['condition' => 'value'], 'table_name', 'column1 ASC', '10');
```

4. Handle errors gracefully using try-catch blocks to capture exceptions thrown during database operations.

```php
try {
    // Perform database operations here
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
```

## Requirements

- PHP 7.0 or higher
- MySQL database

## License

This library is open-source and available under the MIT License. You are free to use and modify it in your projects.

## Contribution

Contributions and bug reports are welcome! If you have any suggestions or encounter issues, please open an issue on GitHub or submit a pull request.

**GitHub Repository**: [PHPDbFramework](https://github.com/koushikghosh11/PHPDbFramework)

Enjoy simplified MySQL database operations with this library!
