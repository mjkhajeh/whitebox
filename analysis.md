# Whitebox Library — Performance, Security & Stability Analysis

Analysis of the `mjkhajeh/whitebox` WordPress PHP utility library (19 PHP files, ~6,500 LOC).

---

## Security

### Medium

| # | Location | Issue |
|---|----------|-------|
| S1 | `Utils/Elementor.php:471` | `echo '<style>' . $post->get_content()` outputs CSS content without escaping. If a compromised DB stored script content in an Elementor post's CSS, it would render inside a `<style>` tag and could be exploited via CSS injection. phpcs suppression acknowledged. |
| S2 | `Utils.php:1230-1232` | `$icon_element_class` parameter is interpolated directly into HTML attributes (`<i class="...">` and `<img class="...">`) without `esc_attr()`. If a caller passes unsanitized data, it could inject arbitrary attributes. |

### Low

| # | Location | Issue |
|---|----------|-------|
| S3 | `Utils.php:508` | In `generate_attributes()`, the attribute `$key` is not escaped and non-scalar values are placed inside single quotes after `wp_json_encode()` without `esc_attr()`. |
| S4 | `Utils.php:842` | `$_GET` superglobal is read directly in `query_string_form_fields()`. Values are escaped at output (`esc_attr()`), but no nonce verification is performed. phpcs-acknowledged — function is a read-only form-field generator, not a state-changing handler. |
| S5 | `Utils.php:250,271` | `call_user_func()` is called with caller-supplied function names. In `convert_chars()` (line 250) the callable is passed directly; in `apply_general_variables()` (line 271) the function name is aggressively sanitized before invocation. Both rely on `is_callable()` as the final guard — no allowlist is enforced. |
| S6 | `Utils/Files.php:58` | `get_file_path($filename)` concatenates the filename onto the upload directory path without calling `sanitize_file_name()` or `esc_attr()`. Callers are expected to pass sanitized values. |
| S7 | `Utils/Files.php:132` | `set_time_limit(0)` in the `download()` method disables the execution time limit. On shared hosting this could be exploited for resource exhaustion. |

### Positive patterns

- **No SQL injection risk.** The library never uses `$wpdb` directly — all database interaction goes through WordPress API functions (`get_post_meta()`, `get_users()`, etc.).
- **No dangerous functions.** No `eval()`, `exec()`, `system()`, `passthru()`, `shell_exec()`, `serialize()`, or `unserialize()` found anywhere.
- **Consistent input sanitization.** `sanitize_text_field()`, `sanitize_html_class()`, `sanitize_url()`, `sanitize_file_name()`, `intval()`, and `absint()` are used throughout.
- **Consistent output escaping.** `esc_attr()`, `esc_url()`, `esc_html()` are used at output points. Most phpcs-suppressed outputs are safe at the point of construction.
- **Architecture eliminates vulnerability classes.** As a library with no `add_action()`/`add_filter()` calls, no AJAX handlers, and no form processing, it sidesteps CSRF, mass assignment, and similar handler-level risks.

---

## Stability

### Critical

| # | Location | Issue |
|---|----------|-------|
| T1 | `Utils/Date.php:465` | **Tautological leap year check.** The condition is `$last_day = (self::jalali_to_gregorian($jy, 12, 30) == self::jalali_to_gregorian($jy, 12, 30)) ? 30 : 29` — both sides are identical, so the condition is always `true` and `$last_day` is always 30. The 29-day path is dead code. One side should likely use day 29 to test the actual leap-year condition. |
| T2 | `Utils/WC.php:47` | **Uninitialized variable.** If `function_exists('wc_get_account_menu_items')` is `false` AND `$items` is empty, the `if` branch is skipped and `$endpoint` is returned without ever being assigned. This produces an `Undefined variable` warning and returns `null`. |
| T3 | `Utils.php:1149,1152` | **Undefined index + fatal error.** `$locations[$location]` is accessed without an `isset()` check — if `$location` is not a key in `get_nav_menu_locations()`, this warns. On line 1152, `$object->name` is accessed but `$object` can be `false` (from `wp_get_nav_menu_object()`), causing a fatal error. |

### High

| # | Location | Issue |
|---|----------|-------|
| T4 | `Utils/WC.php:187` | `$product->get_type()` is called on the result of `wc_get_product()`, which can return `false` for invalid product IDs. This causes a fatal error (calling a method on `false`). |
| T5 | `Utils/Elementor.php:448` | Unsafe method chain: `Plugin::$instance->documents->get($id)->is_built_with_elementor()`. No null check on the return value of `get()`. If the document doesn't exist, this throws or fatals. |

