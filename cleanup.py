#!/usr/bin/env python3
"""
LexiAid Code Cleanup Script
Removes debug code, optimizes files, and prepares for deployment
"""

import os
import re
import json
from pathlib import Path

def clean_javascript_files():
    """Remove console.log and debug statements from JavaScript files"""
    js_dir = Path("site/js")
    if not js_dir.exists():
        return
    
    patterns_to_remove = [
        r'console\.log\(.*?\);?\n?',
        r'console\.debug\(.*?\);?\n?',
        r'console\.error\(.*?\);?\n?',
        r'//\s*DEBUG:.*\n',
        r'//\s*TODO:.*\n',
        r'//\s*FIXME:.*\n'
    ]
    
    for js_file in js_dir.glob("*.js"):
        if js_file.name in ['core.min.js', 'html5shiv.min.js', 'pointer-events.min.js']:
            continue  # Skip minified files
            
        try:
            with open(js_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_size = len(content)
            
            for pattern in patterns_to_remove:
                content = re.sub(pattern, '', content, flags=re.MULTILINE)
            
            # Remove multiple empty lines
            content = re.sub(r'\n\s*\n\s*\n', '\n\n', content)
            
            if len(content) < original_size:
                with open(js_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"✓ Cleaned {js_file.name}: {original_size - len(content)} bytes removed")
                
        except Exception as e:
            print(f"✗ Error cleaning {js_file.name}: {e}")

def clean_php_files():
    """Remove debug statements and optimize PHP files"""
    site_dir = Path("site")
    if not site_dir.exists():
        return
    
    patterns_to_remove = [
        r'error_reporting\(E_ALL\);\n?',
        r'ini_set\([\'"]display_errors[\'"],\s*1\);\n?',
        r'//\s*DEBUG:.*\n',
        r'//\s*TODO:.*\n'
    ]
    
    for php_file in site_dir.rglob("*.php"):
        if 'vendor' in str(php_file) or 'phpmailer' in str(php_file):
            continue  # Skip vendor files
            
        try:
            with open(php_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_size = len(content)
            
            # Only remove debug statements for production
            # Keep error reporting for development
            for pattern in patterns_to_remove:
                if 'error_reporting' in pattern:
                    continue  # Keep error reporting for now
                content = re.sub(pattern, '', content, flags=re.MULTILINE)
            
            if len(content) < original_size:
                with open(php_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"✓ Cleaned {php_file.name}: {original_size - len(content)} bytes removed")
                
        except Exception as e:
            print(f"✗ Error cleaning {php_file.name}: {e}")

def optimize_python_files():
    """Clean up Python files and remove debug prints"""
    python_dir = Path("python")
    if not python_dir.exists():
        return
    
    for py_file in python_dir.glob("*.py"):
        if py_file.name.startswith('test_') or py_file.name.startswith('debug_'):
            continue  # Keep test files for development
            
        try:
            with open(py_file, 'r', encoding='utf-8') as f:
                lines = f.readlines()
            
            cleaned_lines = []
            for line in lines:
                # Remove debug prints but keep important logging
                if ('print(' in line and 'debug' in line.lower()) or line.strip().startswith('# DEBUG'):
                    continue
                cleaned_lines.append(line)
            
            if len(cleaned_lines) < len(lines):
                with open(py_file, 'w', encoding='utf-8') as f:
                    f.writelines(cleaned_lines)
                print(f"✓ Cleaned {py_file.name}: {len(lines) - len(cleaned_lines)} debug lines removed")
                
        except Exception as e:
            print(f"✗ Error cleaning {py_file.name}: {e}")

def create_deployment_checklist():
    """Create a deployment readiness checklist"""
    checklist = """# LexiAid Deployment Checklist

## Pre-Deployment Verification

### ✅ Code Quality
- [ ] All debug statements removed from production files
- [ ] Error handling implemented for all user-facing functions
- [ ] Input validation in place for all forms
- [ ] SQL injection protection verified
- [ ] XSS protection implemented

### ✅ Configuration
- [ ] Database credentials configured for production
- [ ] Python virtual environment properly set up
- [ ] All required Python packages installed
- [ ] File permissions set correctly
- [ ] Log directories created and writable

### ✅ Testing
- [ ] All test dashboard checks pass
- [ ] Search functionality working (semantic + fallback)
- [ ] Database operations confirmed
- [ ] Task management tested
- [ ] Case upload and processing tested
- [ ] Mobile responsiveness verified

### ✅ Performance
- [ ] Python models pre-downloaded if possible
- [ ] Caching mechanisms in place
- [ ] Database queries optimized
- [ ] Static assets minified
- [ ] Server performance tested

### ✅ Security
- [ ] Database access properly secured
- [ ] File upload restrictions in place
- [ ] Session security configured
- [ ] HTTPS ready (if applicable)
- [ ] Sensitive data properly protected

### ✅ Documentation
- [ ] Setup guide updated and tested
- [ ] API documentation complete
- [ ] User manual available
- [ ] Troubleshooting guide prepared

## Deployment Steps

1. **Backup current setup** (if updating existing installation)
2. **Upload files** to production server
3. **Configure database** and import schema
4. **Set up Python environment** and install dependencies
5. **Configure web server** (Apache/Nginx)
6. **Test all functionality** using test dashboard
7. **Monitor logs** for any issues
8. **Verify performance** under expected load

## Post-Deployment Monitoring

- [ ] Check search functionality daily
- [ ] Monitor Python script execution
- [ ] Review error logs regularly
- [ ] Verify database performance
- [ ] Check user feedback and usage patterns

## Rollback Plan

1. Keep backup of previous version
2. Document configuration changes
3. Have database rollback scripts ready
4. Test rollback procedure in staging environment
"""
    
    with open("DEPLOYMENT_CHECKLIST.md", "w", encoding="utf-8") as f:
        f.write(checklist)
    print("✓ Created deployment checklist")

def main():
    """Main cleanup function"""
    print("🧹 Starting LexiAid code cleanup...")
    
    print("\n📜 Cleaning JavaScript files...")
    clean_javascript_files()
    
    print("\n🔧 Cleaning PHP files...")
    clean_php_files()
    
    print("\n🐍 Optimizing Python files...")
    optimize_python_files()
    
    print("\n📋 Creating deployment checklist...")
    create_deployment_checklist()
    
    print("\n✨ Cleanup complete! LexiAid is ready for deployment.")
    print("\nNext steps:")
    print("1. Review DEPLOYMENT_CHECKLIST.md")
    print("2. Test using test-dashboard.html")
    print("3. Verify all functionality works as expected")

if __name__ == "__main__":
    main()
