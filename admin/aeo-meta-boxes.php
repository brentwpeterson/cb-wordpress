<?php
/**
 * RequestDesk AEO Meta Boxes
 */

/**
 * Add AEO meta boxes to post/page editors
 */
function requestdesk_aeo_add_meta_boxes() {
    $settings = get_option('requestdesk_aeo_settings', array());

    // Only add meta boxes if AEO is enabled
    if (!($settings['enabled'] ?? true)) {
        return;
    }

    add_meta_box(
        'requestdesk-aeo-optimization',
        'AEO Optimization',
        'requestdesk_aeo_optimization_meta_box',
        array('post', 'page'),
        'side',
        'default'
    );

    add_meta_box(
        'requestdesk-aeo-qa-pairs',
        'Q&A Pairs',
        'requestdesk_aeo_qa_pairs_meta_box',
        array('post', 'page'),
        'normal',
        'default'
    );

    add_meta_box(
        'requestdesk-aeo-citations',
        'Citation Statistics',
        'requestdesk_aeo_citations_meta_box',
        array('post', 'page'),
        'normal',
        'default'
    );
}

/**
 * Main AEO optimization meta box
 */
function requestdesk_aeo_optimization_meta_box($post) {
    $aeo_core = new RequestDesk_AEO_Core();
    $aeo_data = $aeo_core->get_aeo_data($post->ID);

    $aeo_score = get_post_meta($post->ID, '_requestdesk_aeo_score', true);
    $freshness_score = get_post_meta($post->ID, '_requestdesk_freshness_score', true);
    $freshness_status = get_post_meta($post->ID, '_requestdesk_freshness_status', true);
    $last_update = get_post_meta($post->ID, '_requestdesk_aeo_last_update', true);

    wp_nonce_field('requestdesk_aeo_meta_box', 'requestdesk_aeo_meta_box_nonce');
    ?>

    <div class="requestdesk-aeo-overview">
        <!-- AEO Score Display -->
        <div class="aeo-score-section" style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0;">AEO Optimization Score</h4>
            <?php if ($aeo_score): ?>
                <div class="aeo-score-display" style="text-align: center; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                    <div class="score-circle" style="display: inline-block; width: 60px; height: 60px; border-radius: 50%; background: <?php echo requestdesk_get_score_color($aeo_score); ?>; color: white; line-height: 60px; font-size: 18px; font-weight: bold;">
                        <?php echo $aeo_score; ?>%
                    </div>
                    <div style="margin-top: 10px;">
                        <strong><?php echo requestdesk_get_score_label($aeo_score); ?></strong>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 15px; background: #f9f9f9; border-radius: 8px; color: #666;">
                    Not yet analyzed
                </div>
            <?php endif; ?>
        </div>

        <!-- Freshness Score -->
        <div class="freshness-section" style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0;">Content Freshness</h4>
            <?php if ($freshness_score): ?>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="flex: 1; background: #f0f0f0; border-radius: 10px; height: 8px;">
                        <div style="width: <?php echo $freshness_score; ?>%; height: 100%; background: <?php echo requestdesk_get_score_color($freshness_score); ?>; border-radius: 10px;"></div>
                    </div>
                    <div style="font-weight: bold; color: <?php echo requestdesk_get_score_color($freshness_score); ?>;">
                        <?php echo $freshness_score; ?>%
                    </div>
                </div>
                <small style="color: #666;"><?php echo ucfirst($freshness_status ?: 'unknown'); ?> freshness</small>
            <?php else: ?>
                <div style="color: #666;">Not yet analyzed</div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="aeo-actions" style="margin-bottom: 20px;">
            <button type="button" class="button button-primary aeo-optimize-btn" data-post-id="<?php echo $post->ID; ?>">
                <?php echo $aeo_score ? 'Re-optimize Content' : 'Optimize Content'; ?>
            </button>
            <button type="button" class="button aeo-analyze-btn" data-post-id="<?php echo $post->ID; ?>">
                Analyze Only
            </button>
        </div>

        <!-- Status and Last Update -->
        <?php if ($last_update): ?>
        <div class="aeo-status" style="font-size: 12px; color: #666; border-top: 1px solid #eee; padding-top: 15px;">
            <strong>Last updated:</strong> <?php echo human_time_diff($last_update, current_time('timestamp')); ?> ago<br>
            <strong>Status:</strong> <?php echo ucfirst($aeo_data['optimization_status'] ?? 'pending'); ?>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <?php if (!empty($aeo_data)): ?>
        <div class="aeo-quick-stats" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                <div>
                    <strong>Q&A Pairs:</strong><br>
                    <?php echo count($aeo_data['ai_questions'] ?? array()); ?>
                </div>
                <div>
                    <strong>Statistics:</strong><br>
                    <?php echo count($aeo_data['citation_stats'] ?? array()); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="aeo-loading" style="display: none; text-align: center; padding: 20px;">
        <div class="spinner is-active"></div>
        <p>Processing...</p>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.aeo-optimize-btn').on('click', function() {
            const postId = $(this).data('post-id');
            const $container = $('.requestdesk-aeo-overview');
            const $loading = $('.aeo-loading');

            $container.hide();
            $loading.show();

            $.post(ajaxurl, {
                action: 'requestdesk_optimize_content',
                post_id: postId,
                force: true,
                nonce: requestdesk_aeo.nonce
            }, function(response) {
                if (response.success) {
                    location.reload(); // Refresh to show updated data
                } else {
                    alert('Optimization failed: ' + response.data);
                    $loading.hide();
                    $container.show();
                }
            });
        });

        $('.aeo-analyze-btn').on('click', function() {
            const postId = $(this).data('post-id');

            $.post(ajaxurl, {
                action: 'requestdesk_analyze_content',
                post_id: postId,
                nonce: requestdesk_aeo.nonce
            }, function(response) {
                if (response.success) {
                    console.log('Analysis results:', response.data);
                    // You could show analysis results in a modal or update UI
                    alert('Analysis complete. Check browser console for details.');
                } else {
                    alert('Analysis failed: ' + response.data);
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * Q&A Pairs meta box
 */
function requestdesk_aeo_qa_pairs_meta_box($post) {
    $aeo_core = new RequestDesk_AEO_Core();
    $aeo_data = $aeo_core->get_aeo_data($post->ID);
    $qa_pairs = $aeo_data['ai_questions'] ?? array();
    ?>

    <div class="requestdesk-qa-pairs">
        <?php if (!empty($qa_pairs)): ?>
            <div class="qa-pairs-list">
                <?php foreach ($qa_pairs as $index => $qa): ?>
                <div class="qa-pair" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                    <div class="qa-question" style="margin-bottom: 10px;">
                        <strong style="color: #0073aa;">Q: <?php echo esc_html($qa['question']); ?></strong>
                        <span class="qa-confidence" style="float: right; font-size: 11px; background: <?php echo requestdesk_get_confidence_color($qa['confidence']); ?>; color: white; padding: 2px 6px; border-radius: 3px;">
                            <?php echo round($qa['confidence'] * 100); ?>% confidence
                        </span>
                    </div>
                    <div class="qa-answer" style="color: #333; line-height: 1.4;">
                        <strong>A:</strong> <?php echo esc_html(wp_trim_words($qa['answer'], 25)); ?>
                    </div>
                    <div class="qa-meta" style="margin-top: 8px; font-size: 11px; color: #666;">
                        Type: <?php echo ucfirst($qa['type'] ?? 'unknown'); ?>
                        <button type="button" class="button-link qa-edit-btn" data-index="<?php echo $index; ?>" style="margin-left: 10px;">Edit</button>
                        <button type="button" class="button-link qa-remove-btn" data-index="<?php echo $index; ?>" style="margin-left: 5px; color: #d63638;">Remove</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="qa-actions" style="margin-top: 20px;">
                <button type="button" class="button qa-add-btn">Add Manual Q&A Pair</button>
                <button type="button" class="button qa-regenerate-btn" data-post-id="<?php echo $post->ID; ?>">Regenerate Q&A Pairs</button>
            </div>
        <?php else: ?>
            <div class="no-qa-pairs" style="text-align: center; padding: 40px 20px; color: #666;">
                <p>No Q&A pairs found.</p>
                <button type="button" class="button button-primary qa-generate-btn" data-post-id="<?php echo $post->ID; ?>">
                    Generate Q&A Pairs
                </button>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Q&A Modal -->
        <div class="qa-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 100000;">
            <div class="qa-modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px;">
                <h3>Add/Edit Q&A Pair</h3>
                <div style="margin-bottom: 15px;">
                    <label><strong>Question:</strong></label>
                    <input type="text" class="qa-question-input" style="width: 100%; margin-top: 5px;" placeholder="Enter your question...">
                </div>
                <div style="margin-bottom: 20px;">
                    <label><strong>Answer:</strong></label>
                    <textarea class="qa-answer-input" rows="4" style="width: 100%; margin-top: 5px;" placeholder="Enter the answer..."></textarea>
                </div>
                <div style="text-align: right;">
                    <button type="button" class="button qa-modal-cancel">Cancel</button>
                    <button type="button" class="button button-primary qa-modal-save" style="margin-left: 10px;">Save</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="requestdesk_qa_pairs" value="<?php echo esc_attr(json_encode($qa_pairs)); ?>">

    <script>
    jQuery(document).ready(function($) {
        let currentQAIndex = -1;

        // Add new Q&A pair
        $('.qa-add-btn').on('click', function() {
            currentQAIndex = -1;
            $('.qa-question-input').val('');
            $('.qa-answer-input').val('');
            $('.qa-modal').show();
        });

        // Edit Q&A pair
        $('.qa-edit-btn').on('click', function() {
            currentQAIndex = $(this).data('index');
            const qaPairs = JSON.parse($('input[name="requestdesk_qa_pairs"]').val());
            const qa = qaPairs[currentQAIndex];

            $('.qa-question-input').val(qa.question);
            $('.qa-answer-input').val(qa.answer);
            $('.qa-modal').show();
        });

        // Remove Q&A pair
        $('.qa-remove-btn').on('click', function() {
            if (confirm('Remove this Q&A pair?')) {
                const index = $(this).data('index');
                let qaPairs = JSON.parse($('input[name="requestdesk_qa_pairs"]').val());
                qaPairs.splice(index, 1);
                $('input[name="requestdesk_qa_pairs"]').val(JSON.stringify(qaPairs));
                location.reload();
            }
        });

        // Modal actions
        $('.qa-modal-cancel').on('click', function() {
            $('.qa-modal').hide();
        });

        $('.qa-modal-save').on('click', function() {
            const question = $('.qa-question-input').val().trim();
            const answer = $('.qa-answer-input').val().trim();

            if (!question || !answer) {
                alert('Please enter both question and answer.');
                return;
            }

            let qaPairs = JSON.parse($('input[name="requestdesk_qa_pairs"]').val() || '[]');

            if (currentQAIndex >= 0) {
                // Edit existing
                qaPairs[currentQAIndex].question = question;
                qaPairs[currentQAIndex].answer = answer;
            } else {
                // Add new
                qaPairs.push({
                    question: question,
                    answer: answer,
                    type: 'manual',
                    confidence: 1.0
                });
            }

            $('input[name="requestdesk_qa_pairs"]').val(JSON.stringify(qaPairs));
            $('.qa-modal').hide();
            location.reload();
        });

        // Regenerate Q&A pairs
        $('.qa-regenerate-btn, .qa-generate-btn').on('click', function() {
            const postId = $(this).data('post-id');

            if (confirm('This will replace existing Q&A pairs. Continue?')) {
                $.post(ajaxurl, {
                    action: 'requestdesk_optimize_content',
                    post_id: postId,
                    force: true,
                    nonce: requestdesk_aeo.nonce
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to regenerate Q&A pairs: ' + response.data);
                    }
                });
            }
        });
    });
    </script>
    <?php
}

/**
 * Citation Statistics meta box
 */
function requestdesk_aeo_citations_meta_box($post) {
    $citation_tracker = new RequestDesk_Citation_Tracker();
    $citation_stats = $citation_tracker->get_citation_stats($post->ID);
    ?>

    <div class="requestdesk-citations">
        <?php if (!empty($citation_stats)): ?>
            <div class="citations-list">
                <?php foreach (array_slice($citation_stats, 0, 10) as $stat): ?>
                <?php $formatted = $citation_tracker->format_statistic_for_display($stat); ?>
                <div class="citation-item" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 6px; border-left: 4px solid <?php echo requestdesk_get_citation_color($stat['citation_quality']); ?>;">
                    <div class="citation-value" style="font-weight: bold; color: #0073aa; margin-bottom: 5px;">
                        <?php echo esc_html($formatted['display_value']); ?>
                        <span class="citation-badge" style="float: right; font-size: 10px; background: <?php echo requestdesk_get_badge_color($formatted['quality_badge']['class']); ?>; color: white; padding: 2px 6px; border-radius: 3px;">
                            <?php echo $formatted['quality_badge']['label']; ?>
                        </span>
                    </div>
                    <div class="citation-context" style="font-size: 13px; color: #666; line-height: 1.3;">
                        <?php echo esc_html($formatted['context']); ?>
                    </div>
                    <div class="citation-meta" style="margin-top: 8px; font-size: 11px; color: #999;">
                        <?php echo $formatted['type_label']; ?>
                        <?php if (!empty($formatted['freshness'])): ?>
                        | <span style="color: <?php echo requestdesk_get_badge_color($formatted['freshness']['class']); ?>;">
                            <?php echo $formatted['freshness']['label']; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($citation_stats) > 10): ?>
            <div class="citations-summary" style="margin-top: 15px; padding: 10px; background: #e8f4fd; border-radius: 6px; text-align: center;">
                <small>Showing top 10 of <?php echo count($citation_stats); ?> citation statistics found.</small>
            </div>
            <?php endif; ?>

            <div class="citations-actions" style="margin-top: 20px;">
                <button type="button" class="button citations-refresh-btn" data-post-id="<?php echo $post->ID; ?>">
                    Refresh Citations
                </button>
                <button type="button" class="button citations-export-btn" data-post-id="<?php echo $post->ID; ?>">
                    Export Statistics
                </button>
            </div>
        <?php else: ?>
            <div class="no-citations" style="text-align: center; padding: 40px 20px; color: #666;">
                <p>No citation statistics found.</p>
                <button type="button" class="button button-primary citations-scan-btn" data-post-id="<?php echo $post->ID; ?>">
                    Scan for Statistics
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.citations-refresh-btn, .citations-scan-btn').on('click', function() {
            const postId = $(this).data('post-id');

            $.post(ajaxurl, {
                action: 'requestdesk_update_citation_stats',
                post_id: postId,
                nonce: requestdesk_aeo.nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to update citation statistics: ' + response.data);
                }
            });
        });

        $('.citations-export-btn').on('click', function() {
            const postId = $(this).data('post-id');
            // This could open a modal or trigger a download
            alert('Export functionality coming soon!');
        });
    });
    </script>
    <?php
}

