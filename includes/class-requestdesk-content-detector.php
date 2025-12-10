<?php
/**
 * RequestDesk Content Detector
 *
 * Detects content types for automatic schema markup generation
 * Optimized for AI/LLM visibility
 */

class RequestDesk_Content_Detector {

    /**
     * Detection confidence thresholds
     */
    const CONFIDENCE_HIGH = 0.8;
    const CONFIDENCE_MEDIUM = 0.6;
    const CONFIDENCE_LOW = 0.4;

    /**
     * Claude integration instance
     */
    private $claude_integration;

    /**
     * Constructor
     */
    public function __construct() {
        if (class_exists('RequestDesk_Claude_Integration')) {
            $this->claude_integration = new RequestDesk_Claude_Integration();
        }
    }

    /**
     * Detect all applicable schema types for content
     *
     * @param WP_Post $post The post object
     * @return array Detection results with confidence scores
     */
    public function detect_schema_types($post) {
        $detected = array();

        // Run all detectors
        $detected['product'] = $this->detect_product_content($post);
        $detected['local_business'] = $this->detect_local_business($post);
        $detected['video'] = $this->detect_video_content($post);
        $detected['course'] = $this->detect_course_content($post);
        $detected['breadcrumb'] = $this->should_generate_breadcrumbs($post);

        return $detected;
    }

    /**
     * Detect product/review content
     *
     * @param WP_Post $post The post object
     * @return array Detection result
     */
    public function detect_product_content($post) {
        $content = $post->post_content . ' ' . $post->post_title;
        $signals = array();
        $confidence = 0;

        // Check for WooCommerce product post type first
        if ($post->post_type === 'product') {
            return array(
                'detected' => true,
                'confidence' => 1.0,
                'signals' => array('woocommerce_product'),
                'type' => 'Product'
            );
        }

        // Price patterns: $XX.XX, USD, EUR, price:
        if (preg_match('/\$\d+(?:\.\d{2})?|\b(?:USD|EUR|GBP|CAD|AUD)\s*\d+|\bprice[:\s]+\$?\d+/i', $content)) {
            $signals[] = 'price_detected';
            $confidence += 0.25;
        }

        // Product terminology
        $product_terms = array(
            'buy now', 'purchase', 'add to cart', 'in stock', 'out of stock',
            'SKU', 'product', 'item', 'available', 'ships', 'delivery',
            'warranty', 'guarantee', 'free shipping', 'order now'
        );
        $terms_found = 0;
        foreach ($product_terms as $term) {
            if (stripos($content, $term) !== false) {
                $signals[] = 'product_term:' . $term;
                $terms_found++;
            }
        }
        $confidence += min(0.3, $terms_found * 0.08);

        // Rating patterns: X/5 stars, X out of 5, rating
        if (preg_match('/\d(?:\.\d)?\s*(?:\/\s*5|out of 5|stars?)|(?:rating|review)[:\s]+\d/i', $content)) {
            $signals[] = 'rating_detected';
            $confidence += 0.2;
        }

        // Review keywords
        if (preg_match('/\b(?:review|reviews|rated|rating|customer feedback)\b/i', $content)) {
            $signals[] = 'review_keywords';
            $confidence += 0.1;
        }

        // Check for product shortcodes or blocks
        if (preg_match('/\[product|\[woocommerce|wp:woocommerce/i', $content)) {
            $signals[] = 'product_shortcode';
            $confidence += 0.3;
        }

        // Check for product schema hints in title
        if (preg_match('/\b(?:review|vs|versus|comparison|best|top \d+)\b/i', $post->post_title)) {
            $signals[] = 'product_title_hint';
            $confidence += 0.15;
        }

        return array(
            'detected' => $confidence >= self::CONFIDENCE_MEDIUM,
            'confidence' => min(1.0, $confidence),
            'signals' => $signals,
            'type' => 'Product'
        );
    }

