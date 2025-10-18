Claude Session Close - Standardized Handoff System

**USAGE:**
- `/claude-close <project>` - Standard session close with CLAUDE.md restart context update
- `/claude-close <project> CLEAN` - Close session but leave CLAUDE.md unchanged for fresh feature start
- `/claude-close <project> --debug` - Debug session close with attempt tracking and debug log updates

**Arguments**:
- `<project>` (required): Project to close session for - one of: `cb-requestdesk`, `cb-shopify`, `cb-wordpress`, `cb-magento`, `cb-junogo`

**🏗️ PROJECT-SPECIFIC CONFIGURATIONS:**

**cb-requestdesk** (Full Feature Set):
- Progress logs: `[project]/todo/current/`
- Technical docs: `[project]/documentation/docs/`
- Security scan: Python files (1000 line limit)
- File organization: Full validation for .md and .sh files
- Debug logs: Available with TEST ATTEMPT LOG format

**cb-shopify** (Gadget/JS Project):
- Progress logs: Root level (no todo/ structure)
- Technical docs: API guides and documentation in root
- Security scan: JavaScript files (800 line limit)
- File organization: Focus on API files and documentation
- Debug logs: Gadget deployment logs

**cb-wordpress** (PHP Plugin):
- Progress logs: `[project]/todo/` if exists, else root
- Technical docs: Installation guides and PHP docs
- Security scan: PHP files (600 line limit, WordPress standards)
- File organization: WordPress plugin structure validation
- Debug logs: PHP error logs and plugin logs

**cb-junogo** (Node.js/TypeScript):
- Progress logs: `[project]/todo/` and `[project]/docs/`
- Technical docs: `[project]/docs/` directory
- Security scan: TypeScript/JavaScript files (1000 line limit)
- File organization: Node.js project structure
- Debug logs: Application logs and Docker logs

**cb-magento** (Magento Extension):
- Progress logs: `[project]/todo/` if exists
- Technical docs: Magento-specific documentation
- Security scan: SKIP (use claude-save instead)
- File organization: Basic Magento module structure
- Debug logs: Magento system logs

**🚨 CRITICAL ANTI-COMPACTING RULE:**
✅ **SAVE CONTEXT FIRST** - Write ALL documentation and context BEFORE any scans
✅ **PREVENT CONTEXT LOSS** - Auto-compacting often triggers during validation scans
✅ **PRESERVATION ORDER** - Context → Documentation → Validation → Commit

**🔄 SAME PATTERN EVERY TIME:**
✅ **UPDATE EXISTING PROGRESS LOGS** - Find and append to current progress files
✅ **UPDATE EXISTING DOCUMENTATION** - Enhance current docs, don't create new ones
✅ **VERBOSE COMMIT** - Comprehensive commit message with session summary
✅ **UPDATE CLAUDE.MD HEADER** - Write restart context at the very top

**📋 STANDARDIZED CLOSE WORKFLOW:**

**Phase 1: CRITICAL - Save All Context & Documentation FIRST**
1. **IMMEDIATE Context Preservation (BEFORE ANY SCANS):**
   - Get current branch name: `git branch --show-current`
   - Get current working directory: `pwd`
   - **IMMEDIATELY save branch context** to prevent loss during compacting
   - Create/update context file: `.claude/branch-context/[type]-[branch-name]-context.md`
   - Include comprehensive session summary with timestamp

2. **Debug Log Validation & Normalization:**
   - Get current branch name: `git branch --show-current`
   - Expected debug log name: `[branch-name]-debug.log`
   - Check current working directory for debug logs:
     - Look for `*debug*.log`, `*test*.log`, `*progress*.log` files
     - Look for files matching TEST ATTEMPT LOG format
   - **If wrong name found:**
     - Ask user: "Found debug log '[filename]' but expected '[branch-name]-debug.log'. Rename it? [Y/n]"
     - If yes, rename preserving content: `mv [old-name] [branch-name]-debug.log`
   - **If no debug log found:**
     - Create blank debug log: `[branch-name]-debug.log` 
     - Initialize with TEST ATTEMPT LOG template:
       ```
       ##############################################################################
       # TEST ATTEMPT LOG
       ##############################################################################
       # 1. YYYY-MM-DD HH:MM - [Description of session close]
       # [Add additional entries as testing progresses]
       ##############################################################################
       ```