/**
 * Save AEO meta box data
 */
add_action('save_post', 'requestdesk_save_aeo_meta_box_data');
function requestdesk_save_aeo_meta_box_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['requestdesk_aeo_meta_box_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['requestdesk_aeo_meta_box_nonce'], 'requestdesk_aeo_meta_box')) {
        return;
    }

    // Check if user has permission to edit post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save manual Q&A pairs if provided
    if (isset($_POST['requestdesk_qa_pairs'])) {
        $qa_pairs = json_decode(stripslashes($_POST['requestdesk_qa_pairs']), true);
        if (is_array($qa_pairs)) {
            // Update the AEO data with manual Q&A pairs
            $aeo_core = new RequestDesk_AEO_Core();
            $aeo_data = $aeo_core->get_aeo_data($post_id);

            $aeo_core->update_aeo_data($post_id, array(
                'ai_questions' => json_encode($qa_pairs)
            ));

            // Also update post meta for quick access
            update_post_meta($post_id, '_requestdesk_manual_qa_pairs', $qa_pairs);
        }
    }
}

/**
 * Helper functions for styling
 */
function requestdesk_get_score_color($score) {
    if ($score >= 80) return '#46b450';
    if ($score >= 60) return '#00a32a';
    if ($score >= 40) return '#ffb900';
    if ($score >= 20) return '#f56e28';
    return '#d63638';
}

function requestdesk_get_score_label($score) {
    if ($score >= 80) return 'Excellent';
    if ($score >= 60) return 'Good';
    if ($score >= 40) return 'Fair';
    if ($score >= 20) return 'Poor';
    return 'Needs Work';
}

function requestdesk_get_confidence_color($confidence) {
    if ($confidence >= 0.8) return '#46b450';
    if ($confidence >= 0.6) return '#ffb900';
    return '#f56e28';
}

function requestdesk_get_citation_color($quality) {
    if ($quality >= 80) return '#46b450';
    if ($quality >= 60) return '#00a32a';
    if ($quality >= 40) return '#ffb900';
    return '#f56e28';
}

function requestdesk_get_badge_color($class) {
    $colors = array(
        'success' => '#46b450',
        'warning' => '#ffb900',
        'info' => '#0073aa',
        'danger' => '#d63638',
        'secondary' => '#666'
    );
    return $colors[$class] ?? '#666';
}