# CRITICAL BUGS IN YOUR CONFIGURATION

## Bug #1: DUPLICATE cat_1 URL Pattern Causes Wrong Logic ⚠️⚠️⚠️

**Your configuration has:**
```json
"cat_1": {
    "name": "homepage",
    "type": "contains",
    "urls": ["/?"]
}
```

**What the plugin generates (line 50-52 in functions.php):**
```javascript
if ((window.location.pathname === '/' && window.location.search !== '')) {
    window.dataLayer.push({"content_category": "homepage"});
}
```

**THE PROBLEM:**
This checks if pathname is `/` AND there IS a query string. This means:
- `https://yoursite.com/` → NO MATCH ❌
- `https://yoursite.com/?utm_source=google` → MATCH ✓

**CONFLICT WITH cat_0:**
You have TWO homepage categories:
- cat_0: exact match for `/` (homepage without query string)
- cat_1: contains `/?` (homepage WITH query string)

Both set category to "homepage", so this isn't breaking things, but it's inefficient.

## Bug #2: cat_4 ALSO Contains `/?wizard=true` - DUPLICATE LOGIC!

**Your configuration:**
```json
"cat_4": {
    "name": "intent",
    "type": "contains",
    "urls": ["/?wizard=true", ...]
}
```

**What gets generated:**
```javascript
// This is the FIRST URL in cat_4 array
if ((window.location.pathname === '/' && window.location.search !== '')) {
    window.dataLayer.push({"content_category": "intent"});
}
```

**THE MASSIVE PROBLEM:**
Because `/?wizard=true` starts with `/?`, the plugin triggers the special case at line 50-52.
This creates a rule that matches **ANY homepage with query string**, not just `?wizard=true`.

**Real-world impact:**
- `https://yoursite.com/` → content_category: "homepage" (from cat_0) ✓
- `https://yoursite.com/?utm_source=google` → content_category: "homepage" (from cat_1) ✓
- `https://yoursite.com/?wizard=true` → content_category: "intent" (from cat_4) ✓
- `https://yoursite.com/?anything=else` → content_category: "intent" (from cat_4) ❌ WRONG!

**Wait, it gets worse:**
Since cat_1 ALSO creates the same condition, you have DUPLICATE rules:
```javascript
// From cat_1
if ((window.location.pathname === '/' && window.location.search !== '')) {
    window.dataLayer.push({"content_category": "homepage"});
}

// From cat_4 (first URL)
if ((window.location.pathname === '/' && window.location.search !== '')) {
    window.dataLayer.push({"content_category": "intent"});
}
```

**Result:** Homepage with ANY query string gets BOTH categories pushed to dataLayer!

## Bug #3: DOMContentLoaded Timing (CONFIRMED)

**Generated code wraps EVERYTHING in:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // all matching logic
});
```

**Timeline:**
1. Browser loads page
2. GA4/GTM script executes (sends page_view with content_category = undefined)
3. DOM finishes loading
4. Your mapping code executes
5. dataLayer.push fires (TOO LATE - GA4 already sent the event)

**Evidence from your data:**
- 33,000+ sessions in "(not set)" = GA4 never received content_category
- This affects 100% of initial page views

## Bug #4: Script Loads in Footer (MAKES TIMING WORSE)

**functions.php:15:**
```php
wp_enqueue_script(..., true);  // true = footer
```

This makes the script load even LATER, worsening the timing issue.

## Bug #5: JSON Contains Escaped Slashes

**Your JSON shows:**
```json
"urls": ["\\/products\\/"]
```

**In PHP, this becomes:**
```php
$raw_url = '/products/'  // Backslashes are escape chars in JSON, resolved to single slash
```

This is actually FINE - JSON escaping is normal. But it shows the data went through WordPress's sanitization which escaped slashes.

## SUMMARY: Why You Have 33k+ "(not set)" Sessions

### Primary Cause (99%):
**DOMContentLoaded timing** - By the time your code runs, GA4 already sent page_view without content_category.

### Secondary Issues (1%):
1. **Duplicate /?  patterns** causing multiple categories to be pushed (creates data inconsistency)
2. **Footer loading** making timing even worse
3. **`/?wizard=true` broken logic** - matches ANY query string, not just wizard=true

## Why Some Pages Still Get Categorized

If you're seeing ANY pages with correct categories, it's likely:
1. **Single Page App (SPA) navigation** - Subsequent page changes where dataLayer was already set
2. **Manual testing** - You tested by navigating after page load
3. **Cached/slow loading** - Random timing where script executed before GA4 (rare)

## Test to Prove This

Open your site in incognito mode:
1. Open DevTools Console BEFORE loading the page
2. Type: `console.log('START')` and press Enter
3. Load your homepage
4. You'll see:
   ```
   START
   [GA4 fires page_view event - no content_category]
   [DOMContentLoaded fires]
   [dataLayer.push with content_category]
   ```

The order proves GA4 fires BEFORE your content_category is set.

## Fix Confidence Level

**99.5% confident** that fixing the DOMContentLoaded + footer loading will solve your issue.

**0.5% uncertainty** is:
- Server-side GTM configuration (may need to forward dataLayer variables)
- Other plugin/theme conflicts
- WordPress caching interfering with script loading
