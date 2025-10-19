<?php
/**
 * RequestDesk Schema Generator
 *
 * Generates structured data markup for AEO optimization
 */

class RequestDesk_Schema_Generator {

    private $claude_integration;

    public function __construct() {
        $this->claude_integration = new RequestDesk_Claude_Integration();
    }

    /**
     * Generate FAQ schema markup
     */
    public function generate_faq_schema($post, $qa_pairs = array()) {
        if (empty($qa_pairs)) {
            return array();
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array()
        );

        foreach ($qa_pairs as $qa) {
            // Only include high-confidence Q&A pairs in schema
            if ($qa['confidence'] >= 0.7) {
                $schema['mainEntity'][] = array(
                    '@type' => 'Question',
                    'name' => $qa['question'],
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => $qa['answer']
                    )
                );
            }
        }

        // Only return schema if we have at least 2 questions
        if (count($schema['mainEntity']) >= 2) {
            return $schema;
        }

        return array();
    }

    /**
     * Generate Article schema markup
     */
    public function generate_article_schema($post, $aeo_data = array()) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->post_title,
            'description' => $this->get_post_description($post),
            'url' => get_permalink($post->ID),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
            'author' => $this->get_author_schema($post),
            'publisher' => $this->get_publisher_schema(),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink($post->ID)
            )
        );

        // Add featured image if available
        $featured_image = get_the_post_thumbnail_url($post->ID, 'full');
        if ($featured_image) {
            $schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => $featured_image,
                'width' => 1200,
                'height' => 630
            );
        }

        // Add word count if available
        if (!empty($aeo_data['word_count'])) {
            $schema['wordCount'] = $aeo_data['word_count'];
        }

        // Add reading time estimate
        $word_count = str_word_count(strip_tags($post->post_content));
        $reading_time = max(1, round($word_count / 200)); // Assume 200 words per minute
        $schema['timeRequired'] = 'PT' . $reading_time . 'M';

        return $schema;
    }

    /**
     * Generate HowTo schema markup
     */
    public function generate_howto_schema($post, $steps = array()) {
        if (empty($steps)) {
            $steps = $this->extract_howto_steps($post->post_content);
        }

        if (empty($steps)) {
            return array();
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $post->post_title,
            'description' => $this->get_post_description($post),
            'image' => $this->get_post_images($post),
            'totalTime' => $this->estimate_completion_time($steps),
            'supply' => array(),
            'tool' => array(),
            'step' => array()
        );

        foreach ($steps as $index => $step) {
            $schema['step'][] = array(
                '@type' => 'HowToStep',
                'position' => $index + 1,
                'name' => $step['name'],
                'text' => $step['text'],
                'url' => get_permalink($post->ID) . '#step-' . ($index + 1)
            );
        }

        return $schema;
    }

    /**
     * Generate QAPage schema markup
     */
    public function generate_qanda_schema($post, $qa_pairs = array()) {
        if (empty($qa_pairs)) {
            return array();
        }

        // For single Q&A, use QAPage instead of FAQPage
        if (count($qa_pairs) === 1) {
            $qa = $qa_pairs[0];

            return array(
                '@context' => 'https://schema.org',
                '@type' => 'QAPage',
                'mainEntity' => array(
                    '@type' => 'Question',
                    'name' => $qa['question'],
                    'text' => $qa['question'],
                    'answerCount' => 1,
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => $qa['answer'],
                        'dateCreated' => get_the_date('c', $post->ID),
                        'upvoteCount' => 0,
                        'url' => get_permalink($post->ID)
                    )
                )
            );
        }

        return array();
    }

    /**
     * Generate Organization schema markup
     */
    public function generate_organization_schema() {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url(),
            'description' => get_bloginfo('description')
        );

        // Add logo if available
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
            if ($logo_url) {
                $schema['logo'] = array(
                    '@type' => 'ImageObject',
                    'url' => $logo_url
                );
            }
        }

        // Add social media profiles
        $social_profiles = $this->get_social_profiles();
        if (!empty($social_profiles)) {
            $schema['sameAs'] = $social_profiles;
        }

        return $schema;
    }

    /**
     * Generate WebSite schema markup with search action
     */
    public function generate_website_schema() {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => home_url(),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name')
            )
        );

        // Add search action if search is available
        $search_url = home_url('/?s={search_term_string}');
        $schema['potentialAction'] = array(
            '@type' => 'SearchAction',
            'target' => array(
                '@type' => 'EntryPoint',
                'urlTemplate' => $search_url
            ),
            'query-input' => 'required name=search_term_string'
        );

        return $schema;
    }

    /**
     * Extract HowTo steps from content
     */
    private function extract_howto_steps($content) {
        $steps = array();
        $content = strip_tags($content);

        // Pattern 1: Numbered steps
        $numbered_pattern = '/(?:^|\n)\s*(\d+)\.\s*([^\n]+)(?:\n(.+?))?(?=\n\s*\d+\.|\Z)/m';
        preg_match_all($numbered_pattern, $content, $numbered_matches, PREG_SET_ORDER);

        foreach ($numbered_matches as $match) {
            $steps[] = array(
                'name' => 'Step ' . $match[1],
                'text' => trim($match[2] . ' ' . ($match[3] ?? '')),
                'type' => 'numbered'
            );
        }

        // Pattern 2: Step headings
        $step_pattern = '/(?:^|\n)\s*(?:step\s+\d+:?\s*)?([^\n]+?)(?:\n(.+?))?(?=\n\s*step\s+\d+|\Z)/mi';
        if (stripos($content, 'step') !== false && count($steps) < 3) {
            preg_match_all($step_pattern, $content, $step_matches, PREG_SET_ORDER);

            foreach ($step_matches as $index => $match) {
                if (stripos($match[1], 'step') !== false) {
                    $steps[] = array(
                        'name' => trim($match[1]),
                        'text' => trim($match[2] ?? $match[1]),
                        'type' => 'heading'
                    );
                }
            }
        }

        // Filter out very short or very long steps
        $steps = array_filter($steps, function($step) {
            $text_length = strlen($step['text']);
            return $text_length >= 10 && $text_length <= 500;
        });

        return array_slice($steps, 0, 20); // Limit to 20 steps
    }

    /**
     * Get post description
     */
    private function get_post_description($post) {
        // Try excerpt first
        if (!empty($post->post_excerpt)) {
            return $post->post_excerpt;
        }

        // Generate from content
        $content = strip_tags($post->post_content);
        $description = wp_trim_words($content, 25);

        return $description;
    }

    /**
     * Get author schema
     */
    private function get_author_schema($post) {
        $author_id = $post->post_author;
        $author = get_userdata($author_id);

        $author_schema = array(
            '@type' => 'Person',
            'name' => $author->display_name,
            'url' => get_author_posts_url($author_id)
        );

        // Add author description if available
        $author_description = get_user_meta($author_id, 'description', true);
        if (!empty($author_description)) {
            $author_schema['description'] = $author_description;
        }

        // Add author avatar
        $avatar_url = get_avatar_url($author_id, array('size' => 96));
        if ($avatar_url) {
            $author_schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => $avatar_url,
                'width' => 96,
                'height' => 96
            );
        }

        return $author_schema;
    }

    /**
     * Get publisher schema
     */
    private function get_publisher_schema() {
        $publisher = array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url()
        );

        // Add logo if available
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
            if ($logo_url) {
                $logo_data = wp_get_attachment_metadata($custom_logo_id);
                $publisher['logo'] = array(
                    '@type' => 'ImageObject',
                    'url' => $logo_url,
                    'width' => $logo_data['width'] ?? 600,
                    'height' => $logo_data['height'] ?? 60
                );
            }
        }

        return $publisher;
    }

    /**
     * Get post images
     */
    private function get_post_images($post) {
        $images = array();

        // Featured image
        $featured_image = get_the_post_thumbnail_url($post->ID, 'full');
        if ($featured_image) {
            $images[] = array(
                '@type' => 'ImageObject',
                'url' => $featured_image
            );
        }

        // Images from content
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $content_images);
        foreach ($content_images[1] as $image_url) {
            if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                $images[] = array(
                    '@type' => 'ImageObject',
                    'url' => $image_url
                );
            }
        }

        return array_slice($images, 0, 5); // Limit to 5 images
    }

    /**
     * Estimate completion time for HowTo
     */
    private function estimate_completion_time($steps) {
        $base_time = 5; // Base 5 minutes
        $step_time = count($steps) * 2; // 2 minutes per step

        $total_minutes = $base_time + $step_time;

        // Convert to ISO 8601 duration format
        if ($total_minutes < 60) {
            return 'PT' . $total_minutes . 'M';
        } else {
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            return 'PT' . $hours . 'H' . $minutes . 'M';
        }
    }

    /**
     * Get social media profiles
     */
    private function get_social_profiles() {
        $profiles = array();

        // Common social media meta keys (from themes/plugins)
        $social_keys = array(
            'facebook_url' => 'facebook',
            'twitter_url' => 'twitter',
            'linkedin_url' => 'linkedin',
            'instagram_url' => 'instagram',
            'youtube_url' => 'youtube'
        );

        foreach ($social_keys as $key => $platform) {
            $url = get_option($key) ?: get_theme_mod($key);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $profiles[] = $url;
            }
        }

        return $profiles;
    }

    /**
     * Generate comprehensive schema for a post
     */
    public function generate_comprehensive_schema($post, $aeo_data = array()) {
        $schemas = array();

        // Get Claude AI schema suggestions if available
        if ($this->claude_integration->is_available()) {
            $claude_suggestions = $this->claude_integration->generate_schema_suggestions(
                $post->post_title,
                strip_tags($post->post_content),
                $post->post_type
            );

            if (!is_wp_error($claude_suggestions)) {
                // Use Claude's primary schema suggestion if available
                $primary_schema_type = $claude_suggestions['primary_schema'] ?? 'Article';

                // Store Claude suggestions in AEO data for future reference
                $aeo_data['claude_schema_suggestions'] = $claude_suggestions;
            }
        }

        // Always include Article schema (or Claude's primary suggestion)
        $schemas[] = $this->generate_article_schema($post, $aeo_data);

        // Add FAQ schema if Q&A pairs exist
        if (!empty($aeo_data['ai_questions']) && count($aeo_data['ai_questions']) >= 2) {
            $faq_schema = $this->generate_faq_schema($post, $aeo_data['ai_questions']);
            if (!empty($faq_schema)) {
                $schemas[] = $faq_schema;
            }
        }

        // Add HowTo schema for instructional content (enhanced with Claude suggestions)
        $is_howto_content = (stripos($post->post_title, 'how to') !== false ||
                            stripos($post->post_content, 'step') !== false);

        // Also check Claude's suggestions for HowTo recommendation
        if (isset($aeo_data['claude_schema_suggestions']['additional_schemas']) &&
            is_array($aeo_data['claude_schema_suggestions']['additional_schemas']) &&
            in_array('HowTo', $aeo_data['claude_schema_suggestions']['additional_schemas'])) {
            $is_howto_content = true;
        }

        if ($is_howto_content) {
            $howto_schema = $this->generate_howto_schema($post);
            if (!empty($howto_schema)) {
                $schemas[] = $howto_schema;
            }
        }

        // Add QAPage for single Q&A
        if (!empty($aeo_data['ai_questions']) && count($aeo_data['ai_questions']) === 1) {
            $qa_schema = $this->generate_qanda_schema($post, $aeo_data['ai_questions']);
            if (!empty($qa_schema)) {
                $schemas[] = $qa_schema;
            }
        }

        return $schemas;
    }

    /**
     * Output schema markup as JSON-LD
     */
    public function output_schema_markup($schemas) {
        if (empty($schemas)) {
            return '';
        }

        $output = '';
        foreach ($schemas as $schema) {
            if (!empty($schema)) {
                $output .= '<script type="application/ld+json">';
                $output .= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                $output .= '</script>' . "\n";
            }
        }

        return $output;
    }
}