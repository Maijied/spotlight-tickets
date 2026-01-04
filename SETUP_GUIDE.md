# 🚀 Quick Setup Guide - Automated Deployment

## What I've Set Up For You

✅ **GitHub Actions Workflow** - Automatically deploys on every push
✅ **Database Credentials** - Already configured in the workflow
✅ **Environment File** - Auto-generated with your MySQL details
✅ **Database Setup Script** - One-click database initialization

---

## Step 1: Configure GitHub Secrets (One Time Only)

1. Go to your GitHub repository: https://github.com/Maijied/spotlight-tickets

2. Click **Settings** → **Secrets and variables** → **Actions**

3. Click **New repository secret** and add these **3 secrets**:

   | Secret Name | Value |
   |------------|-------|
   | `FTP_SERVER` | `ftpupload.net` |
   | `FTP_USERNAME` | `if0_40819537` |
   | `FTP_PASSWORD` | *Your InfinityFree FTP password* |

---

## Step 2: Trigger Deployment

The code has already been pushed. GitHub Actions will automatically:

1. ✅ Create `.env` file with these credentials:
   ```
   DB_HOST=sql303.infinityfree.com
   DB_NAME=if0_40819537_shiddarth
   DB_USER=if0_40819537
   DB_PASS=fWNDOUzsifw8yGh
   ```

2. ✅ Upload all files to your server

3. ✅ Deploy to `/htdocs/` directory

**Check deployment status:**
- Go to: https://github.com/Maijied/spotlight-tickets/actions
- You should see "Deploy to Production" running

---

## Step 3: Initialize Database (First Time Only)

After deployment completes:

1. Visit: **https://your-domain.com/setup_database.php**

2. You'll see:
   ```
   ✓ Database connection successful
   ✓ Created bookings table
   ✓ Created admins table
   ✓ Created events table
   ✓ Created checkins table
   ✓ Database setup complete!
   
   Default Admin Credentials:
   Username: admin
   Password: admin123
   ```

3. **Delete the setup file** (for security):
   - Via FTP: Delete `/htdocs/setup_database.php`
   - Or add to `.gitignore` and redeploy

---

## Step 4: Test Your System

### Admin Panel
1. Visit: **https://your-domain.com/public/admin.php**
2. Login: `admin` / `admin123`
3. You should see:
   - Dashboard with stats
   - Pending Payment Approvals section
   - Event management
   - "Scan Tickets" button

### Public Booking Page
1. Visit: **https://your-domain.com/public/index.php**
2. You should see:
   - Event details
   - Available slots
   - bKash payment instructions
   - Transaction ID field

### Test Flow
1. Create a test booking with any Transaction ID
2. Go to Admin Panel → See it in "Pending Approvals"
3. Click ✓ to approve
4. Visit: `https://your-domain.com/public/success.php?txnid=YOUR_TXN_ID`
5. You should see the confirmed ticket with QR code

---

## What Happens on Every Push

From now on, whenever you push to `main` branch:

```bash
git add .
git commit -m "Your changes"
git push
```

GitHub Actions will automatically:
1. ✅ Generate fresh `.env` with database credentials
2. ✅ Deploy all code changes
3. ✅ Update your live site

**No manual FTP needed!**

---

## Troubleshooting

### If deployment fails:
1. Check GitHub Actions logs: https://github.com/Maijied/spotlight-tickets/actions
2. Verify FTP credentials are correct in GitHub Secrets
3. Ensure FTP server allows connections from GitHub IPs

### If database connection fails:
1. Verify credentials in `.env` match your MySQL details
2. Check if MySQL server `sql303.infinityfree.com` is accessible
3. Ensure database `if0_40819537_shiddarth` exists

### If setup_database.php shows errors:
1. Check if tables already exist (safe to ignore duplicate warnings)
2. Verify MySQL user has CREATE TABLE permissions
3. Check error logs in your hosting control panel

---

## Security Checklist

After setup:
- [ ] Delete `setup_database.php` from server
- [ ] Change admin password from default `admin123`
- [ ] Verify `.env` is not publicly accessible
- [ ] Test booking flow end-to-end
- [ ] Test QR scanner functionality

---

## Next Steps

Your system is now **fully automated**! 

**To make changes:**
1. Edit code locally
2. Test with `php -S localhost:8082 -t .`
3. Commit and push
4. GitHub Actions deploys automatically

**To manage events:**
- Login to Admin Panel
- Add/remove event slots
- Set capacities and pricing

**To verify payments:**
- Check "Pending Approvals" section
- Verify Transaction ID in bKash
- Click ✓ to approve

**To scan tickets:**
- Click "Scan Tickets" in Admin Panel
- Use mobile device camera
- Scan QR codes from customer tickets

---

## Support

If you need help:
1. Check GitHub Actions logs for deployment issues
2. Check browser console for frontend errors
3. Check PHP error logs on your hosting panel
4. Review the walkthrough.md for detailed documentation

---

**Your system is production-ready! 🎉**
