# How to Apply Content Fixes

## Manual Fix Instructions

Since the SQL export is blocked by .gitignore, apply these fixes manually:

### Fix 1: Author Display (GP Element ID 387 - Right Sidebar)
```bash
wp post update 387 --post_content="$(wp post get 387 --field=post_content | sed 's/Post autho{{author_meta key:display_name|link:author_archive}}r name/Post author: {{author_meta key:display_name|link:author_archive}}/')"
```

### Fix 2: Post Title (GP Element ID 19948 - Single Post)  
```bash
wp post update 19948 --post_content="$(wp post get 19948 --field=post_content | sed 's/>Post title</>{{post_title}}</')"
```

## What These Fixes Do
1. **Author Display**: Changes "Post authoMarisa Jonesr name" to "Post author: Marisa Jones"
2. **Post Title**: Changes literal "Post title" to actual post titles like "What is Enterprise SEO?"