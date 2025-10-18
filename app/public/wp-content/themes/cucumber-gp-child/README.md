# Cucumber GP Child Theme

Content Cucumber's custom child theme for GeneratePress with version-controlled customizations.

## Overview

This child theme extends GeneratePress with custom styles and functionality specific to the Content Cucumber website.

## Features

- ✅ **Hero Section Styling**: Custom CSS for hero elements with GenerateBlocks overrides
- ✅ **Version Controlled**: All customizations tracked in git
- ✅ **Update Safe**: Child theme approach preserves customizations during parent theme updates
- ✅ **Proper Dependencies**: CSS loads in correct order to override existing styles

## File Structure

```
cucumber-gp-child/
├── style.css           # Child theme metadata and base styles
├── functions.php       # Custom functionality and style enqueuing
├── hero-styles.css     # Hero section styling with GenerateBlocks overrides
└── README.md           # This documentation
```

## CSS Override Strategy

The theme uses high-specificity selectors to override GenerateBlocks styles:

```css
/* Example: Override .gb-text-5d75bda6 */
.gb-container .gb-text.gb-text-5d75bda6,
.gb-element-ee65084a .gb-text.gb-text-5d75bda6,
div.gb-text.gb-text.gb-text-5d75bda6 {
    /* Custom styles with !important declarations */
}
```

## Installation

1. Upload the `cucumber-gp-child` folder to `/wp-content/themes/`
2. Activate "Cucumber GP Child" in WordPress Admin → Appearance → Themes
3. Customizations will automatically load

## Development

- Edit `hero-styles.css` for hero section styling
- Add custom functions to `functions.php`
- Version control all changes through git

## Version History

- **1.0.0** - Initial child theme with hero styling overrides