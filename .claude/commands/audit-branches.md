**COMMAND: Audit Git Branches (LocalWP Project Specific)**

**Usage**: `/audit-branches`

**Purpose:** Review all Git branches for LocalWP WordPress project and clean up merged branches.

**🗂️ PROJECT HANDLING:**
**CRITICAL**: This command works with LocalWP WordPress projects.
1. **Auto-detects LocalWP project** by checking for `app/public/wp-config.php`
2. **Works within current git repository** (LocalWP project git repo)
3. **Creates documentation in project's structure** following WordPress conventions
4. **Integrates with `/todo/` directory structure**

**Phase 0: Directory Setup & Project Navigation**

0. **Detect LocalWP Project:**
   ```bash
   # Store original working directory
   ORIGINAL_DIR=$(pwd)

   # Auto-detect LocalWP project
   if [ -f "app/public/wp-config.php" ]; then
     PROJECT=$(basename $(pwd))
     echo "✅ Detected LocalWP project: $PROJECT"
   else
     echo "❌ Not in a LocalWP project directory. Looking for app/public/wp-config.php"
     exit 1
   fi

   # Verify we're in a git repository
   if [ ! -d ".git" ]; then
     echo "❌ Not in a git repository. LocalWP projects should have git initialized."
     exit 1
   fi

   echo "🎯 Auditing branches for LocalWP project: $PROJECT"
   echo "📍 Working in directory: $(pwd)"
   echo "🏠 WordPress root: app/public/"
   ```

**Phase 1: Setup Documentation Structure**

1. **Check and Create Folders as Needed:**
   ```bash
   # Create project-specific git documentation structure
   if [ ! -d "documentation/docs/technical/git" ]; then
     echo "📁 Creating git documentation structure for $PROJECT..."
     mkdir -p documentation/docs/technical/git/active-branches
     mkdir -p documentation/docs/technical/git/merged-branches
     mkdir -p documentation/docs/technical/git/branch-reports
     mkdir -p documentation/docs/technical/git/scripts
     mkdir -p documentation/docs/technical/git/templates
     echo "✅ Created git documentation structure in $PROJECT"
   fi
   
   # Check for any missing subfolders and create them
   for folder in active-branches merged-branches branch-reports scripts templates; do
     if [ ! -d "documentation/docs/technical/git/$folder" ]; then
       echo "📁 Creating missing folder: documentation/docs/technical/git/$folder"
       mkdir -p "documentation/docs/technical/git/$folder"
     fi
   done
   ```

**Phase 2: Discover and List All Branches**

3. **Get Complete Branch Inventory:**
   ```bash
   # List all local branches
   git branch --format="%(refname:short),%(upstream:short),%(ahead-behind)"
   
   # List all remote branches  
   git branch -r --format="%(refname:short)"
   
   # Check merge status for each branch
   git branch --merged master
   git branch --no-merged master
   ```

4. **Create Active Branch Registry:**
   Generate `documentation/docs/technical/git/ACTIVE-BRANCHES.md`:
   ```markdown
   # Active Git Branches - [DATE]
   
   ## Summary
   - Total branches: [X]
   - Merged with master: [X]
   - Unmerged branches: [X]
   - Protected branches: master
   
   ## Branch Status Overview
   
   ### 🔒 Protected Branches
   | Branch | Purpose | Last Activity | Status |
   |--------|---------|---------------|---------|
   | master | Main development & deployments | [date] | 🔒 Protected |
   
   ### ✅ Merged Branches (Safe to Delete)
   | Branch | Category | Last Commit | Merge Date | Author | Description |
   |--------|----------|-------------|------------|--------|-------------|
   | feature/old-feature | feature | [commit] | [date] | [author] | [description] |
   
   ### 🔄 Active/Unmerged Branches  
   | Branch | Category | Last Commit | Behind Master | Author | Status |
   |--------|----------|-------------|---------------|--------|---------|
   | feature/new-work | feature | [commit] | 5 commits | [author] | In progress |
   
   ### ❓ Investigation Needed
   | Branch | Issue | Last Activity | Recommendation |
   |--------|-------|---------------|----------------|
   | old-branch | Very old, unclear purpose | 6 months ago | Investigate or delete |
   ```

**Phase 3: Interactive Branch Review**