    /**
     * Detect local business content
     *
     * @param WP_Post $post The post object
     * @return array Detection result
     */
    public function detect_local_business($post) {
        $content = $post->post_content . ' ' . $post->post_title;
        $signals = array();
        $confidence = 0;

        // Address patterns
        $address_patterns = array(
            '/\d+\s+[\w\s]+(?:Street|St|Avenue|Ave|Road|Rd|Boulevard|Blvd|Drive|Dr|Lane|Ln|Way|Court|Ct|Place|Pl)[,.\s]/i',
            '/(?:Suite|Ste|Unit|Apt|#)\s*\d+/i',
            '/\b[A-Z]{2}\s*\d{5}(?:-\d{4})?\b/', // US ZIP code
            '/\b[A-Z]\d[A-Z]\s*\d[A-Z]\d\b/i', // Canadian postal code
        );

        foreach ($address_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $signals[] = 'address_pattern';
                $confidence += 0.2;
                break; // Only count once
            }
        }

        // Phone patterns (US/Canada format)
        if (preg_match('/(?:\+1|1)?[-.\s]?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $content)) {
            $signals[] = 'phone_detected';
            $confidence += 0.15;
        }

        // Business hours patterns
        if (preg_match('/(?:open|hours|mon|tue|wed|thu|fri|sat|sun)[:\s]+\d{1,2}(?::\d{2})?\s*(?:am|pm|AM|PM)/i', $content)) {
            $signals[] = 'hours_detected';
            $confidence += 0.2;
        }

        // Alternative hours pattern
        if (preg_match('/\b(?:monday|tuesday|wednesday|thursday|friday|saturday|sunday)\s*[-–:]\s*(?:monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i', $content)) {
            $signals[] = 'hours_range_detected';
            $confidence += 0.15;
        }

        // Business type keywords
        $business_types = array(
            'restaurant', 'store', 'shop', 'clinic', 'office', 'salon',
            'gym', 'spa', 'hotel', 'cafe', 'bar', 'agency', 'studio',
            'dental', 'medical', 'law firm', 'accounting', 'real estate',
            'auto repair', 'veterinary', 'pharmacy', 'bank', 'credit union'
        );
        foreach ($business_types as $type) {
            if (stripos($content, $type) !== false) {
                $signals[] = 'business_type:' . $type;
                $confidence += 0.15;
                break; // Only count primary type
            }
        }

        // Contact/location page detection
        $contact_keywords = array('contact us', 'our location', 'find us', 'visit us', 'directions', 'get directions');
        foreach ($contact_keywords as $keyword) {
            if (stripos($post->post_title, $keyword) !== false || stripos($content, $keyword) !== false) {
                $signals[] = 'contact_page';
                $confidence += 0.2;
                break;
            }
        }

        // About us page with location info
        if (stripos($post->post_title, 'about us') !== false || stripos($post->post_title, 'about') !== false) {
            if (preg_match('/\b(?:located|location|address|headquarters)\b/i', $content)) {
                $signals[] = 'about_with_location';
                $confidence += 0.15;
            }
        }

        // Email pattern
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $content)) {
            $signals[] = 'email_detected';
            $confidence += 0.1;
        }

        return array(
            'detected' => $confidence >= self::CONFIDENCE_MEDIUM,
            'confidence' => min(1.0, $confidence),
            'signals' => $signals,
            'type' => 'LocalBusiness'
        );
    }

    /**
     * Detect video content
     *
     * @param WP_Post $post The post object
     * @return array Detection result with video data
     */
    public function detect_video_content($post) {
        $content = $post->post_content;
        $signals = array();
        $confidence = 0;
        $video_data = array();

        // YouTube embeds and URLs
        if (preg_match_all('/(?:youtube\.com\/(?:embed\/|watch\?v=|v\/)|youtu\.be\/)([\w-]{11})/i', $content, $matches)) {
            $signals[] = 'youtube_embed';
            $confidence += 0.5;
            $video_data['youtube_ids'] = array_unique($matches[1]);
        }

        // Vimeo embeds and URLs
        if (preg_match_all('/vimeo\.com\/(?:video\/)?(\d+)/i', $content, $matches)) {
            $signals[] = 'vimeo_embed';
            $confidence += 0.5;
            $video_data['vimeo_ids'] = array_unique($matches[1]);
        }

        // HTML5 video tags
        if (preg_match_all('/<video[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
            $signals[] = 'html5_video';
            $confidence += 0.5;
            $video_data['video_urls'] = $matches[1];
        }

        // WordPress video blocks
        if (preg_match('/<!-- wp:video|<!-- wp:embed|<!-- wp:core-embed/i', $content)) {
            $signals[] = 'wp_video_block';
            $confidence += 0.4;
        }

        // Video shortcodes
        if (preg_match('/\[video[^\]]*\]|\[embed[^\]]*\]|\[youtube[^\]]*\]|\[vimeo[^\]]*\]/i', $content)) {
            $signals[] = 'video_shortcode';
            $confidence += 0.4;
        }

        // oEmbed URLs on their own line (WordPress auto-embeds these)
        if (preg_match('/^https?:\/\/(?:www\.)?(?:youtube\.com|youtu\.be|vimeo\.com|dailymotion\.com|wistia\.com)/mi', $content)) {
            $signals[] = 'oembed_url';
            $confidence += 0.35;
        }

        // Wistia embeds
        if (preg_match('/wistia\.com|wistia-video/i', $content)) {
            $signals[] = 'wistia_embed';
            $confidence += 0.4;
        }

        // Video in title hints
        if (preg_match('/\b(?:video|watch|tutorial|demo|webinar)\b/i', $post->post_title)) {
            $signals[] = 'video_title_hint';
            $confidence += 0.15;
        }

        return array(
            'detected' => $confidence >= self::CONFIDENCE_LOW, // Lower threshold for video
            'confidence' => min(1.0, $confidence),
            'signals' => $signals,
            'type' => 'VideoObject',
            'video_data' => $video_data
        );
    }

    /**
     * Detect course/educational content
     *
     * @param WP_Post $post The post object
     * @return array Detection result
     */
    public function detect_course_content($post) {
        $content = $post->post_content . ' ' . $post->post_title;
        $signals = array();
        $confidence = 0;

        // Check for LMS plugin post types first
        $lms_post_types = array(
            'sfwd-courses',      // LearnDash
            'course',            // Generic
            'llms_course',       // LifterLMS
            'tutor_course',      // Tutor LMS
            'lp_course',         // LearnPress
            'stm-courses',       // MasterStudy
        );

        if (in_array($post->post_type, $lms_post_types)) {
            return array(
                'detected' => true,
                'confidence' => 1.0,
                'signals' => array('lms_course_type:' . $post->post_type),
                'type' => 'Course'
            );
        }

        // Course terminology
        $course_terms = array(
            'course', 'lesson', 'module', 'curriculum', 'syllabus',
            'enroll', 'enrollment', 'certificate', 'certification',
            'learning objectives', 'prerequisites', 'instructor',
            'lecture', 'tutorial', 'workshop', 'training program',
            'masterclass', 'bootcamp', 'online class'
        );

        $terms_found = 0;
        foreach ($course_terms as $term) {
            if (stripos($content, $term) !== false) {
                $signals[] = 'course_term:' . $term;
                $terms_found++;
            }
        }
        $confidence += min(0.4, $terms_found * 0.1);

        // Learning outcome patterns
        $learning_patterns = array(
            '/(?:you will learn|by the end|learning outcomes?|what you\'ll learn)/i',
            '/(?:objectives?|skills? you\'ll gain|competenc)/i',
            '/(?:upon completion|after this course|students will be able)/i'
        );

        foreach ($learning_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $signals[] = 'learning_outcomes';
                $confidence += 0.2;
                break;
            }
        }

        // Duration patterns for courses
        if (preg_match('/\d+\s*(?:hours?|weeks?|months?|days?)\s*(?:course|training|program|of instruction)/i', $content)) {
            $signals[] = 'course_duration';
            $confidence += 0.15;
        }

        // Check for LMS shortcodes
        $lms_shortcodes = array(
            '[course', '[lesson', '[ld_', '[lifterlms', '[tutor_',
            '[learn_press', '[sensei', 'wp:learndash', 'wp:lifterlms'
        );

        foreach ($lms_shortcodes as $shortcode) {
            if (stripos($content, $shortcode) !== false) {
                $signals[] = 'lms_shortcode';
                $confidence += 0.3;
                break;
            }
        }

        // Course in title
        if (preg_match('/\b(?:course|class|training|certification|bootcamp|masterclass)\b/i', $post->post_title)) {
            $signals[] = 'course_title';
            $confidence += 0.2;
        }

        // Enrollment/pricing keywords
        if (preg_match('/\b(?:enroll now|sign up|register|join|get started|start learning)\b/i', $content)) {
            $signals[] = 'enrollment_cta';
            $confidence += 0.1;
        }

        return array(
            'detected' => $confidence >= self::CONFIDENCE_MEDIUM,
            'confidence' => min(1.0, $confidence),
            'signals' => $signals,
            'type' => 'Course'
        );
    }

    /**
     * Determine if breadcrumbs should be generated
     * Always recommended for AI navigation understanding
     *
     * @param WP_Post $post The post object
     * @return array Detection result
     */
    public function should_generate_breadcrumbs($post) {
        $has_parent = $post->post_parent > 0;
        $has_categories = !empty(wp_get_post_categories($post->ID));
        $has_terms = !empty(wp_get_post_terms($post->ID));

        // Get hierarchy depth for pages
        $hierarchy_depth = 0;
        if ($post->post_type === 'page' && $has_parent) {
            $ancestors = get_post_ancestors($post->ID);
            $hierarchy_depth = count($ancestors);
        }

        return array(
            'detected' => true, // Always recommended
            'confidence' => 1.0,
            'signals' => array(
                'has_parent' => $has_parent,
                'has_categories' => $has_categories,
                'has_terms' => $has_terms,
                'hierarchy_depth' => $hierarchy_depth
            ),
            'type' => 'BreadcrumbList'
        );
    }

    /**
     * Get detected business type for LocalBusiness schema
     *
     * @param array $signals Detection signals
     * @return string Schema.org business type
     */
    public function get_local_business_type($signals) {
        $type_mapping = array(
            'restaurant' => 'Restaurant',
            'cafe' => 'CafeOrCoffeeShop',
            'bar' => 'BarOrPub',
            'hotel' => 'Hotel',
            'store' => 'Store',
            'shop' => 'Store',
            'salon' => 'BeautySalon',
            'spa' => 'DaySpa',
            'gym' => 'ExerciseGym',
            'clinic' => 'MedicalClinic',
            'dental' => 'Dentist',
            'medical' => 'MedicalBusiness',
            'veterinary' => 'VeterinaryCare',
            'pharmacy' => 'Pharmacy',
            'office' => 'ProfessionalService',
            'agency' => 'ProfessionalService',
            'studio' => 'ProfessionalService',
            'law firm' => 'LegalService',
            'accounting' => 'AccountingService',
            'real estate' => 'RealEstateAgent',
            'auto repair' => 'AutoRepair',
            'bank' => 'BankOrCreditUnion',
            'credit union' => 'BankOrCreditUnion'
        );

        foreach ($signals as $signal) {
            if (strpos($signal, 'business_type:') === 0) {
                $type = str_replace('business_type:', '', $signal);
                if (isset($type_mapping[$type])) {
                    return $type_mapping[$type];
                }
            }
        }

        return 'LocalBusiness';
    }

    /**
     * Extract product data from content using patterns
     *
     * @param string $content Post content
     * @return array Extracted product data
     */
    public function extract_product_data($content) {
        $data = array();

        // Extract price
        if (preg_match('/\$(\d+(?:\.\d{2})?)/i', $content, $matches)) {
            $data['price'] = $matches[1];
            $data['currency'] = 'USD';
        }

        // Extract rating
        if (preg_match('/(\d(?:\.\d)?)\s*(?:\/\s*5|out of 5|stars?)/i', $content, $matches)) {
            $data['rating'] = floatval($matches[1]);
        }

        return $data;
    }

    /**
     * Extract video metadata from YouTube ID
     *
     * @param string $video_id YouTube video ID
     * @return array Video metadata
     */
    public function get_youtube_video_data($video_id) {
        return array(
            'embedUrl' => 'https://www.youtube.com/embed/' . $video_id,
            'contentUrl' => 'https://www.youtube.com/watch?v=' . $video_id,
            'thumbnailUrl' => 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg',
            'platform' => 'youtube',
            'videoId' => $video_id
        );
    }

    /**
     * Extract video metadata from Vimeo ID
     *
     * @param string $video_id Vimeo video ID
     * @return array Video metadata
     */
    public function get_vimeo_video_data($video_id) {
        return array(
            'embedUrl' => 'https://player.vimeo.com/video/' . $video_id,
            'contentUrl' => 'https://vimeo.com/' . $video_id,
            'platform' => 'vimeo',
            'videoId' => $video_id
        );
    }
}
