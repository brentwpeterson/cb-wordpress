# RequestDesk WordPress Plugin - AEO/AIO/GEO Extension

## Overview

The RequestDesk WordPress Plugin v2.0.0 now includes comprehensive **Answer Engine Optimization (AEO)**, **AI Optimization (AIO)**, and **Generative Engine Optimization (GEO)** capabilities. This extension optimizes your WordPress content for AI-powered search engines like ChatGPT, Claude, Perplexity, and other emerging AI platforms.

## What is AEO/AIO/GEO?

- **Answer Engine Optimization (AEO)**: Optimizing content structure and format for AI engines that provide direct answers
- **AI Optimization (AIO)**: Using AI tools and techniques to enhance content creation and optimization
- **Generative Engine Optimization (GEO)**: Optimizing for AI systems that generate responses rather than just ranking pages

## Key Features

### 🧠 Intelligent Content Analysis
- **Q&A Pair Extraction**: Automatically identifies and extracts question-answer pairs from content
- **Content Structure Analysis**: Evaluates content for AI-friendly formatting
- **Readability Assessment**: Calculates readability scores optimized for AI consumption
- **AI Readiness Scoring**: Comprehensive scoring system (0-100) for AI optimization

### 📊 Citation Statistics Tracking
- **Statistic Identification**: Finds percentages, numbers, ratios, and data points
- **Citation Quality Scoring**: Assesses how likely statistics are to be cited by AI
- **Context Analysis**: Provides context around statistics for better AI understanding
- **Source Reliability Tracking**: Monitors the reliability and freshness of data

### 🕒 Content Freshness Monitoring
- **Multi-Factor Freshness Scoring**: Age, update frequency, content indicators
- **Automated Recommendations**: Actionable suggestions for content updates
- **Freshness Alerts**: Notifications when content becomes stale
- **Update Tracking**: Monitors content modification patterns

### 🏷️ Advanced Schema Markup
- **FAQ Schema**: Automatically generates FAQ structured data from Q&A pairs
- **Article Schema**: Enhanced article markup with AEO metadata
- **HowTo Schema**: Structured data for instructional content
- **Organization Schema**: Brand and authority markup

### 📈 Performance Analytics
- **AEO Score Dashboard**: Track optimization scores across all content
- **Citation Analytics**: Monitor which statistics get AI citations
- **Freshness Reports**: Identify content needing updates
- **Bulk Optimization Tools**: Mass optimization capabilities

## Installation & Setup

### Prerequisites
- WordPress 5.0+
- PHP 7.4+
- RequestDesk account and API key

### Activation
1. The AEO extension is automatically included in RequestDesk Plugin v2.0.0
2. Navigate to **WordPress Admin → RequestDesk → AEO Settings**
3. Configure your AEO preferences
4. Enable auto-optimization features

### Configuration Options

#### Core Settings
- **Enable AEO System**: Master toggle for all AEO functionality
- **Auto-Optimization**: Choose when optimization runs (publish, update, manual)
- **Minimum Content Length**: Set threshold for optimization (default: 300 words)

#### Q&A Extraction
- **Extract Q&A Pairs**: Automatically find question-answer content
- **Confidence Threshold**: Quality control for extracted Q&A (50%-90%)
- **Manual Q&A Management**: Add custom question-answer pairs

#### Schema Markup
- **FAQ Schema Generation**: Create structured data from Q&A pairs
- **Article Schema**: Enhanced article markup with AEO data
- **Schema Validation**: Automatic validation of generated markup

#### Citation Tracking
- **Statistics Extraction**: Find citation-ready data points
- **Quality Assessment**: Score statistics for citation potential
- **Citation Monitoring**: Track which stats get AI citations

#### Freshness Monitoring
- **Content Age Tracking**: Monitor content aging
- **Update Recommendations**: Get suggestions for content refresh
- **Freshness Alerts**: Notifications for stale content

## Using the AEO Extension

### Individual Post/Page Optimization

#### AEO Meta Box (Post Editor Sidebar)
- **Optimization Score**: Current AEO score (0-100%)
- **Freshness Score**: Content freshness rating
- **Action Buttons**:
  - "Optimize Content": Full AEO analysis and optimization
  - "Analyze Only": Analysis without applying changes
- **Quick Stats**: Q&A pairs count, statistics found

#### Q&A Pairs Meta Box
- **Extracted Pairs**: View automatically found Q&A content
- **Quality Indicators**: Confidence scores for each pair
- **Manual Management**: Add, edit, or remove Q&A pairs
- **Schema Preview**: See how pairs will appear in structured data

#### Citation Statistics Meta Box
- **Found Statistics**: All identified citation-ready data
- **Quality Badges**: Citation potential scoring
- **Context Display**: Surrounding context for each statistic
- **Type Classification**: Percentage, numeric, ratio, change, etc.

### Bulk Operations

#### Bulk AEO Tools Page
- **Mass Optimization**: Optimize multiple posts simultaneously
- **Selective Processing**: Choose specific posts for optimization
- **Progress Tracking**: Monitor bulk operation progress
- **Results Summary**: Success/failure reporting

#### Quick Actions
- **Optimize All Published Content**: Site-wide optimization
- **Analyze All Content**: Analysis without changes
- **Refresh Freshness Scores**: Update content aging data

### Analytics Dashboard

#### AEO Analytics Page
- **Content Statistics Overview**: Total stats, high-quality count
- **Freshness Breakdown**: Content freshness distribution
- **Statistic Types**: Most common data types found
- **Content Needing Attention**: Posts requiring updates

## API Integration

### Enhanced RequestDesk RAG Sync
The AEO extension enhances the RequestDesk RAG (Retrieval-Augmented Generation) system by including:

