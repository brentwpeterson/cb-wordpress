/**
 * RequestDesk AEO Admin JavaScript
 *
 * Handles admin interface interactions for AEO optimization
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // Main optimize button functionality
        $(document).on('click', '.aeo-optimize-btn', function() {
            const postId = $(this).data('post-id');
            const $container = $('.requestdesk-aeo-overview');
            const $loading = $('.aeo-loading');

            console.log('Optimize button clicked for post:', postId);

            $container.hide();
            $loading.show();

            $.post(ajaxurl, {
                action: 'requestdesk_optimize_content',
                post_id: postId,
                force: true,
                nonce: requestdesk_aeo.nonce
            }, function(response) {
                console.log('Optimize response:', response);
                if (response.success) {
                    location.reload(); // Refresh to show updated data
                } else {
                    alert('Optimization failed: ' + response.data);
                    $loading.hide();
                    $container.show();
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX error:', xhr, status, error);
                alert('Network error occurred. Please try again.');
                $loading.hide();
                $container.show();
            });
        });

        // Analyze button functionality
        $(document).on('click', '.aeo-analyze-btn', function() {
            const postId = $(this).data('post-id');

            console.log('Analyze button clicked for post:', postId);

            $.post(ajaxurl, {
                action: 'requestdesk_analyze_content',
                post_id: postId,
                nonce: requestdesk_aeo.nonce
            }, function(response) {
                console.log('Analysis response:', response);
                if (response.success) {
                    console.log('Analysis results:', response.data);
                    alert('Analysis complete. Check browser console for details.');
                } else {
                    alert('Analysis failed: ' + response.data);
                }
            }).fail(function(xhr, status, error) {
                console.error('Analysis AJAX error:', xhr, status, error);
                alert('Network error occurred during analysis. Please try again.');
            });
        });

        // Q&A Pairs generation/regeneration
        $(document).on('click', '.qa-regenerate-btn, .qa-generate-btn', function(e) {
            e.preventDefault();
            const postId = $(this).data('post-id');

            if ($(this).hasClass('qa-regenerate-btn')) {
                if (!confirm('This will replace existing Q&A pairs. Continue?')) {
                    return;
                }
            }

            $.post(ajaxurl, {
                action: 'requestdesk_optimize_content',
                post_id: postId,
                force: true,
                nonce: requestdesk_aeo.nonce
            }, function(response) {
                console.log('Q&A Generation response:', response);
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to generate Q&A pairs: ' + response.data);
                }
            }).fail(function(xhr, status, error) {
                console.error('Q&A Generation AJAX error:', xhr, status, error);
                alert('Network error occurred during Q&A generation. Please try again.');
            });
        });

        // Citation refresh functionality
        $(document).on('click', '.citations-refresh-btn, .citations-scan-btn', function() {
            const postId = $(this).data('post-id');

            console.log('Citations refresh button clicked for post:', postId);

            $.post(ajaxurl, {
                action: 'requestdesk_update_citation_stats',
                post_id: postId,
                nonce: requestdesk_aeo.nonce
            }, function(response) {
                console.log('Citations refresh response:', response);
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to update citation statistics: ' + response.data);
                }
            }).fail(function(xhr, status, error) {
                console.error('Citations refresh AJAX error:', xhr, status, error);
                alert('Network error occurred during citations refresh. Please try again.');
            });
        });

        // Verify AEO settings are available
        if (!window.requestdesk_aeo) {
            console.error('RequestDesk AEO: Configuration not found');
            return;
        }
    });

})(jQuery);