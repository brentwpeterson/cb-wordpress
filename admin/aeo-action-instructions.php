<?php
/**
 * AEO Template Action Instructions
 * Enhanced guidance for template customization with specific instructions
 */

function requestdesk_get_action_instructions() {
    return array(
        'meta_description' => array(
            'title' => '📝 Meta Description Optimization',
            'instruction' => 'Replace this meta description with your business-specific version',
            'why' => 'Meta descriptions directly impact click-through rates from search results and are often used by AI engines when generating summaries',
            'action' => 'Write 150-160 characters that include your main keyword, value proposition, and a call-to-action',
            'example' => '"[Your Company] delivers [primary service] for [target audience]. [Key benefit/stat]. [Call-to-action]."'
        ),
        'hero_section' => array(
            'title' => '🎯 Hero Section Optimization',
            'instruction' => 'Update headline, subheadline, and call-to-action button',
            'why' => 'The hero section is the first thing users see and directly impacts bounce rate and conversions',
            'action' => 'Replace with your unique value proposition, target keywords, and clear CTA',
            'example' => 'Headline: Include primary keyword + benefit. Subheadline: Expand on who you serve. CTA: Use action verbs like "Get Started" or "Schedule Free Consultation"'
        ),
        'services_section' => array(
            'title' => '🛠️ Services Section Optimization',
            'instruction' => 'Replace each service with your actual offerings',
            'why' => 'Service descriptions help search engines understand what you do and improve topical authority',
            'action' => 'Use keyword-rich titles and benefit-focused descriptions for each service',
            'example' => 'Service Title: Include target keywords. Description: Focus on client benefits, not just features'
        ),
        'testimonials_section' => array(
            'title' => '⭐ Testimonials Section Optimization',
            'instruction' => 'Replace with real client testimonials and photos',
            'why' => 'Social proof builds trust and credibility, important ranking factors for E-E-A-T',
            'action' => 'Use specific client names, companies, and measurable results in testimonials',
            'example' => 'Include client\'s full name, company, specific results achieved, and if possible, their photo'
        ),
        'faq_section' => array(
            'title' => '❓ FAQ Section Optimization',
            'instruction' => 'Replace with questions your actual customers ask',
            'why' => 'FAQ content directly targets voice search queries and answer engine optimization',
            'action' => 'Use real customer questions and provide comprehensive, keyword-rich answers',
            'example' => 'Questions should start with "How", "What", "Why", "When" to match search patterns'
        ),
        'schema_markup' => array(
            'title' => '🏗️ Schema Markup Customization',
            'instruction' => 'Update all [CUSTOMIZE] tags in the JSON-LD schema',
            'why' => 'Schema markup helps search engines understand your content and can trigger rich snippets',
            'action' => 'Replace placeholder text with your actual business information, keeping the JSON structure intact',
            'example' => 'Business name, address, phone, services, team members, awards, etc.'
        ),
        'company_story' => array(
            'title' => '📖 Company Story Optimization',
            'instruction' => 'Replace with your authentic company founding story and mission',
            'why' => 'Authentic storytelling builds trust and helps with brand entity recognition in search engines',
            'action' => 'Share the problem you solve, why you started, and what makes you different',
            'example' => 'Start with a relatable problem, explain your solution approach, highlight unique methodology'
        ),
        'team_profiles' => array(
            'title' => '👥 Team Profiles Optimization',
            'instruction' => 'Replace with real team members and their credentials',
            'why' => 'Team expertise builds E-E-A-T signals and helps establish topical authority',
            'action' => 'Include relevant certifications, years of experience, and specific achievements',
            'example' => 'Focus on credentials that relate to your services: "Google Ads Certified", "10+ years SEO experience"'
        ),
        'achievement_stats' => array(
            'title' => '🏆 Achievement Statistics Optimization',
            'instruction' => 'Replace with your actual business metrics and achievements',
            'why' => 'Specific numbers and achievements build credibility and social proof',
            'action' => 'Use your real metrics: projects completed, clients served, years in business, client satisfaction rate',
            'example' => 'Be specific: "500+ Projects" instead of "Many Projects", "4.9/5 Rating" instead of "Great Reviews"'
        ),
        'internal_linking' => array(
            'title' => '🔗 Internal Linking Optimization',
            'instruction' => 'Update all placeholder links to point to your actual pages',
            'why' => 'Internal linking helps search engines understand your site structure and distributes page authority',
            'action' => 'Link to your actual service pages, about page, contact form, and relevant blog posts',
            'example' => 'Replace "#services" with "/our-services/", "#contact" with "/contact-us/"'
        ),
        'local_seo' => array(
            'title' => '📍 Local SEO Optimization',
            'instruction' => 'Add your business location information if you serve local markets',
            'why' => 'Local signals help with geographic search results and Google Business Profile optimization',
            'action' => 'Include city, state, service areas in natural language within content',
            'example' => '"Serving businesses in [City, State]" or "Based in [Location] with clients nationwide"'
        )
    );
}

