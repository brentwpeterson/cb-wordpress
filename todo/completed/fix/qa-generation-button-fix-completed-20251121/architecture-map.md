# WordPress Image Upload Feature - CB Architecture Flow Map

## 🏗️ **CB TECHNICAL LAYERS**

### Frontend Layer
**Components Modified/Created:**
- [ ] Image Upload Component - Handle image selection/generation for blog posts
- [ ] Blog Post Management UI - Add image upload functionality to existing post management
- [ ] Image Preview Component - Display uploaded images with post associations
- [ ] Upload Progress Indicator - Show real-time upload status and progress
- [ ] Error Display Component - Show upload failures and retry options

### DataLayer
**API Integration:**
- [ ] WordPressImageService - Handle image upload operations to WordPress API
- [ ] BlogPostImageProvider - Manage image-to-post associations
- [ ] ImageMetadataService - Store and retrieve image metadata from CB database
- [ ] UploadStatusTracker - Track upload progress and handle retries
- [ ] WordPressAuthProvider - Handle WordPress API authentication

### Router Layer (FastAPI)
**Endpoints:**
- [ ] POST /api/wordpress/images/upload - Upload single image to WordPress and associate with post
- [ ] POST /api/wordpress/images/batch-upload - Upload multiple images for a blog post
- [ ] GET /api/wordpress/images/{post_id} - Retrieve images associated with a blog post
- [ ] DELETE /api/wordpress/images/{image_id} - Remove image from WordPress and CB database
- [ ] POST /api/wordpress/images/retry/{image_id} - Retry failed image upload
- [ ] GET /api/wordpress/images/status - Get upload status for pending/failed images

### Service Layer
**Business Logic:**
- [ ] WordPressImageUploadService.upload_image() - Core image upload logic with WordPress API
- [ ] BlogPostImageService.associate_image() - Link uploaded images to blog posts
- [ ] ImageProcessingService.prepare_image() - Optimize and validate images before upload
- [ ] RetryService.handle_failed_upload() - Implement retry logic with exponential backoff
- [ ] SyncService.update_blog_sync() - Integrate with existing blog post sync mechanism
- [ ] ValidationService.validate_upload_request() - Validate permissions and requirements

### Model Layer
**Data Models:**
- [ ] BlogPostImage - Model for image-to-post associations with metadata
- [ ] ImageUploadStatus - Track upload progress and failure states
- [ ] WordPressImageSchema - Validation schema for WordPress image uploads
- [ ] BatchUploadRequest - Model for multiple image upload operations
- [ ] ImageMetadata - Store image details (URL, filename, alt text, dimensions)
- [ ] RetryAttempt - Track retry attempts and failure reasons

### Collection Layer (Database)
**Database Operations:**
- [ ] blog_post_images - New table for image-to-post relationships
- [ ] image_upload_status - Track upload progress and retry attempts
- [ ] wordpress_image_metadata - Store WordPress-specific image data
- [ ] **Migration Required** - Add image tracking tables to existing blog sync database
- [ ] **Index Creation** - Optimize queries for post-to-image lookups
- [ ] **Foreign Keys** - Link to existing blog_posts table

## 🔄 **DATA FLOW**

### Image Upload Flow
```
User Selects Image → Image Upload Component → DataLayer WordPressImageService →
POST /api/wordpress/images/upload → WordPressImageUploadService.upload_image() →
ImageProcessingService.prepare_image() → WordPress REST API → BlogPostImage Model →
blog_post_images Collection → Success Response → Frontend Update
```

### Error Handling Flow
```
Upload Failure → RetryService.handle_failed_upload() → ImageUploadStatus Model →
image_upload_status Collection → Exponential Backoff Delay → Retry Attempt →
Success/Failure Update → Frontend Notification
```

### Integration with Existing Blog Sync Flow
```
Existing Blog Sync → SyncService.update_blog_sync() → Check for Associated Images →
Update Image-Post Relationships → Maintain Sync Consistency → Complete Sync Operation
```

## 🔌 **INTEGRATION POINTS**

### WordPress API Integration
**Authentication Flow:**
- Reuse existing WordPress auth mechanism from current blog sync
- Verify media upload permissions for authenticated user
- Handle authentication refresh for long-running operations

**Media Upload API:**
- Endpoint: `POST /wp/v2/media`
- Headers: Authorization, Content-Type (multipart/form-data)
- Parameters: file, post (for association), title, alt_text, caption
- Response: WordPress media object with ID, URL, metadata

### Database Integration
**Extend Existing Blog Sync Schema:**
- Add image tracking to current blog post database
- Maintain referential integrity with existing blog_posts table
- Support both WordPress post IDs and CB internal post IDs

### File Processing Integration
**Image Optimization Pipeline:**
- Integrate with existing file processing capabilities
- Apply compression and format optimization before upload
- Generate thumbnails and multiple sizes as needed

## 📝 **IMPLEMENTATION NOTES**

### Phase 1: Foundation
**Priority 1 - Core Upload Functionality:**
- Implement basic WordPress media upload API client
- Create database schema for image tracking
- Build fundamental upload service with error handling

### Phase 2: Integration
**Priority 2 - Blog Sync Integration:**
- Integrate with existing blog post sync mechanism
- Ensure no disruption to current sync operations
- Add image metadata to sync process

### Phase 3: Advanced Features
**Priority 3 - Enhanced Capabilities:**
- Implement batch upload functionality
- Add retry logic and queue management
- Create advanced error handling and monitoring

### Critical Dependencies
**Must Not Break:**
- Existing blog post synchronization functionality
- Current WordPress authentication and connection
- Database integrity for blog_posts table

### Performance Considerations
**Upload Optimization:**
- Implement concurrent upload limiting (max 5 simultaneous)
- Use streaming uploads for large files
- Optimize database queries for image-post lookups
- Cache WordPress authentication tokens

### Security Considerations
**Data Protection:**
- Validate image files before processing
- Sanitize user inputs for image metadata
- Protect WordPress API credentials
- Log security-relevant events without exposing sensitive data

## ✅ **ARCHITECTURE VALIDATION CHECKLIST**
- [ ] All CB layers properly addressed (Frontend → Collection)
- [ ] Data flow clearly mapped and validated
- [ ] Integration points with existing systems identified
- [ ] Database schema changes planned and documented
- [ ] Security and performance considerations included
- [ ] Error handling strategy defined across all layers
- [ ] WordPress API integration properly designed
- [ ] Existing blog sync system integration planned

**Architecture Status:** 🔄 **DRAFT** - Requires validation and refinement during implementation