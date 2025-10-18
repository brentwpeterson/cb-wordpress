# CB-WordPress Claude Configuration

**🔗 CENTRALIZED CONFIGURATION**

This project uses **centralized Claude configuration** from the CB-Workspace root.

## 📁 Configuration Location

All Claude commands, settings, and documentation are located at:

```
/Users/brent/scripts/CB-Workspace/.claude/
```

## 🧭 Available Resources

### Commands
- **All Claude commands**: `../../../.claude/commands/`
- **Branch context files**: `../../../.claude/branch-context/`

### Key Files
- **Root directory map**: `../../../.claude/root-directory-map.md`
- **Violations log**: `../../../.claude/violations/incorrect-instruction-log.md`

## 🚀 How to Use

When working in cb-wordpress, Claude automatically:
- ✅ **Loads commands** from workspace root `.claude/commands/`
- ✅ **Saves branch context** to workspace root `.claude/branch-context/`
- ✅ **Uses unified settings** across all CB projects

## 🎯 Project-Specific Instructions

For cb-wordpress specific instructions, see:
- **Project docs**: `cb-wordpress/CLAUDE.md` (if exists)
- **Task context**: `cb-wordpress/todo/current/[category]/[task-name]/`

## 🔄 Migration Complete

- **Status**: ✅ Centralized configuration active
- **Migration date**: 2025-10-11
- **Benefit**: Unified Claude experience across all CB-Workspace projects

This ensures consistency and easier maintenance across cb-requestdesk, cb-shopify, cb-wordpress, cb-magento, and cb-junogo.