5. **For Each Branch, Collect Information:**
   
   **Technical Data (Automatic):**
   ```bash
   # For each branch:
   git log --oneline -n 5 [branch-name]
   git show --stat [branch-name]  
   git log --pretty=format:"%h %ad %an %s" --date=short -n 10 [branch-name]
   git diff --name-only master..[branch-name]
   git rev-list --count master..[branch-name]  # commits ahead
   git rev-list --count [branch-name]..master  # commits behind
   ```
   
   **Interactive Questions (For Each Branch):**
   ```
   Branch: [name]
   Last commit: [message] ([date])
   Author: [name]
   Commits ahead of master: [X]
   Merge status: [Merged/Unmerged]
   
   Questions:
   1. What category is this branch? 
      (feature/enhancement/fix/bug/hotfix/security/performance/refactor/docs/test/chore)
   
   2. What is the current status?
      a) ✅ Completed and merged
      b) 🔄 Active work in progress  
      c) ⏸️ Paused/blocked work
      d) ❌ Abandoned/obsolete
      e) ❓ Unknown/needs investigation
   
   3. Brief description of this branch's purpose:
      [User input]
   
   4. If unmerged, what's needed to complete?
      [User input for unmerged branches]
   
   5. Safe to delete? (for merged branches)
      [y/n with reasoning]
   ```

**Phase 4: Create Individual Branch Documentation**

6. **For Each Branch, Create Documentation File:**

   **Active branches:** `documentation/docs/technical/git/active-branches/[branch-name].md`
   **Merged branches:** `documentation/docs/technical/git/merged-branches/[branch-name].md`
   
   **Branch Documentation Template:**
   ```markdown
   # Branch: [branch-name]
   
   ## Status
   - **Category:** [feature/enhancement/fix/etc.]
   - **Status:** [Active/Merged/Abandoned/etc.]
   - **Author:** [name]
   - **Created:** [date]
   - **Last Activity:** [date]
   
   ## Purpose
   [User-provided description]
   
   ## Technical Details
   - **Commits ahead of master:** [X]
   - **Commits behind master:** [X]  
   - **Files changed:** [X]
   - **Lines added/removed:** [+X/-X]
   
   ## Recent Commits
   ```
   [Last 5 commits]
   ```
   
   ## Files Modified
   ```
   [List of changed files]
   ```
   
   ## Merge Status
   [Merged/Unmerged with details]
   
   ## Action Items
   [What needs to happen with this branch]
   
   ## Completion Status
   [For unmerged branches - what's needed to finish]
   
   ## Deletion Safety
   [For merged branches - safe to delete reasoning]
   ```

**Phase 5: Generate Reports and Action Plans**

7. **Create Comprehensive Branch Report:**
   Generate `documentation/docs/technical/git/branch-reports/BRANCH-AUDIT-[DATE].md` with **Weekly Context Summary**:
   ```markdown
   # Git Branch Audit Report - [DATE]
   
   ## 📋 REMAINING ACTIVE BRANCHES (Still Being Worked On)
   
   **These branches were preserved during cleanup - here are their TODO links for context:**
   
   ### 🔄 Unmerged Development Branches
   | Branch | Status | TODO Link | Last Activity |
   |--------|---------|-----------|---------------|
   | feature/conversational-brand-building | Active (92 behind master) | [📁 TODO](/todo/current/feature/conversational-brand-building/README.md) | 2025-08-16 |
   | refactor/material-ui-removal | Active (97 behind master) | [📁 TODO](/todo/current/refactor/material-ui-removal/README.md) | 2025-08-15 |
   | fix/dark-mode-font-fixes | Stale (258 behind master) | [📁 TODO](/todo/completed/fix/dark-mode-font-fixes/README.md) | 2025-08-02 |
   | feature/create-persona-sections-from-template | Very Stale (299 behind master) | [📁 TODO](/todo/current/feature/create-persona-sections/README.md) | 2025-07-29 |
   
   ### ✅ Merged But Preserved Branches  
   | Branch | Reason Preserved | TODO Link | Status |
   |--------|------------------|-----------|---------|
   | feature/community-section | [User reason] | [📁 TODO](/todo/current/feature/community-section/README.md) | Review needed |
   | enhancement/llm-terms-phase2 | [User reason] | [📁 TODO](/todo/completed/enhancement/llm-terms-phase2/README.md) | Review needed |
   
   **💡 Weekly Tip:** Click the TODO links above to understand the current status of each remaining branch.
   
   ---
   
   ## Executive Summary
   - Total branches reviewed: [X]
   - Branches cleaned up this session: [X]
   - Merged and safe to delete: [X]
   - Active development branches: [X] 
   - Branches updated with master: [X]
   - Branches with merge conflicts: [X]
   - Abandoned/obsolete branches: [X]
   - Requires investigation: [X]
   
   ## Cleanup Recommendations
   
   ### 🗑️ NUMBERED DELETION CANDIDATES (Merged Branches)
   
   **Review each item carefully - some "merged" branches may still have active work!**
   
   | # | Branch Name | Last Commit | Date | Remote | Status | Recommendation |
   |---|-------------|-------------|------|---------|---------|----------------|
   | 1 | feature/5-star-rating-system | e04001fe | 2025-08-09 | ✅ | Deployed | ✅ Safe to delete |
   | 2 | feature/admin-impersonation | 3339bdc3 | 2025-08-21 | ❌ | ⚠️ May have active work | ⚠️ REVIEW FIRST |
   | 3 | feature/community-section | 23940c60 | 2025-07-16 | ✅ | Deployed | ✅ Safe to delete |
   | 4 | feature/google-drive-integration | d61ea7f1 | 2025-07-28 | ✅ | Deployed | ✅ Safe to delete |
   | 5 | enhancement/llm-terms-phase2 | b7b180f0 | 2025-07-15 | ✅ | Deployed | ✅ Safe to delete |
   | ... | (additional branches) | ... | ... | ... | ... | ... |
   
   **Usage Examples:**
   ```
   Delete items: 1,3,4,5    (comma-separated list)
   Delete range: 1-5        (range notation) 
   Delete single: 2         (single number)
   Skip all: skip           (review later)
   ```
   
   ### Move to Backlog (Unfinished Work)
   - [branch-name] → todo/backlog/[branch-name]-requirements.md
   
   ### Archive (Abandoned)
   - [branch-name] → Archive with documentation
   
   ### Active Development
   - [branch-name] → Continue current work
   
   ### Branch Update Results
   ```bash
   # Successfully Updated Branches
   [list of branches that were updated with master]
   
   # Branches with Merge Conflicts (Need Manual Resolution)
   [list of branches that had conflicts]
   
   # Skipped Branches  
   [list of branches user chose not to update]
   ```
   
   ## Branch Categories Summary
   
   ### Features (Added)
   - [list of feature branches with status]
   
   ### Enhancements (Changed)  
   - [list of enhancement branches with status]
   
   ### Fixes (Fixed)
   - [list of fix branches with status]
   
   ### Technical (Refactor/Docs/Tests)
   - [list of technical branches with status]
   
   ## Workflow Recommendations
   [Suggestions for improving branch management]
   ```

