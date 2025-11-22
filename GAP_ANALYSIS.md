# Gap Analysis: Respira for WordPress Lite Implementation

## Missing from PROMPT-01 Specification

### 1. Main Plugin File (`respira-for-wordpress-lite.php`)

**Missing:**
- `declare(strict_types=1);` at top of file
- `define( 'RESPIRA_LITE_PLUGIN_FILE', __FILE__ );` constant
- `define( 'RESPIRA_LITE_MAX_API_KEYS', 3 );` constant
- `define( 'RESPIRA_LITE', true );` boolean constant
- Settings link filter function:
  ```php
  function respira_lite_settings_link( array $links ): array {
      $settings_link = sprintf(
          '<a href="%s">%s</a>',
          admin_url( 'admin.php?page=respira-lite' ),
          esc_html__( 'Settings', 'respira-for-wordpress-lite' )
      );
      array_unshift( $links, $settings_link );

      $upgrade_link = sprintf(
          '<a href="%s" target="_blank" style="color: #2271b1; font-weight: bold;">%s</a>',
          'https://respira.press?utm_source=lite&utm_medium=plugins_page&utm_campaign=upgrade',
          esc_html__( 'Upgrade to Full Version', 'respira-for-wordpress-lite' )
      );
      $links[] = $upgrade_link;

      return $links;
  }
  add_filter( 'plugin_action_links_' . RESPIRA_LITE_PLUGIN_BASENAME, 'respira_lite_settings_link' );
  ```

### 2. Usage Limiter Class

**Current:** Uses different method names (`get_usage()`)
**Spec wants:** Separate `get_edit_data()` and `get_usage_stats()` methods with internal data format using `c`, `r`, `v` keys

**Status:** ⚠️ Functionally equivalent but different API - ACCEPTABLE

### 3. API Class (`class-respira-lite-api.php`)

**Missing:**
- `/usage` endpoint:
  ```php
  register_rest_route(
      'respira-lite/v1',
      '/usage',
      array(
          'methods'             => 'GET',
          'callback'            => array( $this, 'get_usage' ),
          'permission_callback' => array( $this, 'check_api_key' ),
      )
  );
  ```
- `get_usage()` callback method for the endpoint

### 4. Auth Class (`class-respira-lite-auth.php`)

**Missing:**
- 3 API key limit enforcement in `generate_api_key()` method:
  ```php
  // Check existing key count
  $existing_count = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND is_active = 1",
          $user_id
      )
  );

  if ( $existing_count >= RESPIRA_LITE_MAX_API_KEYS ) {
      return new WP_Error(
          'respira_lite_max_keys_reached',
          sprintf(
              __( 'Maximum API key limit reached (%d keys). Delete an existing key to create a new one.', 'respira-for-wordpress-lite' ),
              RESPIRA_LITE_MAX_API_KEYS
          ),
          array( 'status' => 403 )
      );
  }
  ```

### 5. Audit Log Cleanup

**Missing:**
- Cron job registration in `class-respira-lite-activator.php`:
  ```php
  if ( ! wp_next_scheduled( 'respira_lite_cleanup_logs' ) ) {
      wp_schedule_event( time(), 'daily', 'respira_lite_cleanup_logs' );
  }
  ```
- Cleanup function and hook:
  ```php
  public static function cleanup_old_logs(): void {
      global $wpdb;

      $table = $wpdb->prefix . 'respira_lite_audit_log';
      $days = RESPIRA_LITE_AUDIT_RETENTION_DAYS;
      $date_limit = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

      $wpdb->query(
          $wpdb->prepare(
              "DELETE FROM {$table} WHERE created_at < %s",
              $date_limit
          )
      );
  }
  add_action( 'respira_lite_cleanup_logs', array( 'Respira_Lite_Core', 'cleanup_old_logs' ) );
  ```

### 6. Deactivator

**Missing:**
- Clear cron job on deactivation:
  ```php
  wp_clear_scheduled_hook( 'respira_lite_cleanup_logs' );
  ```

### 7. License File

**Status:** ✅ Created but abbreviated
**Spec wants:** Full GPL v2 text

---

## Missing from PROMPT-02 Specification

**Status:** ✅ ALL COMPLETE (updated in commit 70fd232)

---

## Missing from PROMPT-03 Specification

**Status:** ⚠️ DOCUMENTED ONLY

The spec asks for actual implementation of website changes, but I only created documentation (`LITE_VERSION_IMPLEMENTATION.md`).

**Should implement:**
1. `src/lib/license.ts` - Add `createLiteLicense()` function
2. `src/pages/api/auth/signup.ts` - Detect Lite users
3. `src/pages/dashboard/admin.astro` - Add filtering tabs
4. `src/pages/about-lite.astro` - Create comparison page
5. `src/pages/dashboard/index.astro` - Add plan badges

**Decision needed:** Implement website changes or keep as documentation?

---

## Summary

### Critical Gaps:
1. ❌ Main plugin file missing constants and settings link
2. ❌ API missing `/usage` endpoint
3. ❌ Auth missing 3 API key limit
4. ❌ Audit log missing cron cleanup job
5. ❌ Deactivator missing cron cleanup

### Minor Gaps:
6. ⚠️ Usage Limiter has different API (but functionally equivalent)
7. ⚠️ License file abbreviated (should have full GPL v2)

### Website:
8. ⚠️ PROMPT-03 documented but not implemented

---

## Recommendation

**Option 1:** Fix all critical gaps (1-5) in plugin code
**Option 2:** Leave as-is since functionally equivalent
**Option 3:** Fix everything to match spec exactly

Which would you prefer?
