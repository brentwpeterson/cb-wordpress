# RequestDesk Connector Version History

## v2.3.16 (2025-11-20) 🔧 **AUTO-UPDATE TOGGLE FIX**
- **CRITICAL FIX:** Fixed "Enable auto-updates" toggle button not working
- **Enhancement:** Added proper action handlers for enable/disable auto-update actions
- **Enhancement:** Added success/error notices for auto-update toggle actions
- **Enhancement:** Fixed translation loading issues in auto-update action handlers
- **Status:** Auto-update toggle should now work properly in WordPress admin
- **Location:** `requestdesk-connector-v2.3.16.zip`

## v2.3.15 (2025-11-20) 🌟 **NEW FEATURE: Frontend Q&A Display**
- **NEW FEATURE:** Complete frontend Q&A pairs display system
- **Shortcode:** Added `[requestdesk_qa]` shortcode with customizable options
- **Auto-Display:** Optional automatic Q&A display at end of posts/pages
- **Settings Page:** Full admin control panel for frontend Q&A configuration
- **Template Functions:** `requestdesk_display_qa_pairs()`, `requestdesk_get_qa_pairs()`, `requestdesk_has_qa_pairs()`
- **Responsive Design:** Mobile-friendly, dark theme support, accessibility features
- **SEO Enhancement:** Automatic FAQ schema markup for better search visibility
- **Confidence Filtering:** Display only high-confidence Q&A pairs on frontend
- **Customizable:** Title, max pairs, confidence thresholds all configurable
- **Status:** Ready for testing - enables public display of extracted Q&A pairs
- **Location:** `requestdesk-connector-v2.3.15.zip`

## v2.3.14 (2025-11-20) ✅ **PRODUCTION SUCCESS**
- **CRITICAL FIX:** ✅ **CONFIRMED WORKING** - Eliminated "133 characters of unexpected output" activation error
- **Root Cause Identified:** `get_plugin_data()` in auto-updater constructor triggered early translation loading
- **Solution:** Lazy-loaded plugin version data to prevent early translation loading during activation
- **Technical Fix:** Moved `get_plugin_data()` call out of constructor into `get_plugin_version()` method
- **WordPress 6.7.0+ Compatibility:** ✅ **RESOLVED** - Translation loading now happens after `init` action
- **Production Result:** Plugin activates cleanly without errors
- **Auto-Update Status:** Should now display "Enable auto-updates" toggle correctly
- **Location:** `requestdesk-connector-v2.3.14.zip`

## v2.3.13 (2025-11-20)
- **Fix:** Moved auto-updater from `admin_init` to `wp_loaded` hook
- **Issue:** Still had 133-character error due to constructor calling `get_plugin_data()`
- **Status:** Superseded by v2.3.14

## v2.3.11 (2025-11-20)
- **Fix:** Resolved "133 characters of unexpected output" activation error
- **Fix:** Auto-updater now safely initializes AFTER activation completes
- **Fix:** Added activation completion flag to prevent early auto-updater initialization
- **Status:** Ready for local testing
- **Location:** `requestdesk-connector-v2.3.11.zip`

## v2.3.10 (2025-11-20)
- **Fix:** Translation loading during activation (attempted fix - failed)
- **Issue:** Still had activation errors
- **Status:** Superseded by v2.3.11

## v2.3.9 (2025-11-20)
- **Fix:** WordPress 6.7.0/6.8.3 translation compatibility
- **Feature:** Auto-update system functional
- **Issue:** Had activation errors in production
- **Status:** Working but with activation issues

## Previous Versions
- v2.3.6: Auto-update system added
- v2.3.5: Auto-update attempt (failed)
- v2.3.4: Baseline version (working)

---

## Version Archive Policy
- Keep last 5 versions in `plugin-releases/`
- Archive older versions to `plugin-releases/archive/`
- Always maintain working rollback options