<?php
/**
 * RequestDesk Claude AI Integration
 *
 * Handles all Claude AI API interactions for AEO features
 */

class RequestDesk_Claude_Integration {

    private $api_key;
    private $api_url = 'https://api.anthropic.com/v1/messages';
    private $model = 'claude-3-5-sonnet-20241022';

    public function __construct() {
        $settings = get_option('requestdesk_settings', array());
        $this->api_key = $settings['claude_api_key'] ?? '';
    }

    /**
     * Check if Claude integration is available
     */
    public function is_available() {
        return !empty($this->api_key);
    }

    /**
     * Make a request to Claude API
     */
    private function make_request($prompt, $max_tokens = 4096) {
        if (!$this->is_available()) {
            return new WP_Error('no_api_key', 'Claude API key not configured');
        }

        $headers = array(
            'Content-Type' => 'application/json',
            'x-api-key' => $this->api_key,
            'anthropic-version' => '2023-06-01'
        );

        $body = array(
            'model' => $this->model,
            'max_tokens' => $max_tokens,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            )
        );

        $response = wp_remote_post($this->api_url, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('api_error', $data['error']['message'] ?? 'Claude API error');
        }

        return $data['content'][0]['text'] ?? '';
    }

    /**
     * Analyze content quality and structure for AEO
     */
    public function analyze_content($title, $content) {
        $prompt = "Analyze this WordPress post for Answer Engine Optimization (AEO). Provide a detailed analysis in JSON format.

Title: {$title}

Content: {$content}

Please analyze and return JSON with these fields:
- aeo_score: Overall AEO readiness score (0-100)
- readability_score: Content readability score (0-100)
- structure_score: Content structure score (0-100)
- keyword_density: Object with primary keywords and their density
- improvements: Array of specific improvement suggestions
- questions_answered: Array of questions this content answers
- missing_elements: Array of missing AEO elements
- schema_suggestions: Array of recommended schema types

Focus on how well this content would perform in AI search engines and answer engines.";

        $result = $this->make_request($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        // Try to extract JSON from the response
        if (preg_match('/\{.*\}/s', $result, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        return new WP_Error('parse_error', 'Could not parse Claude response');
    }

    /**
     * Extract Q&A pairs from content
     */
    public function extract_qa_pairs($title, $content) {
        $prompt = "Extract question and answer pairs from this WordPress post content. Return as JSON array.

Title: {$title}

Content: {$content}

Extract Q&A pairs that:
1. Are directly answered by the content
2. Would be useful for FAQ schema markup
3. Are relevant for voice search and AI assistants
4. Cover the main topics and subtopics

Return JSON array with objects containing:
- question: The question being answered
- answer: Concise answer (1-2 sentences max)
- confidence: Confidence score (0-100) that this Q&A is accurate
- type: Type of question (factual, how-to, definition, comparison, etc.)
- keywords: Array of relevant keywords for this Q&A

Limit to the 10 most important Q&A pairs.";

        $result = $this->make_request($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        // Try to extract JSON from the response
        if (preg_match('/\[.*\]/s', $result, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        return new WP_Error('parse_error', 'Could not parse Claude Q&A response');
    }

    /**
     * Generate content optimization suggestions
     */
    public function get_optimization_suggestions($title, $content) {
        $prompt = "Provide specific optimization suggestions for this WordPress post to improve its performance in AI search engines and answer engines.

Title: {$title}

Content: {$content}

Analyze and provide JSON with:
- title_suggestions: Array of improved title variations
- heading_improvements: Array of better H2/H3 heading suggestions
- content_gaps: Array of missing information that should be added
- semantic_improvements: Suggestions for better semantic structure
- answer_engine_optimization: Specific tips for AI search engines
- featured_snippet_potential: How to optimize for featured snippets
- voice_search_optimization: Tips for voice search optimization

Focus on actionable, specific improvements rather than general advice.";

        $result = $this->make_request($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        // Try to extract JSON from the response
        if (preg_match('/\{.*\}/s', $result, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        return new WP_Error('parse_error', 'Could not parse Claude optimization response');
    }

    /**
     * Generate schema markup suggestions
     */
    public function generate_schema_suggestions($title, $content, $post_type = 'article') {
        $prompt = "Generate schema markup suggestions for this WordPress post. Return as JSON.

Title: {$title}
Content: {$content}
Post Type: {$post_type}

Analyze the content and suggest appropriate schema.org markup. Return JSON with:
- primary_schema: Main schema type (Article, HowTo, FAQ, etc.)
- schema_data: Structured data object for the primary schema
- additional_schemas: Array of other applicable schema types
- faq_schema: FAQ schema if Q&A content is present
- breadcrumb_suggestions: Suggested breadcrumb structure
- organization_markup: Any organization-related markup needed

Focus on schema that will help with rich snippets and AI understanding.";

        $result = $this->make_request($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        // Try to extract JSON from the response
        if (preg_match('/\{.*\}/s', $result, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        return new WP_Error('parse_error', 'Could not parse Claude schema response');
    }

    /**
     * Assess content freshness and update recommendations
     */
    public function assess_content_freshness($title, $content, $post_date, $last_modified) {
        $post_age = (time() - strtotime($post_date)) / (60 * 60 * 24); // Days
        $last_update_age = (time() - strtotime($last_modified)) / (60 * 60 * 24); // Days

        $prompt = "Assess the freshness and update needs for this WordPress post content.

Title: {$title}
Content: {$content}
Post Age: {$post_age} days
Last Updated: {$last_update_age} days ago

Analyze and return JSON with:
- freshness_score: Content freshness score (0-100)
- update_priority: Priority level (low, medium, high, urgent)
- outdated_elements: Array of specific outdated information found
- update_suggestions: Array of specific updates needed
- evergreen_score: How evergreen/timeless this content is (0-100)
- trending_opportunities: Current trends this content could capitalize on
- competitive_gaps: Areas where content could be strengthened vs competitors

Focus on actionable insights for content updates and improvements.";

        $result = $this->make_request($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        // Try to extract JSON from the response
        if (preg_match('/\{.*\}/s', $result, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        return new WP_Error('parse_error', 'Could not parse Claude freshness response');
    }

    /**
     * Test Claude API connection
     */
    public function test_connection() {
        $result = $this->make_request("Test connection. Please respond with 'Claude API connection successful'", 100);

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'status' => 'success',
            'response' => $result,
            'model' => $this->model
        );
    }
}