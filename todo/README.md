# Task Management Documentation Tree

⚠️ **TEMPLATE FILE - DO NOT MODIFY FOR INDIVIDUAL TASKS**

This is a template/overview file. **DO NOT** write planning or progress information directly to this file.

## 🚨 **IMPORTANT FOR CLAUDE:**
- **DO NOT** add task-specific planning or progress to this README.md
- **DO NOT** modify this file for individual feature work
- **ALWAYS** create new files with the task/feature name as prefix:
  - `[task-name]-README.md`
  - `[task-name]-requirements.md`
  - `[task-name]-progress.md`
  - `[task-name]-implementation-plan.md`

**Example**: For "author-display-fix" task, create:
- `author-display-fix-README.md`
- `author-display-fix-requirements.md`
- `author-display-fix-progress.md`

This keeps the template clean and makes task files self-identifying when moved.

## Project Context

**Site**: Content Cucumber WordPress Site  
**Environment**: LocalWP (Local by Flywheel)  
**Repository**: `/Users/brent/LocalSites/contentcucumber/`  
**WordPress**: `app/public/`

## Directory Structure

```
todo/
├── README.md                    # This file - overview only
├── current/                     # Active work in progress
│   ├── feature/                 # New feature development
│   ├── fix/                     # Bug fixes and issue resolution
│   ├── content/                 # Content and editorial work
│   └── maintenance/             # Site maintenance tasks
├── completed/                   # Completed work archives
│   ├── feature/                 # Completed features
│   ├── fix/                     # Resolved issues
│   ├── content/                 # Completed content work
│   └── maintenance/             # Completed maintenance
├── backlog/                     # Future work backlog
├── planning/                    # Planning documents and strategies
└── archive/                     # Historical documents
```

## WordPress-Specific Task Categories

- **feature**: New WordPress functionality, plugins, themes
- **fix**: Bug fixes and issue resolution (like author display issues)
- **content**: Editorial, content creation, SEO work
- **maintenance**: WordPress updates, plugin updates, backups
- **customization**: Theme/plugin customization work

## Work Management Guidelines

### Starting New Work
1. Create task folder in appropriate `/current/[category]/[task-name]/`
2. Include these files with **mandatory task-prefixed names**:
   - `README.md` - Task overview and requirements
   - `[task-name]-requirements.md` - Detailed specifications
   - `[task-name]-progress.md` - Progress log
   - `[task-name]-implementation-plan.md` - Implementation steps
   - `[task-name]-testing-checklist.md` - Testing checklist

### File Naming Convention
- **ALL task-specific files MUST be prefixed with the task name** (except README.md)
- **Format**: `[task-name]-[descriptive-name].md`
- **Examples**: 
  - `author-display-fix-implementation-plan.md`
  - `seo-optimization-requirements.md`
  - `plugin-updates-checklist.md`

## Current Work Status

### Known Issues
- **Author Display Bug**: "Post authoMarisa Jonesr name" concatenation issue on blog posts

### Future Work Queue
- WordPress maintenance and updates
- SEO optimization
- Performance improvements
- Content strategy implementation

## WordPress-Specific Commands

Common LocalWP/WordPress workflows:
- Pull updates from live site
- Test changes in LocalWP environment
- Deploy changes to live site
- Plugin and theme management

## Next Steps

1. Fix author display concatenation issue
2. Implement proper git workflow for WordPress changes
3. Set up content management processes
4. Plan SEO and performance improvements