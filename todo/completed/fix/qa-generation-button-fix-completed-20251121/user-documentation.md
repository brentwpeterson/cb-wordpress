# WordPress Image Upload Feature - User Documentation

## 📚 **PUBLIC DOCUMENTATION**

### Feature Overview
**WordPress Image Upload Integration**

This feature enables automatic creation and upload of images to WordPress blog posts within the CB ecosystem. Users can generate images for their blog content and seamlessly push them to their WordPress site while maintaining synchronization with the CB database.

**Key Benefits:**
- Automated image creation and upload to WordPress
- Seamless integration with existing blog post management
- Automatic image-to-post associations
- Robust error handling and retry mechanisms
- Real-time upload progress tracking

### How to Use

#### Basic Image Upload
1. **Access Blog Post Management**
   - Navigate to your blog post in the CB interface
   - Look for the "Images" or "Media" section

2. **Create/Select Images**
   - Use the image generation feature to create new images
   - Or select existing images from your library
   - Preview images before uploading

3. **Upload to WordPress**
   - Click "Upload to WordPress" button
   - Monitor progress via real-time status indicator
   - Verify successful upload in WordPress media library

#### Batch Image Upload
1. **Select Multiple Images**
   - Choose multiple images for a single blog post
   - Review selected images in preview panel

2. **Configure Upload Settings**
   - Set image titles and alt text
   - Choose image optimization settings
   - Select upload priority

3. **Execute Batch Upload**
   - Initiate batch upload process
   - Monitor individual image progress
   - Handle any failed uploads via retry mechanism

#### Error Handling & Retry
1. **Monitor Upload Status**
   - View real-time upload progress
   - Receive notifications for completed uploads
   - Get alerts for failed uploads

2. **Handle Failed Uploads**
   - Review error details for failed images
   - Use automatic retry for temporary failures
   - Manually retry with different settings if needed

### API Documentation

#### Core Endpoints

**Upload Single Image**
```http
POST /api/wordpress/images/upload
Content-Type: multipart/form-data
Authorization: Bearer {token}

Parameters:
- file: Image file (required)
- post_id: Blog post ID (required)
- title: Image title (optional)
- alt_text: Alternative text (optional)
- caption: Image caption (optional)

Response:
{
  "success": true,
  "image_id": "wp_123456",
  "url": "https://wordpress.site/wp-content/uploads/image.jpg",
  "post_association": "post_789",
  "upload_status": "completed"
}
```

**Batch Upload Multiple Images**
```http
POST /api/wordpress/images/batch-upload
Content-Type: application/json
Authorization: Bearer {token}

Body:
{
  "post_id": "post_789",
  "images": [
    {
      "file_path": "/path/to/image1.jpg",
      "title": "Featured Image",
      "alt_text": "Description of image"
    },
    {
      "file_path": "/path/to/image2.png",
      "title": "Supporting Image",
      "alt_text": "Additional description"
    }
  ]
}

Response:
{
  "batch_id": "batch_456",
  "total_images": 2,
  "successful_uploads": 2,
  "failed_uploads": 0,
  "results": [...]
}
```

**Get Upload Status**
```http
GET /api/wordpress/images/status
Authorization: Bearer {token}

Response:
{
  "pending_uploads": 3,
  "failed_uploads": 1,
  "recent_uploads": [...],
  "retry_queue": [...]
}
```

### Error Codes & Troubleshooting

| Error Code | Description | Solution |
|------------|-------------|----------|
| `WP_AUTH_FAILED` | WordPress authentication failed | Check WordPress credentials |
| `FILE_TOO_LARGE` | Image file exceeds size limit | Compress image or reduce dimensions |
| `UNSUPPORTED_FORMAT` | Image format not supported | Convert to JPEG, PNG, or WebP |
| `NETWORK_TIMEOUT` | Upload timed out | Retry upload or check network connection |
| `POST_NOT_FOUND` | Blog post doesn't exist | Verify post ID and sync status |
| `PERMISSION_DENIED` | Insufficient WordPress permissions | Check user media upload permissions |

## 🔒 **INTERNAL DOCUMENTATION**

### Developer Notes

#### Implementation Architecture
**WordPress API Integration:**
- Uses WordPress REST API v2 for media uploads
- Implements OAuth authentication with refresh token support
- Handles file uploads via multipart/form-data
- Associates uploaded media with blog posts via post parameter