- **Q&A Pairs**: For better AI training and response generation
- **Citation Statistics**: High-quality data points for AI reference
- **Content Analysis**: Structural and readability insights
- **Freshness Data**: Content age and update frequency
- **Schema Types**: Structured data classifications

### REST API Endpoints

#### Get AEO Data
```
GET /wp-json/requestdesk/v1/aeo-data/{post_id}
```

#### Optimize Content
```
POST /wp-json/requestdesk/v1/optimize-content
{
  "post_id": 123,
  "force": true
}
```

## Database Structure

### AEO Data Table
```sql
wp_requestdesk_aeo_data
- id (primary key)
- post_id (foreign key)
- content_type (post/page)
- aeo_score (0-100 optimization score)
- last_analyzed (timestamp)
- ai_questions (JSON: Q&A pairs)
- faq_data (JSON: FAQ schema)
- citation_stats (JSON: statistics)
- optimization_status (pending/processing/completed)
```

### Post Meta Fields
- `_requestdesk_aeo_score`: Quick access to optimization score
- `_requestdesk_freshness_score`: Content freshness rating
- `_requestdesk_citation_stats`: Citation-ready statistics
- `_requestdesk_aeo_last_update`: Last optimization timestamp

## Best Practices

### Content Optimization
1. **Write Question-Forward Headings**: Use "How to...", "What is...", "Why does..."
2. **Include Direct Answers**: Follow questions with clear, concise answers
3. **Add Statistics and Data**: Include current, sourced statistics
4. **Structure Content Clearly**: Use headings, lists, and logical flow
5. **Update Regularly**: Keep content fresh with recent information

### AEO Implementation
1. **Start with High-Traffic Content**: Optimize your most visited pages first
2. **Monitor Freshness Scores**: Address stale content proactively
3. **Review Q&A Extractions**: Manually refine automatically extracted pairs
4. **Track Citation Performance**: Monitor which statistics get AI citations
5. **Test Schema Markup**: Validate structured data with Google's tools

### Performance Optimization
1. **Use Bulk Tools**: Process multiple posts efficiently
2. **Schedule Regular Reviews**: Set up freshness monitoring alerts
3. **Monitor AEO Scores**: Track optimization progress over time
4. **Optimize Database**: Regular cleanup of stale AEO data

## Troubleshooting

### Common Issues

#### AEO Not Running
- **Check Settings**: Ensure AEO is enabled in settings
- **Verify Permissions**: User needs `edit_posts` capability
- **Content Length**: Must meet minimum word count threshold
- **Debug Logs**: Check WordPress debug.log for errors

#### Low AEO Scores
- **Add Q&A Content**: Include more question-answer pairs
- **Improve Structure**: Use clear headings and organization
- **Include Statistics**: Add relevant data points
- **Update Content**: Refresh stale information

#### Schema Markup Issues
- **Validate Markup**: Use Google's Structured Data Testing Tool
- **Check Q&A Quality**: Ensure high-confidence pairs are used
- **Review Content Structure**: Proper heading hierarchy required

### Debug Information

#### Enable WordPress Debug Mode
```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### Check Debug Log
- **Location**: `wp-content/debug.log`
- **AEO Entries**: Look for "RequestDesk" prefixed messages
- **Common Issues**: API errors, database issues, processing failures

#### Database Health Check
- **Tables**: Verify `wp_requestdesk_aeo_data` table exists
- **Data Integrity**: Check for orphaned records
- **Index Performance**: Monitor query performance

## Development

### File Structure
```
requestdesk-connector/
├── requestdesk-connector.php          # Main plugin file
├── includes/
│   ├── class-requestdesk-aeo-core.php         # Core AEO orchestrator
│   ├── class-requestdesk-content-analyzer.php  # Content analysis
│   ├── class-requestdesk-schema-generator.php  # Schema markup
│   ├── class-requestdesk-citation-tracker.php  # Statistics tracking
│   ├── class-requestdesk-freshness-tracker.php # Freshness monitoring
│   └── class-requestdesk-push.php             # Enhanced RAG sync
└── admin/
    ├── aeo-settings-page.php          # AEO configuration
    └── aeo-meta-boxes.php            # Post editor integration
```

### Extending the System

#### Adding New Analysis Types
1. Extend `RequestDesk_Content_Analyzer`
2. Add new scoring methods
3. Update database schema if needed
4. Integrate with core optimization workflow

#### Custom Schema Types
1. Extend `RequestDesk_Schema_Generator`
2. Add new schema generation methods
3. Register with core AEO system
4. Test with structured data tools

#### New Citation Types
1. Extend `RequestDesk_Citation_Tracker`
2. Add pattern matching for new data types
3. Update quality scoring algorithms
4. Integrate with analytics dashboard

## Support

### Documentation
- **Plugin Settings**: WordPress Admin → RequestDesk → AEO Settings
- **Analytics Dashboard**: WordPress Admin → RequestDesk → AEO Analytics
- **Bulk Tools**: WordPress Admin → RequestDesk → Bulk AEO Tools

### Getting Help
- **Support Portal**: https://requestdesk.ai/support
- **Documentation**: https://requestdesk.ai/docs/aeo-guide
- **GitHub Issues**: Report bugs and feature requests
- **WordPress Debug Log**: First line of troubleshooting

### Version History
- **v2.0.0**: Initial AEO/AIO/GEO extension release
- **v1.3.0**: Base RequestDesk functionality
- **v1.2.0**: Enhanced API and push capabilities

---

*This extension represents a significant advancement in content optimization for the AI-powered search era. By implementing AEO/AIO/GEO strategies, your content becomes more discoverable, citable, and valuable to both AI systems and human readers.*