### Medium

| # | Location | Issue |
|---|----------|-------|
| T6 | Entire codebase | **Zero try/catch blocks.** No exception handling anywhere. PHP warnings from `json_decode()` on malformed JSON, missing files in `include_once`, or failed `download_url()` calls propagate uncaught. |
| T7 | `Utils/Options.php:47` | `$options[$keys['text-custom']]` accessed without an `isset()` guard. Produces an undefined index warning if the key is missing. |
| T8 | `Utils/Elementor.php:315` | `$settings['button_icon']['value']` assumes a nested array structure without checking the intermediate key exists. |
| T9 | `Utils/Users.php:18` | `$user['ID']` accessed in `get_user_object()` without verifying the array key exists. |
| T10 | `Utils/Date.php:54,61` | `$key[$num - 1]` — if `$num` is 0, the index becomes -1, producing an undefined offset warning. |

### Low

| # | Location | Issue |
|---|----------|-------|
| T11 | `Utils.php:69` | `to_bool()` calls `strtolower($value)` after null check but does not guard against arrays or objects that aren't `WP_Error`. `strtolower()` on non-scalar types emits a warning. |
| T12 | `Utils/Posts.php:34` | `get_post_id(null)` falls through to `absint(null) === 0` then `is_singular()`, which may have unexpected behavior outside The Loop. |
| T13 | `Utils.php:879` | In `query_string_form_fields()`, `$value` could be an array in nested structures, which is passed to `esc_attr(wp_unslash($value))` — `esc_attr()` on an array produces a warning. |

---

## Performance

### Medium

| # | Location | Issue |
|---|----------|-------|
| P1 | `Utils/WC.php:87-129` | `get_active_coupons_for_user()` fetches all published coupons with `posts_per_page => -1` (unbounded), then instantiates `new \WC_Coupon($coupon_id)` for each ID in a loop. This is an **N+1 problem** — each instantiation loads the full coupon object and metadata from the database. On shops with hundreds of coupons, this is expensive. |
| P2 | `Utils/Posts.php:77` | `get_post_options()` calls `get_post_meta()` inside a `foreach` loop — each iteration is a separate database query. Should be replaced with a single `get_post_meta($id)` call that returns all meta. |
| P3 | `Utils/Users.php:79-95` | `find_user_by_mobile()` runs `get_users()` with a 3-way OR `meta_query` on every call. No per-request caching. The query requires WordPress object cache to perform well. |

### Low

| # | Location | Issue |
|---|----------|-------|
| P4 | `Utils/Users.php:114-123` | `get_user_mobile()` makes up to 3 sequential `get_user_meta()` calls per invocation with no static caching. |
| P5 | `Utils.php:32` | `check_default()` uses `in_array($index, $skips)` which is O(n) per key in `$defaults`. With large defaults arrays (30+ keys in ElementorControls), this could be O(1) by flipping `$skips` to an associative array first. Called 47 times across the codebase. |
| P6 | `Utils/Date.php:503` | Duplicate `is_iran_timezone()` method overrides the parent `Utils` version with an independent static cache. Two separate static caches exist for the same value (`wp_timezone_string()`). |
| P7 | `ElementorControls.php:1638-1685` | Four nearly identical `foreach` blocks strip `query_type` conditions from `$includes_controls`, `$excludes_controls`, `$start_controls`, and `$end_controls`. This is copy-pasted code that could be a single helper call. |
| P8 | `Utils.php:1309-1328` | `get_icon_packs()` iterates over all packs and all icons per pack with `substr()` calls on each icon. Runs once per request (cached), but the iteration is non-trivial for large icon sets. |

### Positive patterns

- **10 static caching instances** (`static $var = null;`) prevent redundant computation within a single request — plugin detection, timezone, cart count, upload dir, max upload size.
- **All recursion is bounded** by input nesting depth — no unbounded or mutual recursion exists.
- **Zero side effects on load** — no hooks registered, no autoloading overhead beyond Composer PSR-4.
- **No `$wpdb` usage** — relies on WordPress API functions which benefit from the object cache layer.

---

## Summary

| Category | Critical | High | Medium | Low | Total |
|----------|----------|------|--------|-----|-------|
| Security | 0 | 0 | 2 | 5 | 7 |
| Stability | 3 | 2 | 5 | 3 | 13 |
| Performance | 0 | 0 | 3 | 5 | 8 |
| **Total** | **3** | **2** | **10** | **13** | **28** |

The most urgent items to address are the **3 critical stability bugs** (T1–T3) which are concrete defects — a tautological condition, an uninitialized variable, and missing null/array-key checks that cause warnings or fatals in production.
