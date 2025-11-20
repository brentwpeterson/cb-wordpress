# WordPress Plugin Development - Standard Operating Procedure (SOP)

**Document Version:** 1.0
**Created:** 2025-11-20
**Project:** RequestDesk Connector WordPress Plugin
**Purpose:** Prevent production issues through systematic development and testing

---

## 🎯 **SOP OBJECTIVE**

Establish a standardized, repeatable process for WordPress plugin development that eliminates:
- Plugin duplication issues
- Production deployment failures
- Activation errors
- Auto-update system problems
- Repeated mistakes due to inconsistent processes

---

## 📋 **PHASE 1: DEVELOPMENT PREPARATION**

### **Step 1.1: Environment Setup**
- [ ] Confirm local WordPress development environment is running
- [ ] Verify local environment matches production WordPress version
- [ ] Backup existing plugin installation (if any)
- [ ] Document current plugin version before starting

### **Step 1.2: Code Changes**
- [ ] Make code changes in development directory: `/cb-wordpress/`
- [ ] Update version number in TWO places only:
  - Plugin header: `* Version: X.Y.Z`
  - PHP constant: `define('REQUESTDESK_VERSION', 'X.Y.Z');`
- [ ] **NEVER** change directory names or main file names
- [ ] Test code syntax: `php -l requestdesk-connector.php`

### **Step 1.3: Version Increment Rules**
- **Patch (X.Y.Z):** Bug fixes, minor improvements
- **Minor (X.Y.0):** New features, significant changes
- **Major (X.0.0):** Breaking changes, major rewrites

---

## 📦 **PHASE 2: PACKAGE CREATION**

### **Step 2.1: SPECIFIC Build Location (NEVER CHANGE THIS)**
```bash
cd /Users/brent/scripts/CB-Workspace/cb-wordpress
mkdir -p plugin-releases
rm -rf plugin-releases/requestdesk-connector
```

### **Step 2.2: Copy Plugin Files**
```bash
cp -r . plugin-releases/requestdesk-connector
```

### **Step 2.3: Clean Unwanted Files**
```bash
rm -rf plugin-releases/requestdesk-connector/.claude
rm -rf plugin-releases/requestdesk-connector/todo
rm -rf plugin-releases/requestdesk-connector/logs
rm -rf plugin-releases/requestdesk-connector/plugin-releases
rm -rf plugin-releases/requestdesk-connector/.git
rm -f plugin-releases/requestdesk-connector/debug.log
rm -f plugin-releases/requestdesk-connector/.DS_Store
```

### **Step 2.4: Create Versioned ZIP**
```bash
cd plugin-releases
zip -r requestdesk-connector-v[VERSION].zip requestdesk-connector/ -x "*.DS_Store"
```

### **Step 2.5: FINAL LOCATION & VERSION HISTORY**
**Plugin ZIP file location:** `/Users/brent/scripts/CB-Workspace/cb-wordpress/plugin-releases/requestdesk-connector-v[VERSION].zip`

**Version History Maintained:**
- `requestdesk-connector-v2.3.9.zip`
- `requestdesk-connector-v2.3.10.zip`
- `requestdesk-connector-v2.3.11.zip`
- etc.

**Benefits:**
- ✅ Easy rollback to previous versions
- ✅ Compare different versions
- ✅ Reference working versions
- ✅ Track version progression

**THIS IS THE ONLY LOCATION - NO EXCEPTIONS**

### **Step 2.6: Verify Package Structure**
- [ ] ZIP file named: `requestdesk-connector-v[VERSION].zip`
- [ ] Contains directory: `requestdesk-connector/`
- [ ] Main file exists: `requestdesk-connector/requestdesk-connector.php`
- [ ] Version in plugin header matches ZIP filename
- [ ] Previous versions preserved in same directory

### **Step 2.7: Version Archive Management**
- [ ] Keep last 5 versions for rollback capability
- [ ] Archive older versions to `plugin-releases/archive/` if needed
- [ ] Document version notes in `plugin-releases/VERSION-NOTES.md`

---

## 🧪 **PHASE 3: LOCAL TESTING (MANDATORY)**