**Database Schema:**
```sql
-- New table for tracking image uploads
CREATE TABLE blog_post_images (
    id SERIAL PRIMARY KEY,
    cb_post_id VARCHAR(255) NOT NULL,
    wordpress_post_id VARCHAR(255),
    wordpress_image_id VARCHAR(255),
    image_url VARCHAR(500),
    filename VARCHAR(255),
    alt_text TEXT,
    upload_status VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (cb_post_id) REFERENCES blog_posts(id)
);

-- Track upload attempts and failures
CREATE TABLE image_upload_attempts (
    id SERIAL PRIMARY KEY,
    blog_post_image_id INTEGER,
    attempt_number INTEGER,
    status VARCHAR(50),
    error_message TEXT,
    attempted_at TIMESTAMP,
    FOREIGN KEY (blog_post_image_id) REFERENCES blog_post_images(id)
);
```

#### Configuration Settings
**WordPress Connection:**
- `WORDPRESS_API_URL`: WordPress site REST API endpoint
- `WORDPRESS_CLIENT_ID`: OAuth application client ID
- `WORDPRESS_CLIENT_SECRET`: OAuth application client secret
- `WORDPRESS_REDIRECT_URI`: OAuth callback URL

**Upload Settings:**
- `MAX_IMAGE_SIZE`: Maximum file size for uploads (default: 10MB)
- `SUPPORTED_FORMATS`: Allowed image formats (JPEG, PNG, WebP)
- `CONCURRENT_UPLOAD_LIMIT`: Maximum simultaneous uploads (default: 5)
- `RETRY_ATTEMPTS`: Number of retry attempts for failed uploads (default: 3)

#### Integration Points
**Blog Post Sync System:**
- Hooks into existing blog post synchronization workflow
- Updates image associations when posts are synced
- Maintains consistency between CB database and WordPress

**Error Handling:**
- Implements exponential backoff for retry attempts
- Logs all upload attempts with detailed error information
- Queues failed uploads for manual review and retry

### Troubleshooting Guide

#### Common Issues

**Upload Failures:**
1. **Check WordPress Connectivity**
   - Verify WordPress site is accessible
   - Test API endpoint availability
   - Validate authentication credentials

2. **File Validation Issues**
   - Check image file format compatibility
   - Verify file size within limits
   - Ensure file is not corrupted

3. **Permission Problems**
   - Confirm user has media upload permissions in WordPress
   - Verify API user has sufficient privileges
   - Check WordPress security plugins blocking uploads

#### Monitoring & Logging
**Key Metrics to Monitor:**
- Upload success rate (target: >95%)
- Average upload time per image
- Failed upload queue length
- WordPress API response times

**Log Locations:**
- Upload attempts: `/logs/wordpress-image-uploads.log`
- Error details: `/logs/wordpress-upload-errors.log`
- Performance metrics: `/logs/wordpress-upload-performance.log`

#### Debugging Steps
1. **Enable Debug Mode**
   - Set `DEBUG_WORDPRESS_UPLOADS=true` in environment
   - Increases logging verbosity for upload operations

2. **Test WordPress Connectivity**
   - Use built-in connection test endpoint
   - Verify API authentication is working

3. **Manual Upload Testing**
   - Test uploads directly via WordPress admin
   - Compare with API upload behavior

### Security Considerations

#### Data Protection
**Sensitive Information:**
- WordPress API credentials stored securely
- User authentication tokens encrypted
- Upload logs sanitized to remove sensitive data

**File Security:**
- Image files validated for malicious content
- File type verification beyond extension checking
- Size limits enforced to prevent DoS attacks

#### Access Control
- Upload permissions validated at API level
- User authorization checked before processing
- WordPress-side permissions respected and enforced

## 📋 **DOCUMENTATION CHECKLIST**
- [x] User guide for basic image upload functionality
- [x] API documentation with examples and error codes
- [x] Installation and configuration instructions
- [x] Troubleshooting guide for common issues
- [x] Internal developer documentation
- [x] Database schema and integration details
- [x] Security considerations and best practices
- [ ] Video tutorials for user training (planned)
- [ ] Integration examples for developers (planned)
- [ ] Performance optimization guide (planned)

## 🚀 **FUTURE ENHANCEMENTS**
- **Advanced Image Editing**: Built-in image editing capabilities
- **AI-Generated Images**: Integration with AI image generation services
- **Bulk Management**: Mass image operations across multiple posts
- **CDN Integration**: Automatic CDN upload for performance optimization
- **Image SEO**: Automated alt text and metadata generation