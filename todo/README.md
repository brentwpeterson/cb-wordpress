# CB-WordPress Todo Management

## Structure

- `current/` - Active tasks and features in development
- `completed/` - Finished tasks and archived work

## Usage with Claude Commands

This todo structure works with the CB workspace Claude commands:
- `/claude-start cb-wordpress` - Start development session
- `/claude-close cb-wordpress` - Close session with progress tracking
- `/claude-debug cb-wordpress` - Debug session with task logging
- `/create-branch cb-wordpress <task-path>` - Create branch from task document

## Task Organization

Each task should be organized as:
```
current/
├── feature/
│   └── task-name/
│       ├── README.md
│       └── [task files]
└── fix/
    └── bug-name/
        ├── README.md
        └── [debug files]
```