### **Step 3.1: Upload to Local WordPress**
- [ ] Access local WordPress admin: Plugins → Add New → Upload Plugin
- [ ] Upload `requestdesk-connector.zip`
- [ ] Verify installation creates `requestdesk-connector/` directory only

### **Step 3.2: Activation Testing**
- [ ] Activate plugin without errors
- [ ] Check for any "unexpected output" errors
- [ ] Verify no duplicate plugins appear
- [ ] Check error logs for any warnings

### **Step 3.3: Functionality Testing**
- [ ] Test core plugin features work
- [ ] Verify settings pages accessible
- [ ] Test API connectivity if applicable
- [ ] Check database tables created properly

### **Step 3.4: Update System Testing**
- [ ] If auto-update system exists, verify "Enable auto-updates" toggle appears
- [ ] Test deactivation works cleanly
- [ ] Test reactivation works cleanly
- [ ] Verify plugin overwrites previous version (no duplicates)

### **Step 3.5: Local Testing Success Criteria**
- [ ] ✅ Clean activation with no errors
- [ ] ✅ All functionality works as expected
- [ ] ✅ No duplicate plugin installations
- [ ] ✅ Auto-update system functional (if applicable)
- [ ] ✅ Error logs clean

**🚨 CRITICAL: If ANY local test fails, DO NOT proceed to production. Fix issues and restart testing.**

---

## 🚀 **PHASE 4: PRODUCTION DEPLOYMENT**

### **Step 4.1: Pre-Deployment Checklist**
- [ ] All local tests passed
- [ ] Production backup completed
- [ ] Deployment window scheduled
- [ ] Rollback plan documented

### **Step 4.2: Production Upload**
- [ ] Access production WordPress admin: Plugins → Add New → Upload Plugin
- [ ] Upload same `requestdesk-connector.zip` file used in local testing
- [ ] **DO NOT** upload a different version than locally tested

### **Step 4.3: Production Activation**
- [ ] Activate plugin and monitor for errors
- [ ] Verify functionality matches local testing results
- [ ] Check production error logs
- [ ] Test critical plugin features

### **Step 4.4: Post-Deployment Verification**
- [ ] Plugin version shows correct number
- [ ] No duplicate installations
- [ ] Auto-update system working (if applicable)
- [ ] All expected features functional

---

## 📊 **PHASE 5: DOCUMENTATION & CLEANUP**

### **Step 5.1: Success Documentation**
- [ ] Record successful deployment in project logs
- [ ] Document any issues encountered and solutions
- [ ] Update version tracking records
- [ ] Store successful package for future reference

### **Step 5.2: Knowledge Retention**
- [ ] Add deployment notes to MCP memory system
- [ ] Update any relevant documentation
- [ ] Share learnings with team if applicable

### **Step 5.3: Cleanup**
- [ ] Remove temporary files from `/tmp/`
- [ ] Archive old plugin versions if needed
- [ ] Clean development directory

---

## 🚨 **EMERGENCY PROCEDURES**

### **Production Failure Response**
1. **Immediate:** Deactivate failing plugin
2. **Rollback:** Activate previous working version
3. **Investigate:** Check error logs, identify root cause
4. **Fix:** Apply fixes in development, restart full SOP process
5. **Document:** Record incident and resolution

### **Common Issues & Solutions**
- **Duplicate Plugins:** Delete all instances, follow SOP exactly
- **Activation Errors:** Check error logs, verify local testing was complete
- **Auto-Update Issues:** Verify server configuration, API accessibility

---

## ✅ **SOP COMPLIANCE CHECKLIST**

**Before ANY production deployment:**
- [ ] Read entire SOP
- [ ] Follow all phases in order
- [ ] Complete ALL checklist items
- [ ] Local testing 100% successful
- [ ] Emergency procedures understood

**Success Metrics:**
- Zero production activation failures
- Zero duplicate plugin issues
- Zero emergency rollbacks needed
- Consistent, repeatable deployments

---

## 📝 **REVISION HISTORY**

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | 2025-11-20 | Initial SOP creation | Claude Code |

---

**🔥 REMEMBER: This SOP exists because of repeated production failures. Following it religiously is the ONLY way to prevent those failures from recurring.**