function requestdesk_get_action_instruction_block($instruction_key, $section_title = '') {
    $instructions = requestdesk_get_action_instructions();

    if (!isset($instructions[$instruction_key])) {
        return '';
    }

    $instruction = $instructions[$instruction_key];
    $title = !empty($section_title) ? $section_title : $instruction['title'];

    return <<<EOD
<!-- wp:group {"style":{"color":{"background":"#f8f9fa"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"20px","right":"20px"},"margin":{"top":"30px","bottom":"30px"}},"border":{"radius":"12px","color":"#e9ecef","width":"2px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background has-border-color" style="border-color:#e9ecef;border-width:2px;border-radius:12px;background-color:#f8f9fa;margin-top:30px;margin-bottom:30px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:heading {"level":4,"style":{"typography":{"fontSize":"18px","fontWeight":"600"},"color":{"text":"#495057"}}} -->
<h4 class="wp-block-heading has-text-color" style="color:#495057;font-size:18px;font-weight:600">{$title}</h4>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"15px"},"color":{"text":"#6c757d"},"spacing":{"margin":{"top":"10px"}}}} -->
<p class="has-text-color" style="color:#6c757d;margin-top:10px;font-size:15px"><strong>📋 What to do:</strong> {$instruction['instruction']}</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"15px"},"color":{"text":"#6c757d"},"spacing":{"margin":{"top":"8px"}}}} -->
<p class="has-text-color" style="color:#6c757d;margin-top:8px;font-size:15px"><strong>🎯 Why it matters:</strong> {$instruction['why']}</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"15px"},"color":{"text":"#6c757d"},"spacing":{"margin":{"top":"8px"}}}} -->
<p class="has-text-color" style="color:#6c757d;margin-top:8px;font-size:15px"><strong>✅ Action steps:</strong> {$instruction['action']}</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontStyle":"italic"},"color":{"text":"#868e96"},"spacing":{"margin":{"top":"8px"}}}} -->
<p class="has-text-color" style="color:#868e96;margin-top:8px;font-size:14px;font-style:italic"><strong>💡 Example:</strong> {$instruction['example']}</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

EOD;
}

function requestdesk_get_customization_reminder_block() {
    return <<<'EOD'
<!-- wp:group {"style":{"color":{"background":"#fff3cd"},"spacing":{"padding":{"top":"25px","bottom":"25px","left":"25px","right":"25px"},"margin":{"top":"40px","bottom":"40px"}},"border":{"radius":"12px","color":"#ffeaa7","width":"2px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background has-border-color" style="border-color:#ffeaa7;border-width:2px;border-radius:12px;background-color:#fff3cd;margin-top:40px;margin-bottom:40px;padding-top:25px;padding-right:25px;padding-bottom:25px;padding-left:25px"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"600"},"color":{"text":"#856404"}}} -->
<h3 class="wp-block-heading has-text-color" style="color:#856404;font-size:20px;font-weight:600">🎯 Customization Checkpoint</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px"},"color":{"text":"#856404"},"spacing":{"margin":{"top":"15px"}}}} -->
<p class="has-text-color" style="color:#856404;margin-top:15px;font-size:16px"><strong>Before you publish:</strong> Make sure you've replaced all <mark>[CUSTOMIZE]</mark> tags with your actual business information. This template is optimized for AEO/GEO, but personalization is key to ranking success.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"style":{"typography":{"fontSize":"15px"},"color":{"text":"#856404"},"spacing":{"margin":{"top":"15px"}}}} -->
<ul class="has-text-color" style="color:#856404;margin-top:15px;font-size:15px"><li>✅ Company name and branding</li><li>✅ Contact information and links</li><li>✅ Service descriptions and offerings</li><li>✅ Team member profiles and photos</li><li>✅ Client testimonials and case studies</li><li>✅ Schema markup business details</li><li>✅ Meta description and SEO elements</li></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

EOD;
}