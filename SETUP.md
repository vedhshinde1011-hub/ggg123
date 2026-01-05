# Ekam Care Website Setup Guide

## SMTP Configuration for Gmail

### Step 1: Enable 2-Factor Authentication
1. Go to your Google Account settings
2. Navigate to Security
3. Enable 2-Step Verification if not already enabled

### Step 2: Generate App Password
1. Go to Google Account settings > Security
2. Under "Signing in to Google", select "App passwords"
3. Select "Mail" as the app and "Other" as the device
4. Enter "Ekam Care Website" as the device name
5. Click "Generate"
6. Copy the 16-character password (remove spaces)

### Step 3: Update .env File
1. Open the `.env` file in your project
2. Replace `your_app_password_here` with the generated app password
3. Save the file

Example:
```
SMTP_PASS=abcd efgh ijkl mnop
```
Should become:
```
SMTP_PASS=abcdefghijklmnop
```

### Step 4: Install Dependencies
Run this command in your project directory:
```bash
composer install
```

### Step 5: Test the Setup
1. Open your website in a browser
2. Fill out the contact form
3. Submit the form
4. Check your email (ekamcareindia@gmail.com) for the form submission

## Security Notes
- Never commit the `.env` file to GitHub
- Add `.env` to your `.gitignore` file
- The app password is different from your regular Gmail password
- Keep your app password secure and don't share it

## Troubleshooting
- If emails aren't sending, check the PHP error logs
- Ensure your hosting provider allows SMTP connections
- Verify the app password is correct (no spaces)
- Make sure 2FA is enabled on your Gmail account

## Files Created
- `.env` - Environment variables (keep secure)
- `submit-form.php` - Form processing script
- `composer.json` - PHP dependencies
- `Index.html` - Updated with form functionality

## Next Steps
1. Install Composer if not already installed
2. Run `composer install` to install PHPMailer
3. Configure your `.env` file with the app password
4. Test the form submission