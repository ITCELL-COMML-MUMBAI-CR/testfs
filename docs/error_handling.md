# SAMPARK Error Handling Guidelines

This document outlines the standardized approach for handling and logging errors within the SAMPARK application.

## Error Logging Configuration

All errors are now logged to `logs/error.log` through the following mechanisms:

1. PHP's built-in error logging configured to use our log file
2. Custom error handlers that capture various error types
3. The `Config::logError()` method for explicit error logging

## How to Log Errors

### Preferred Method: Using Config::logError()

Always use the `Config::logError()` method to log errors:

```php
try {
    // Some risky operation
} catch (Exception $e) {
    Config::logError("Operation failed: " . $e->getMessage());
    // Handle the error appropriately
}
```

You can also provide additional context:

```php
Config::logError("Operation failed: " . $e->getMessage(), [
    'user_id' => $userId,
    'operation' => 'data_export',
    'file_path' => $filePath
]);
```

### Error Types

Different error types are automatically identified and logged:

- PHP Errors (E_ERROR, E_WARNING, etc.)
- Uncaught Exceptions
- Fatal Errors
- Custom Application Errors

### Error Handling Pattern

For consistency, follow this pattern in try-catch blocks:

```php
try {
    // Begin transaction if using database
    $this->db->beginTransaction();
    
    // Perform operations
    
    // Commit if successful
    $this->db->commit();
    
    // Return success response
    
} catch (Exception $e) {
    // Rollback if using database
    if (isset($this->db)) {
        $this->db->rollback();
    }
    
    // Log the error with appropriate context
    Config::logError("Error type: " . $e->getMessage(), [
        'context' => 'relevant_data',
        'operation' => 'operation_name'
    ]);
    
    // Return appropriate error response
    $this->json([
        'success' => false,
        'message' => 'User-friendly error message'
    ], 500);
}
```

## Debugging and Monitoring

### Viewing Logs

The error log file is located at `logs/error.log` and contains timestamped entries with error details.

### Development vs. Production

In development mode (ENVIRONMENT = 'development'):
- Errors are displayed on screen
- All errors are logged to the error log

In production mode:
- Errors are NOT displayed on screen for security
- All errors are still logged to the error log
- Custom error pages are shown to users

## Best Practices

1. **Always log errors** - Don't silently fail
2. **Use descriptive messages** - Include what operation was being performed
3. **Include context** - Add relevant data to help with debugging
4. **Handle database transactions properly** - Always rollback on error
5. **Provide user-friendly error messages** - Don't expose technical details to users
6. **Categorize errors** - Use prefixes like "Database Error:", "API Error:", etc.
