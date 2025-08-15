# Quick Fix Guide - Library System

## Mentioned Problems and Solutions

### 1. "Not Found" Error
**Problem:** The page does not exist on the server

**Solutions:**
1. Make sure Apache is running in XAMPP
2. Make sure mod_rewrite is enabled
3. Check the correct paths

**Diagnostic files:**
- `diagnostic.php` - Complete system diagnostic
- `fix_not_found.php` - Quick fix for "Not Found" problem
- `test_simple.php` - Simple PHP test

### 2. Buttons don't work
**Problem:** Buttons don't respond to clicks

**Solutions:**
1. Make sure JavaScript is enabled in the browser
2. Check for CSS and JS files
3. Check browser console for errors

**Diagnostic files:**
- `fix_buttons.php` - Fix button problems
- `test_buttons_page.php` - Button test page
- `assets/js/test_buttons.js` - Test JavaScript

### 3. Complete fix
**Complete fix file:**
- `complete_fix.php` - Fix all problems
- `test_complete.php` - Complete system test

## Quick Fix Steps

### Step 1: Problem diagnosis
```bash
# Navigate to system folder
cd C:\xampp1\htdocs\library_system\public

# Open browser and go to:
http://localhost/library_system/public/diagnostic.php
```

### Step 2: Complete fix
```bash
# Open browser and go to:
http://localhost/library_system/public/complete_fix.php
```

### Step 3: System test
```bash
# Complete test:
http://localhost/library_system/public/test_complete.php

# Button test:
http://localhost/library_system/public/test_buttons_page.php

# Simple test:
http://localhost/library_system/public/test_simple.php
```

## Error Checking

### 1. Browser console check
1. Press F12 to open developer tools
2. Go to "Console" tab
3. Look for red errors

### 2. PHP logs check
```bash
# PHP logs location in XAMPP:
C:\xampp1\php\logs\php_error_log
```

### 3. Apache logs check
```bash
# Apache logs location in XAMPP:
C:\xampp1\apache\logs\error.log
```

## Common Problems and Solutions

### Problem 1: Apache doesn't work
**Solution:**
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Make sure no other service uses port 80

### Problem 2: Database doesn't connect
**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check connection settings in `config/database.php`
3. Make sure database exists

### Problem 3: Sessions don't work
**Solution:**
1. Check PHP settings for sessions
2. Make sure temp folder is writable
3. Check cookie settings

### Problem 4: Files don't exist
**Solution:**
1. Check all required files exist
2. Check correct permissions
3. Check file paths

## Test Links

### Basic tests:
- [System diagnostic](diagnostic.php)
- [Complete fix](complete_fix.php)
- [Button test](test_buttons_page.php)
- [Simple test](test_simple.php)

### System tests:
- [Dashboard](dashboard.php)
- [Login page](login.php)
- [Employee management](router.php?module=administration&action=list)
- [Items list](router.php?module=items&action=list)
- [Items statistics](router.php?module=items&action=statistics)
- [Administration reports](router.php?module=administration&action=reports)

## Additional Tips

1. **Backup** before making any changes
2. **Test in separate environment** first
3. **Check logs** regularly to detect problems
4. **Keep updated** XAMPP and PHP

## Support

If problems persist:
1. Check error logs
2. Check server settings
3. Test in different browser
4. Check security settings