8. **Create Cleanup Scripts:**
   Generate `documentation/docs/technical/git/scripts/cleanup-merged-branches.sh`:
   ```bash
   #!/bin/bash
   echo "Git Branch Cleanup - [DATE]"
   echo "========================================="
   
   echo "The following merged branches are safe to delete:"
   [list branches with confirmation prompts]
   
   echo "Deleting local merged branches..."
   [git branch -d commands with user confirmation]
   
   echo "Deleting remote merged branches..."  
   [git push origin --delete commands with user confirmation]
   
   echo "Cleanup complete!"
   echo "See documentation/docs/technical/git/merged-branches/ for preserved documentation"
   ```

**Phase 6: Ongoing Maintenance**

9. **Create Branch Lifecycle Tracking:**
   
   **When a branch is merged:** Update documentation automatically
   ```bash
   # Script: documentation/docs/technical/git/scripts/mark-branch-merged.sh
   # Usage: mark-branch-merged.sh [branch-name]

   # Move branch doc from active-branches/ to merged-branches/
   # Update ACTIVE-BRANCHES.md registry
   # Add to cleanup candidates list
   ```

10. **Create Regular Branch Health Check:**
    ```bash
    # Script: documentation/docs/technical/git/scripts/branch-health-check.sh
    # Runs weekly to identify stale branches
    
    # Find branches with no activity for 30+ days
    # Find branches far behind master  
    # Generate report of potential cleanup candidates
    ```

**Phase 7: Update Active Branches with Master**

