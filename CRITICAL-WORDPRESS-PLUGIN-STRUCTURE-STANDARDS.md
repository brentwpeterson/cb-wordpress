# CRITICAL: WordPress Plugin Structure Standards

## 🚨 **PERMANENT DOCUMENTATION - DO NOT IGNORE**

**Date Created:** 2025-11-20
**Reason:** Repeated failures in WordPress plugin development due to inconsistent directory naming
**User Impact:** "this issue has been constant... you NEVER document when you get it right"

---

## ❌ **THE RECURRING PROBLEM**

**What Keeps Happening:**
- Creating ZIP files with version numbers: `requestdesk-connector-v2.3.6-AUTO-UPDATE-FIXED.zip`
- WordPress creates directories based on ZIP names: `requestdesk-connector-v2.3.6-AUTO-UPDATE-FIXED/`
- Results in MULTIPLE plugin instances instead of updates
- Causes: Database conflicts, function redefinition errors, settings corruption, broken auto-updates

**Real Impact:**
- Production site breakage
- User frustration with "serious problems"
- Loss of trust due to repeated mistakes
- Time wasted fixing the same issue multiple times

---

## ✅ **THE CORRECT APPROACH (NEVER DEVIATE)**

### **WordPress Plugin Directory Structure Rules:**

```
CORRECT:
└── requestdesk-connector/                    ← NEVER CHANGES
    ├── requestdesk-connector.php            ← NEVER CHANGES
    ├── includes/
    ├── admin/
    └── readme.txt

WRONG:
└── requestdesk-connector-v2.3.6-FIXED/     ← CREATES DUPLICATES
└── requestdesk-connector-v2.3.9/           ← CREATES DUPLICATES
```

### **ZIP File Naming Rules:**

```
CORRECT:   requestdesk-connector.zip          ← Always the same name
WRONG:     requestdesk-connector-v2.3.6.zip   ← Creates wrong directory
WRONG:     plugin-name-FIXED.zip              ← Creates wrong directory
```

### **Version Management Rules:**

```php
// ONLY change these for versions:
// 1. Plugin header
* Version: 2.3.10

// 2. PHP constant
define('REQUESTDESK_VERSION', '2.3.10');

// NEVER change:
// - Directory name
// - Main file name
// - ZIP file name
```

---

## 🔧 **IMPLEMENTATION CHECKLIST**

**Before Creating Any Plugin Package:**

- [ ] Directory name is exactly: `requestdesk-connector/`
- [ ] Main file is exactly: `requestdesk-connector.php`
- [ ] ZIP file name is exactly: `requestdesk-connector.zip`
- [ ] Only version numbers changed in headers/constants

**MANDATORY LOCAL TESTING:**
- [ ] **ALWAYS upload to LOCAL WordPress first**
- [ ] Test activation works without errors
- [ ] Test plugin functionality works
- [ ] Test deactivation/reactivation works
- [ ] Test auto-update system if applicable
- [ ] Verified overwrites existing installation (doesn't create duplicate)
- [ ] **ONLY AFTER local testing passes, proceed to production**

---

## 📊 **WHY THIS DOCUMENTATION EXISTS**

**Pattern Recognition:**
- This exact mistake has been repeated multiple times
- Each time, a different "solution" is attempted
- No learning occurs between sessions due to lack of documentation
- User correctly identified: "you NEVER document when you get it right"

**Critical Insight:**
The problem isn't the technical issue - it's the failure to document successful approaches for future reference.

---

## 🚨 **MANDATORY READING FOR ALL PLUGIN WORK**

**Before touching any WordPress plugin development:**

1. **READ THIS DOCUMENT FIRST**
2. **Follow the exact structure outlined**
3. **Test locally before production**
4. **Document any new learnings**

**If you find yourself creating version-named ZIP files or directories - STOP and re-read this document.**

---

## 📝 **SUCCESS CRITERIA**

**You know you're doing it right when:**
- WordPress shows "Plugin updated successfully" instead of "Plugin activated"
- Only ONE instance of the plugin exists in WordPress admin
- Directory structure remains consistent across all versions
- Updates overwrite existing files, don't create new directories

**You know you're doing it WRONG when:**
- Multiple instances of same plugin appear in WordPress
- WordPress creates new directories for each "update"
- User reports "serious problems" with plugin installations

---

## 🎯 **FINAL WARNING**

**This document exists because of a pattern of repeated failures.**

Every time plugin development happens differently, it causes:
- Production issues
- User frustration
- Lost development time
- Broken trust

**THERE IS NO EXCUSE FOR REPEATING THIS MISTAKE AGAIN.**

Follow this documentation religiously, or risk serious production consequences.

---

*This documentation created in response to user feedback: "this issue has been constant and sometimes you get it right sometimes not the problem is you NEVER document when you get it right so each time you do it a different way and you never learn"*