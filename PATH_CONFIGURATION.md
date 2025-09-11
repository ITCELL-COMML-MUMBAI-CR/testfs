# SAMPARK Path Configuration Guide

## Overview
The SAMPARK application now supports dynamic path detection, making it easy to deploy on different servers without code changes. You only need to comment/uncomment one line in the `.htaccess` file.

## How It Works
The application automatically detects:
- Server protocol (HTTP/HTTPS)
- Host name
- Base path (subdirectory or root)

All paths are calculated dynamically at runtime using the `Config::getAppUrl()` and `Config::getBasePath()` methods.

## Configuration Steps

### For Localhost Development (XAMPP)
In `public/.htaccess`, ensure this line is **UNCOMMENTED**:
```apache
RewriteBase /testfs/public/
```

And this line is **COMMENTED**:
```apache
# RewriteBase /
```

### For Production Server (Document Root)
In `public/.htaccess`, ensure this line is **COMMENTED**:
```apache
# RewriteBase /testfs/public/
```

And this line is **UNCOMMENTED**:
```apache
RewriteBase /
```

### For Production Server (Subdirectory)
If your application is in a subdirectory on the production server:
```apache
RewriteBase /your-subdirectory/
```

## Testing Your Configuration

1. Visit `http://yourserver/testfs/test_paths.php` (localhost) or `http://yourserver/test_paths.php` (production)
2. Check that all paths are correctly detected
3. Verify that the application loads properly

## Files Modified

The following changes were made to support dynamic paths:

1. **`src/config/Config.php`**:
   - Removed hardcoded `APP_URL` and `BASE_PATH` constants
   - Added `getAppUrl()` and `getBasePath()` methods for dynamic detection
   - Updated path calculation methods

2. **`public/.htaccess`**:
   - Added comments to easily switch between localhost and production

3. **All PHP templates and controllers**:
   - Replaced `Config::APP_URL` with `Config::getAppUrl()` calls

## Troubleshooting

### Issue: CSS/JS files not loading
**Solution**: Check the RewriteBase setting in `.htaccess`

### Issue: Links pointing to wrong URLs
**Solution**: Verify that `Config::getAppUrl()` returns the correct base URL

### Issue: File uploads not working
**Solution**: Check upload directory permissions and path settings

## Benefits

- ✅ No code changes needed for different environments
- ✅ Automatic protocol detection (HTTP/HTTPS)
- ✅ Works in subdirectories and document root
- ✅ Single configuration change via .htaccess
- ✅ Backward compatible with existing code

## Legacy Support

The application maintains backward compatibility. All existing `Config::APP_URL` references have been updated to use the new dynamic methods automatically.