11. **Merge Master into Active Branches:**
    For each branch identified as "Active/Unmerged" in previous phases:
    
    ```bash
    # For each active branch that's behind master:
    for branch in [list-of-active-branches]; do
        echo "🔄 Updating branch: $branch"
        
        # Show how far behind master this branch is
        behind_count=$(git rev-list --count $branch..master)
        echo "Branch $branch is $behind_count commits behind master"
        
        # Interactive confirmation
        echo "Merge master into $branch? (y/n/skip_all)"
        read -r merge_response
        
        if [[ "$merge_response" =~ ^[Yy]$ ]]; then
            # Save current branch
            current_branch=$(git branch --show-current)
            
            # Switch to target branch
            git checkout $branch
            
            # Pull latest from remote if it exists
            if git ls-remote --heads origin $branch | grep -q $branch; then
                echo "Pulling latest changes for $branch"
                git pull origin $branch
            fi
            
            # Merge master into this branch
            echo "Merging master into $branch..."
            if git merge master --no-edit; then
                echo "✅ Successfully merged master into $branch"
                
                # Push updated branch to remote if it exists
                if git ls-remote --heads origin $branch | grep -q $branch; then
                    echo "Push updated $branch to remote? (y/n)"
                    read -r push_response
                    if [[ "$push_response" =~ ^[Yy]$ ]]; then
                        git push origin $branch
                        echo "✅ Pushed updated $branch to remote"
                    fi
                fi
            else
                echo "❌ Merge conflicts detected in $branch"
                echo "Please resolve conflicts manually:"
                echo "1. Fix conflicts in the listed files"
                echo "2. Run: git add ."
                echo "3. Run: git commit"
                echo "4. Continue with audit or skip this branch"
                
                echo "Continue with merge conflict resolution now? (y/n/skip)"
                read -r conflict_response
                if [[ "$conflict_response" =~ ^[Ss] ]]; then
                    echo "⏭️ Skipping $branch - conflicts need manual resolution"
                    git merge --abort
                elif [[ "$conflict_response" =~ ^[Yy]$ ]]; then
                    echo "⏸️ Pausing audit for manual conflict resolution"
                    echo "After resolving conflicts, re-run the audit command"
                    exit 1
                fi
            fi
            
            # Return to original branch
            git checkout $current_branch
            
        elif [[ "$merge_response" =~ ^[Ss] ]]; then
            echo "⏭️ Skipping remaining merge operations"
            break
        else
            echo "⏭️ Skipped $branch"
        fi
        
        echo "----------------------------------------"
    done
    ```

**Phase 8: Integration with TODO System**

12. **Link Branches to TODO Items:**
    - Create `todo/backlog/[branch-name]-requirements.md` for unfinished work
    - Update TODO category folders with completed branch work
    - Cross-reference branch documentation with TODO items

13. **Update Main Documentation:**
    - Add branch inventory to main project documentation
    - Link Git workflow to development process
    - Update CLAUDE.md with branch management references

**Interactive Execution Flow:**

1. **Setup:** Create folder structure and protection
2. **Discovery:** Find all branches and get technical details  
3. **Interview:** Go through each branch asking status questions
4. **Documentation:** Generate individual branch docs and master registry
5. **Branch Updates:** Merge master into active branches (with conflict handling)
6. **Reporting:** Create audit report with cleanup recommendations and update results
7. **Action:** Generate cleanup scripts and TODO backlog items

**Success Criteria:**
- Complete inventory of all Git branches with status
- Individual documentation for each branch  
- Clear action plan for cleanup and ongoing work
- Integration with TODO system for unfinished work
- Automated tracking system for future branch management

**Phase 9: Return to Original Directory**

14. **Return to Original Working Directory:**
    ```bash
    # Return to original directory
    cd "$ORIGINAL_DIR"
    echo "📍 Returned to original directory: $(pwd)"

    # Summary of completed work
    echo "✅ Branch audit completed for project: $PROJECT"
    echo "📋 Documentation created in: $PROJECT/documentation/docs/technical/git/"
    echo "🗂️  Branch reports available for review"
    ```

**Usage:**
```bash
/audit-branches cb-requestdesk
/audit-branches cb-shopify
/audit-branches cb-wordpress
/audit-branches cb-magento
/audit-branches cb-junogo

# Auto-detect project from current directory:
/audit-branches
```

## 🚨 MANDATORY FINAL OUTPUT 

**CRITICAL: The command MUST ALWAYS end with these actionable lists - never just generate a report without providing the actual lists:**

```
## 🗑️ DELETION CANDIDATES (Merged Branches)

**Ready for cleanup - these are all merged and deployed:**

1. branch-name-1
2. branch-name-2  
3. branch-name-3

**Usage:** 1,2,3 (delete all) or 1,3 (selective)

## 🔄 UPDATE CANDIDATES (Active Branches)

**These branches are behind master and need updates:**

A. active-branch-1 (X behind master)
B. active-branch-2 (X behind master)

**Usage:** A,B (update both) or A (update single)

---

**Ready for your decision:**
- Delete branches: [numbers]
- Update branches: [letters]  
- Skip: skip

Which action would you like to take?
```

**🗓️ Weekly Cleanup Workflow:**
1. **Run audit command** - Gets complete branch overview
2. **Review numbered deletion list** - Easy selective cleanup  
3. **Update active branches** - Merge master into current work
4. **Check TODO links** - Context for remaining branches
5. **Generate weekly report** - Documents what was cleaned up and what remains

**Benefits:**
- **Quick context** - TODO links show purpose of remaining branches
- **Safe cleanup** - Numbered system prevents accidental deletions  
- **Weekly maintenance** - Keeps repository clean and organized
- **Work continuity** - Easy to resume any branch with proper context

This creates a living documentation system that tracks all your Git branches and maintains their lifecycle.