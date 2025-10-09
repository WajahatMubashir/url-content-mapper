# WordPress.org SVN Deployment Guide

## 📋 Overview

This guide will help you deploy version **1.3** of the URL Content Mapper plugin to WordPress.org.

## ✅ Pre-Deployment Checklist

All items have been verified and completed:

- ✅ All required files exist
- ✅ Version numbers are correct (1.3)
- ✅ PHP syntax is valid
- ✅ Changelog is updated
- ✅ Query parameter validation improvements added
- ✅ Edge case handling implemented

## 🚀 Deployment Steps

### Step 1: Verify Files (Optional)

Run the verification script to double-check everything:

```bash
cd /Users/wmubashir/Desktop/Plugin/url-content-mapper
./verify-files.sh
```

### Step 2: Deploy to WordPress.org

Run the automated deployment script:

```bash
./deploy-to-svn.sh
```

The script will:
1. ✓ Check if SVN is installed
2. ✓ Checkout/update your SVN repository to `~/svn-url-content-mapper`
3. ✓ Clean the trunk directory
4. ✓ Copy all plugin files (excluding .git, .claude, DOCS, etc.)
5. ✓ Show you what will be changed
6. ⚠️ **Ask for your confirmation before proceeding**
7. ✓ Add new files to SVN
8. ✓ Remove deleted files from SVN
9. ✓ Commit changes to trunk
10. ✓ Create and commit version 1.3 tag

### Step 3: Verify on WordPress.org

After deployment (usually takes 5-15 minutes):

1. Visit: https://wordpress.org/plugins/url-content-mapper/
2. Check that version 1.3 is showing
3. Verify the changelog is updated
4. Test downloading the plugin

## 📦 What Gets Deployed

The following files will be deployed:

```
✓ wp-url-content-mapper.php (main plugin file)
✓ readme.txt (WordPress.org description)
✓ functions.php (core functionality)
✓ admin-settings.php (admin interface)
✓ uninstall.php (cleanup on uninstall)
✓ assets/admin-script.js
✓ assets/admin-style.css
✓ assets/frontend-script.js
```

## 🚫 What Gets Excluded

The following files/folders are NOT deployed:

```
✗ .git (version control)
✗ .claude (AI assistant files)
✗ .DS_Store (macOS system files)
✗ DOCS (documentation folder)
✗ deploy-to-svn.sh (deployment script)
✗ verify-files.sh (verification script)
✗ *.log files
```

## 🔐 SVN Authentication

When you run the deployment script, SVN will prompt you for your WordPress.org credentials:

- **Username**: Your wordpress.org username
- **Password**: Your wordpress.org password

You can save these credentials when prompted to avoid entering them repeatedly.

## 📝 Version 1.3 Changelog

This release includes:

- **CRITICAL FIX**: Removed DOMContentLoaded wrapper to fix GA4 timing issues
- **CRITICAL FIX**: Script now loads in HEAD instead of footer for proper execution order
- Fixed URL pattern matching to use pathname instead of full href
- Improved query parameter handling for patterns like `/?wizard=true`
- **NEW**: Added validation for malformed query strings (e.g., `/?=value`, `/?#`)
- **NEW**: Added support for `/?` pattern to match pages with any query parameters
- **NEW**: Improved edge case handling for empty and whitespace query strings
- Fixed sanitization to preserve relative URL patterns like `/products/`
- Removed duplicate script enqueuing for better performance
- Added priority-based category matching to prevent conflicts
- WordPress.org compliance improvements

## ⚠️ Important Notes

1. **Backup First**: The deployment script creates automatic backups in your SVN directory
2. **Review Changes**: Always review the SVN status before confirming deployment
3. **Tag Versioning**: The script automatically creates a tag for version 1.3
4. **Can't Undo Easily**: SVN commits are permanent (though you can revert)
5. **Testing**: Make sure to test on a staging site before deploying to production

## 🆘 Troubleshooting

### SVN Not Installed

If you get "SVN is not installed", install it with Homebrew:

```bash
brew install subversion
```

### SVN Checkout Takes Forever

The initial checkout can take 5-10 minutes depending on your connection. This is normal.

### Authentication Failed

Make sure you're using your wordpress.org credentials, not your email or site admin credentials.

### Changes Not Showing on WordPress.org

It can take 5-15 minutes for changes to propagate. Clear your browser cache and check again.

### Need to Rollback

If something goes wrong, you can revert to the previous version:

```bash
cd ~/svn-url-content-mapper
svn revert -R trunk
svn update
```

## 📞 Support

- **Plugin Support**: https://wordpress.org/support/plugin/url-content-mapper/
- **SVN Documentation**: https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
- **Contact**: wajahat@example.com

---

**Ready to deploy?** Run `./deploy-to-svn.sh` to get started!
