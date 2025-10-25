# Step-by-Step Guide to Put Your Site on GitHub..

## 🎯 Overview

This comprehensive guide will walk you through the entire process of putting your NT2 Taalles International website on GitHub, from creating an account to uploading your code.

## 📋 Prerequisites

Before you begin, you'll need:
- A computer with internet access
- Your website files ready
- Basic computer skills
- About 30-60 minutes of time

## 🚀 Step 1: Create a GitHub Account

### 1.1 Go to GitHub
1. Open your web browser
2. Go to [github.com](https://github.com)
3. Click the **"Sign up"** button

### 1.2 Create Your Account
1. **Username**: Choose a unique username (e.g., `majahe`)
2. **Email**: Use a valid email address
3. **Password**: Create a strong password
4. **Verification**: Complete the verification puzzle
5. Click **"Create account"**

### 1.3 Verify Your Email
1. Check your email inbox
2. Click the verification link from GitHub
3. Complete the email verification process

## 📁 Step 2: Create a New Repository

### 2.1 Start Creating Repository
1. Log into GitHub
2. Click the **"+"** icon in the top right corner
3. Select **"New repository"**

### 2.2 Repository Settings
1. **Repository name**: `NT2TaallesInternational` (or your preferred name)
2. **Description**: "NT2 Dutch language course registration website"
3. **Visibility**: 
   - ✅ **Public** (recommended for portfolio projects)
   - ❌ Private (if you want to keep it private)
4. **Initialize repository**: 
   - ❌ Don't check "Add a README file"
   - ❌ Don't check "Add .gitignore"
   - ❌ Don't check "Choose a license"
5. Click **"Create repository"**

## 💻 Step 3: Install Git (if not already installed)

### 3.1 Download Git
1. Go to [git-scm.com](https://git-scm.com/downloads)
2. Download Git for Windows
3. Run the installer
4. Use default settings during installation

### 3.2 Verify Installation
1. Open Command Prompt or PowerShell
2. Type: `git --version`
3. You should see a version number (e.g., "git version 2.40.1")

## 📂 Step 4: Prepare Your Project Folder

### 4.1 Navigate to Your Project
1. Open Command Prompt or PowerShell
2. Navigate to your project folder:
```bash
cd "C:\Users\majah\OneDrive\Bureaublad\NT2 site\NT2TaallesInternational"
```

### 4.2 Create .gitignore File
Create a `.gitignore` file to exclude sensitive files:

```gitignore
# Environment variables (contains sensitive data)
config/.env

# Database files
*.sql
*.db

# Logs
*.log
logs/

# Temporary files
*.tmp
*.temp

# OS generated files
.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db
Thumbs.db

# IDE files
.vscode/
.idea/
*.swp
*.swo

# Backup files
*.bak
*.backup
```

## 🔧 Step 5: Initialize Git Repository

### 5.1 Initialize Git
```bash
git init
```

### 5.2 Configure Git (First Time Only)
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### 5.3 Add Files to Git
```bash
git add .
```

### 5.4 Create First Commit
```bash
git commit -m "Initial commit: NT2 Taalles International website"
```

## 🔗 Step 6: Connect to GitHub Repository

### 6.1 Add Remote Origin
Replace `YOUR_USERNAME` with your actual GitHub username:
```bash
git remote add origin https://github.com/YOUR_USERNAME/NT2TaallesInternational.git
```

### 6.2 Verify Remote Connection
```bash
git remote -v
```

You should see:
```
origin  https://github.com/YOUR_USERNAME/NT2TaallesInternational.git (fetch)
origin  https://github.com/YOUR_USERNAME/NT2TaallesInternational.git (push)
```

## 🚀 Step 7: Push to GitHub

### 7.1 Set Main Branch
```bash
git branch -M main
```

### 7.2 Push to GitHub
```bash
git push -u origin main
```

### 7.3 Authentication
When prompted:
- **Username**: Your GitHub username
- **Password**: Use a Personal Access Token (not your GitHub password)

## 🔐 Step 8: Set Up Authentication (Important!)

### 8.1 Create Personal Access Token
1. Go to GitHub → Settings → Developer settings → Personal access tokens
2. Click **"Generate new token (classic)"**
3. **Note**: "Git operations"
4. **Expiration**: Choose appropriate duration
5. **Scopes**: Check **"repo"** (full control of private repositories)
6. Click **"Generate token"**
7. **Copy the token** (you won't see it again!)

### 8.2 Use Token for Authentication
When Git asks for password, use the Personal Access Token instead of your GitHub password.

## ✅ Step 9: Verify Your Upload

### 9.1 Check GitHub Repository
1. Go to your repository: `https://github.com/YOUR_USERNAME/NT2TaallesInternational`
2. You should see all your files
3. The README.md will display automatically

### 9.2 Verify Files
Check that these files are present:
- ✅ `index.php`
- ✅ `README.md`
- ✅ `Instructions.md`
- ✅ `Git-Setup-Fix.md`
- ✅ `GitHub-Update-Guide.md`
- ✅ All your website files

## 🔄 Step 10: Future Updates

### 10.1 Making Changes
When you make changes to your website:

```bash
# 1. Check what changed
git status

# 2. Add changes
git add .

# 3. Commit changes
git commit -m "Description of your changes"

# 4. Push to GitHub
git push origin main
```

### 10.2 Quick Update Workflow
```bash
git add .
git commit -m "Your update message"
git push origin main
```

## 🛠️ Troubleshooting Common Issues

### Issue 1: "Repository not found"
**Solution:**
- Check your repository name is correct
- Verify you have access to the repository
- Ensure the repository exists on GitHub

### Issue 2: "Authentication failed"
**Solution:**
- Use Personal Access Token instead of password
- Check your token has the right permissions
- Verify your GitHub account is active

### Issue 3: "Remote origin already exists"
**Solution:**
```bash
git remote remove origin
git remote add origin https://github.com/YOUR_USERNAME/NT2TaallesInternational.git
```

### Issue 4: "URL rejected: Port number"
**Solution:**
- Use HTTPS URL format: `https://github.com/username/repo.git`
- Don't use: `http://github.com:username/repo.git`

## 📚 Alternative Methods

### Method 1: GitHub Desktop (GUI)
1. Download [GitHub Desktop](https://desktop.github.com/)
2. Sign in with your GitHub account
3. Clone your repository
4. Copy your files to the cloned folder
5. Commit and push through the GUI

### Method 2: Web Interface
1. Go to your GitHub repository
2. Click **"uploading an existing file"**
3. Drag and drop your files
4. Add commit message
5. Click **"Commit changes"**

### Method 3: VS Code Integration
1. Install VS Code
2. Install Git extension
3. Open your project folder
4. Use built-in Git features

## 🎯 Success Checklist

After completing all steps, you should have:
- ✅ GitHub account created
- ✅ Repository created on GitHub
- ✅ Git installed and configured
- ✅ Project files uploaded to GitHub
- ✅ Authentication set up
- ✅ Ability to make future updates

## 📞 Getting Help

### If You Get Stuck:
1. **Check error messages** carefully
2. **Verify your repository URL** is correct
3. **Ensure authentication** is set up properly
4. **Try the alternative methods** if command line doesn't work

### Resources:
- [Git Documentation](https://git-scm.com/doc)
- [GitHub Help](https://docs.github.com)
- [Git Tutorial](https://www.atlassian.com/git/tutorials)

## 🎉 Congratulations!

You've successfully put your NT2 Taalles International website on GitHub! 

Your website is now:
- ✅ Version controlled
- ✅ Backed up online
- ✅ Shareable with others
- ✅ Ready for collaboration
- ✅ Available for deployment

## 🚀 Next Steps

Now that your code is on GitHub, you can:
1. **Share your repository** with others
2. **Deploy to hosting services** (Heroku, Netlify, etc.)
3. **Collaborate with other developers**
4. **Track changes and versions**
5. **Create issues and project management**

---

**Need more help?** Check the other guides in your repository:
- `Instructions.md` - Website setup guide
- `Git-Setup-Fix.md` - Git troubleshooting
- `GitHub-Update-Guide.md` - Updating your repository