3. **Project-Specific Progress File Updates:**

   **For cb-requestdesk:**
   - Search `[project]/todo/current/` for active progress logs (e.g., MVP-manyrequest-progress-log.md)
   - Update current version in `CHANGELOG.md`
   - Enhance existing technical docs in `documentation/docs/`

   **For cb-shopify:**
   - Look for progress logs in root level or any existing logs
   - Update API documentation (EXTERNAL_API_GUIDE.md, etc.)
   - Check for any deployment or feature logs

   **For cb-wordpress:**
   - Search `[project]/todo/` for any progress logs
   - Update INSTALLATION-GUIDE.md if installation process changed
   - Document plugin changes and version updates

   **For cb-junogo:**
   - Search `[project]/todo/` and `[project]/docs/` for progress logs
   - Update development documentation in `docs/`
   - Update any deployment or feature development notes

   **For cb-magento:**
   - Search `[project]/todo/` if exists
   - Basic documentation updates only
   - No extensive progress tracking required

   **Universal Steps:**
   - **IMMEDIATELY append session summary** to existing progress logs (don't create new files)
   - Enhance existing technical docs with any new implementation details
   - Look for existing technical documentation that needs updates

4. **Enhanced Branch Context Documentation:**
   - Update the context file: `.claude/branch-context/[type]-[branch-name]-context.md`
   - Include comprehensive session summary:
     ```markdown
     # Branch Context: [full-branch-name]

     **Saved:** [timestamp] UTC via /claude-close
     **Last Commit:** [hash] - [commit message]
     **Working Directory:** [current-working-directory]

     ## Work Completed This Session
     [Summary of what was accomplished]

     ## Work In Progress
     [Any partial implementations]

     ## Current Todos
     [Capture any active TodoWrite items with their status]
     - ✅ Completed: [completed tasks]
     - 🔄 In Progress: [current active task]
     - ⏳ Pending: [queued tasks]

     ## Files Modified
     [List of key files changed]

     ## Next Steps
     [What needs to be done next session]

     ## Documentation & Work Directory
     **TODO Directory**: `[project]/todo/current/[category]/[task-name]/` - [View README](file:/Users/brent/scripts/CBTextApp/todo/current/[category]/[task-name]/README.md)

     ## Recovery Instructions
     To resume work on this branch:
     ```bash
     git checkout [branch-name]
     /claude-start
     ```

     ## Status
     [ACTIVE/COMPLETE/BLOCKED] - [Brief status description]
     ```

5. **Enhance Current Documentation:**
   - Find relevant technical docs in `/documentation/docs/` that relate to current work
   - Update existing architecture docs if changes were made
   - Add to existing API documentation if endpoints were modified
   - **RULE: Enhance existing, don't create new unless absolutely necessary**

**Phase 2: Project-Specific File Validation (AFTER Context is Saved)**
6. **CRITICAL: Project-Specific File Size Validation**

   **For cb-requestdesk (Python/Full Stack):**
   - Check all Python files for size violations
   - **BLOCKING**: Files over 1000 lines
   - **WARNING**: Files 500-1000 lines
   - Extension pattern: `*.py`

   **For cb-shopify (JavaScript/Gadget):**
   - Check JavaScript/TypeScript files
   - **BLOCKING**: Files over 800 lines (Gadget platform limits)
   - **WARNING**: Files 400-800 lines
   - Extension pattern: `*.js`, `*.ts`, `*.tsx`
   - Exclude: `.gadget/` generated files

   **For cb-wordpress (PHP Plugin):**
   - Check PHP files for WordPress standards
   - **BLOCKING**: Files over 600 lines (WordPress coding standards)
   - **WARNING**: Files 300-600 lines
   - Extension pattern: `*.php`
   - Exclude: Plugin framework files

   **For cb-junogo (Node.js/TypeScript):**
   - Check TypeScript/JavaScript files
   - **BLOCKING**: Files over 1000 lines
   - **WARNING**: Files 500-1000 lines
   - Extension pattern: `*.ts`, `*.tsx`, `*.js`, `*.jsx`
   - Exclude: `node_modules/`, `dist/`, `.next/`

   **For cb-magento:**
   - **SKIP FILE SIZE VALIDATION** - Use `/claude-save` instead

   **Validation Output Format:**
     ```
     PROJECT FILE SIZE CHECK ([project])...
     OPTIMAL: component.ts (324 lines) - Optimal for Claude
     WARNING: service.ts (847 lines) - Monitor for growth
     CRITICAL: handler.php (1,247 lines) - EXCEEDS [limit] LINE LIMIT
     ```

   **If violations found:**
     ```
     FILE SIZE VIOLATIONS - COMMIT BLOCKED

     OVERSIZED FILES DETECTED:
     1. File: [file] ([line count] lines)
        Issue: Exceeds [project-limit]-line limit for [project-type]
        Impact: Degraded Claude refactoring capability

     REQUIRED ACTIONS:
     1. Refactor large files into smaller modules
     2. Break file into logical components
     3. Target: Files under [warning-limit] lines for optimal performance
     4. Run /claude-close [project] again after refactoring
     ```

**Phase 3: Project-Specific File Organization Validation**
7. **CRITICAL: Check for Documentation and Files in Wrong Locations**

   **For cb-requestdesk (Full Validation):**
   ```bash
   # Check for scattered .md files (excluding approved locations)
   find . -name "*.md" -not -path "./todo/current/*" -not -path "./documentation/*" -not -path "./README.md" -not -path "./CHANGELOG.md" -not -path "./CLAUDE.md" -not -path "./AGENTS.md" -not -path "./.claude/*"

   # Check for scattered .sh test scripts (excluding approved locations)
   find . -name "*.sh" -not -path "./backend/tests/curl_scripts/*" -not -path "./docker/*" -not -path "./my-utils/*"
   ```

   **For cb-shopify (Gadget Project Structure):**
   ```bash
   # Check for unauthorized .md files (allow root-level docs)
   find . -name "*.md" -not -path "./README.md" -not -path "./CLAUDE.md" -not -path "./EXTERNAL_API_GUIDE.md" -not -path "./BLOG_SELECTION_DOCUMENTATION.md" -not -path "./.claude/*"

   # Check for non-Gadget files in wrong locations
   find . -name "*.js" -path "./.gadget/*" -prune -o -name "*.js" -not -path "./node_modules/*" -print
   ```

   **For cb-wordpress (Plugin Structure):**
   ```bash
   # Check for plugin files outside plugin directory
   find . -name "*.php" -not -path "./requestdesk-connector/*" -not -path "./.claude/*"

   # Check for documentation in wrong locations
   find . -name "*.md" -not -path "./README.md" -not -path "./CLAUDE.md" -not -path "./INSTALLATION-GUIDE.md" -not -path "./todo/*" -not -path "./.claude/*"
   ```

   **For cb-junogo (Node.js Structure):**
   ```bash
   # Check for documentation outside docs/ or root
   find . -name "*.md" -not -path "./docs/*" -not -path "./README.md" -not -path "./CLAUDE.md" -not -path "./todo/*" -not -path "./.claude/*" -not -path "./node_modules/*"

   # Check for TypeScript files in wrong locations
   find . -name "*.ts" -not -path "./src/*" -not -path "./node_modules/*" -not -path "./dist/*" -print
   ```

   **For cb-magento (Minimal Check):**
   ```bash
   # Basic structure validation only
   find . -name "*.md" -not -path "./README.md" -not -path "./CLAUDE.md" -not -path "./.claude/*"
   ```

   **If unauthorized files found:**
   - **STOP COMMIT PROCESS** (except cb-magento - warn only)
   - **List all files found**: Show user what files are in wrong locations
   - **Project-specific guidance**:

     **cb-requestdesk**: "Files should be in: `todo/current/`, `documentation/docs/`, or `backend/tests/curl_scripts/`"

     **cb-shopify**: "Documentation should be in root level, code in proper Gadget structure"

     **cb-wordpress**: "Plugin files in `requestdesk-connector/`, docs in root or `todo/`"

     **cb-junogo**: "Documentation in `docs/`, TypeScript in `src/`, tasks in `todo/`"

     **cb-magento**: "⚠️  WARNING: Files may need reorganization (not blocking)"

   - **Require organization**: "Move files to proper locations before commit? [Y/n]" (except cb-magento)
   - **If Yes**: Help move files to correct locations
   - **If No**: "❌ COMMIT BLOCKED - Files must be properly organized" (cb-requestdesk/cb-shopify/cb-wordpress/cb-junogo only)

8. **Create Verbose Commit Statement:**
   - **ONLY AFTER file organization check passes**
   - Analyze all changed files in current session
   - Document what was accomplished and why
   - **Include security scan results and improvements made**
   - Include context for future developers/Claude sessions
   - Reference any ongoing work or next steps needed
   - Use structured format with clear sections including security status

9. **Commit All Work:**
   - Stage all changes: `git add .`
   - Create comprehensive commit with detailed message including security context
   - Ensure all session work is preserved in git history

**Phase 4: Update [project]/CLAUDE.md Header (Standard Close Only)**
10. **Write Restart Context at Top of [project]/CLAUDE.md:**
   - **STANDARD CLOSE**: Insert session handoff info at very beginning of [project]/CLAUDE.md
   - **CLEAN CLOSE**: Skip this step - leave [project]/CLAUDE.md unchanged for fresh start
   - **CRITICAL: Include current working directory**: `pwd` output
   - **CRITICAL: Include branch name**: `git branch --show-current` output
   - **CRITICAL: Document debug log location**: `[branch-name]-debug.log (in working directory)`
   - Include specific next steps for `/claude-start`
   - Document current status of any ongoing processes
   - Keep format consistent and easy to parse

**Phase 5: Final Commit and Exit Verification**
11. **Commit CLAUDE.md Updates:**
   - Stage CLAUDE.md: `git add CLAUDE.md`
   - Create final commit with restart context updates
   - **VERIFY CLEAN WORKING TREE**: `git status` should show "nothing to commit, working tree clean"

12. **Confirm Safe Exit:**
   - **MANDATORY**: Say "OK TO EXIT - All work preserved and context committed"
   - **NEVER exit** without confirming clean git status
   - **ENSURE** all session work is in git history for recovery

**🎯 WHAT GETS UPDATED (NOT CREATED):**
- **Existing Progress Logs**: Add session entry to current active logs
- **Current CHANGELOG.md**: Update current version section
- **Existing Technical Docs**: Enhance with new implementation details
- **Branch Context File**: `.claude/branch-context/[type]-[branch]-context.md` (created/updated)
- **CLAUDE.md Header**: Replace previous session context with current (Standard Close only)

**🧹 CLEAN CLOSE BEHAVIOR:**
- Skips CLAUDE.md restart context update
- Leaves CLAUDE.md in clean state for new feature work
- Still updates all progress logs, changelog, and documentation
- Still creates comprehensive commit message
- Use when switching to completely different feature/context

**📝 EXAMPLE PROGRESS LOG UPDATE:**
```markdown
## UPDATE: [Session Topic] (2025-07-27)

### Overview
[Brief summary of what was accomplished this session]

### Issues Addressed ✅
- [Specific problem 1]: [Solution implemented]
- [Specific problem 2]: [Solution implemented]

### Technical Implementation
[Key technical details for future reference]

### Next Steps
[What needs to happen in next session]

---
```

**🔧 CLAUDE.md HEADER FORMAT WITH SECURITY:**
```markdown
# Claude Code Guide for RequestDesk.ai

<!-- CLAUDE RESTART CONTEXT - 2025-07-27 15:30 -->
**🔄 LAST SESSION STATUS:**
- **Branch**: [current-branch-name]
- **Working Directory**: [current-working-directory-path]
- **Debug Log**: [branch-name]-debug.log (in working directory)
- **Completed**: [What was finished]
- **In Progress**: [What's currently running/pending]
- **Next Priority**: [Most important next step]

**🔒 SECURITY STATUS:**
- **Critical Issues**: [count] (fixed: [count])
- **Warnings**: [count] (new: [count])
- **Security Score**: [score]/100 ([change] from session start)
- **Report**: `.claude/security-session-end-[timestamp].md`

**📋 IMMEDIATE NEXT STEPS FOR /claude-start:**
1. [Specific action with commands/URLs]
2. [Specific action with commands/URLs]
3. [Security-related action if warnings exist]

**🎯 Success Criteria**: [Clear definition of when next session is complete]
<!-- END CLAUDE RESTART CONTEXT -->

## CLAUDE CODE INSTRUCTIONS - READ FIRST
[Existing CLAUDE.md content continues...]
```

**🎯 COMMIT MESSAGE STRUCTURE WITH SECURITY:**
```
[type]: [session summary]

SESSION SUMMARY:
• [Major accomplishment 1]
• [Major accomplishment 2]
• [Major accomplishment 3]

SECURITY IMPROVEMENTS:
• Fixed: [count] critical issues
• Resolved: [specific security fixes]
• Identified: [count] warnings for future attention
• Security Score: [score]/100 ([change])

FILES UPDATED:
• [file1]: [what was changed and why]
• [file2]: [what was changed and why]

DOCUMENTATION UPDATES:
• [progress log]: Added session summary with security context
• [technical doc]: Enhanced with implementation details
• CHANGELOG.md: Updated current version
• Security Report: .claude/security-session-end-[timestamp].md

NEXT SESSION PRIORITIES:
• [Priority 1 with specific action needed]
• [Security warnings to address if any]
• [Priority 2 with specific action needed]

STATUS: [Current state - deployed/testing/debugging/etc.]

🤖 Generated with [Claude Code](https://claude.ai/code)
Co-Authored-By: Claude <noreply@anthropic.com>
```

**⚡ KEY PRINCIPLES:**
- **FIND existing files, don't create new ones**
- **APPEND to current progress logs**
- **ENHANCE existing documentation**
- **UPDATE current CHANGELOG version**
- **PRESERVE all work in comprehensive commit**
- **HANDOFF via CLAUDE.md header**

**🔍 FILE DISCOVERY PATTERNS:**
- Progress logs: `find /todo/current -name "*progress*.md"`
- Technical docs: `find /documentation -name "*.md" -mtime -7`
- Active changelogs: `CHANGELOG.md` (update current version)
- Implementation docs: Look for files related to current work

This ensures every session follows the exact same pattern while respecting existing project structure and documentation.

---

## 🐛 **DEBUG MODE BEHAVIOR (`--debug` flag)**

When `/claude-close --debug` is used, the command switches to **debug session close** with attempt tracking and specialized debug documentation.

### **Debug Mode Workflow:**

**Phase 1: Debug Log Update**
1. **Find Current Debug Log:**
   - Get current branch: `git branch --show-current`
   - Locate debug log: `/todo/current/[category]/[task-name]/[branch-name]-debug.log`
   - Parse current attempt number from SUMMARY section

2. **Update Debug Log with New Attempt:**
   - Auto-increment attempt number (e.g., #27 → #28)
   - Add new entry to SUMMARY OF ATTEMPTS section
   - Add detailed entry with session results
   - Format: `Attempt #X | YYYY-MM-DD HH:MM | [test case] | [FAILED/SUCCESS] | [description]`

3. **Interactive Debug Documentation:**
   - Prompt user for attempt results:
     - "What was tested this session?"
     - "Result: FAILED/SUCCESS/PENDING?"
     - "What was learned?"
     - "What will be tried next?"

**Phase 2: Branch Context Update (Additive Only)**
4. **Update Branch Context File:**
   - Read `.claude/branch-context/[branch-name]-context.md`
   - **NEVER remove existing context** - only add new information
   - Append debug session results
   - Add attempt findings and next steps
   - Preserve complete debugging history

**Phase 3: Debug-Focused Commit**
5. **Create Debug Commit:**
   - Use debug commit format: `debug: attempt #X - [description]`
   - Include attempt results and findings
   - Reference debug log entry
   - Structured debug commit message:
   ```
   debug: attempt #28 - [brief result description]

   DEBUG SESSION SUMMARY:
   • Attempt #28: [test case] - [FAILED/SUCCESS]
   • Finding: [key discovery or result]
   • Next: [planned next step]

   DEBUG CONTEXT:
   • Total attempts: [count] ([failed count] FAILED, [success count] SUCCESS)
   • Debug log: [branch-name]-debug.log entry #[session number]
   • Branch context: Updated with attempt results

   🤖 Generated with [Claude Code](https://claude.ai/code)
   Co-Authored-By: Claude <noreply@anthropic.com>
   ```

**Phase 4: CLAUDE.md Debug Context Update**
6. **Update CLAUDE.md with Debug Status:**
   - Update restart context to reflect debug session status
   - Include current attempt number and status
   - Point to debug log location
   - Maintain debug session continuity

### **Debug Mode Differences from Standard Close:**
- ✅ **Updates debug log** with attempt tracking and results
- ✅ **Uses debug commit format** (`debug: attempt #X`)
- ✅ **Preserves all existing context** (additive only)
- ✅ **Interactive attempt documentation** with prompts
- ✅ **Maintains debug session continuity** in CLAUDE.md
- ✅ **Focuses on systematic debugging** progress tracking

### **Debug Session File Updates:**
1. **Debug Log** - New attempt entry with results and findings
2. **Branch Context** - Additive debug progress (never removes existing)
3. **CLAUDE.md** - Debug session status and next attempt preparation
4. **Git History** - Debug commit with attempt tracking

### **Debug Commit Message Pattern:**
```
debug: attempt #28 - User AA invitation test successful
debug: attempt #29 - Email verification flow investigation
debug: attempt #30 - Root cause analysis for auth timing
```

This ensures debug sessions maintain systematic attempt tracking and preserve complete debugging context for effective problem-solving continuity.