# URL Content Mapper v1.3 - Testing Guide

## How to Test After Deployment

---

## Test 1: Verify Files Uploaded Successfully ✅

### Via WordPress Admin:
1. Log into WordPress admin
2. Go to **Plugins** → **Installed Plugins**
3. Find "URL Content Mapper"
4. Check version shows **"Version 1.3"**

### Via SFTP (Optional):
```bash
# Check file dates match today
ls -la /wp-content/plugins/url-content-mapper/

# Should show today's date on these 4 files:
# wp-url-content-mapper.php
# functions.php
# admin-settings.php
# readme.txt
```

**Expected Result:** Version shows 1.3 ✅

---

## Test 2: Verify Settings Still Exist ✅

1. Go to **Tools** → **URL Content Mapper**
2. Check that all your 13 categories are still there:
   - cat_0: homepage
   - cat_1: homepage (/?))
   - cat_2: product
   - cat_3: solutions
   - cat_4: intent
   - cat_5: blog
   - cat_6: news
   - cat_7: resources
   - cat_8: webinars
   - cat_9: events
   - cat_10: landing-pages
   - cat_11: partners
   - cat_12: company

**Expected Result:** All rules present ✅

---

## Test 3: Verify Script Loads in HEAD ✅ (CRITICAL)

### Method 1: View Page Source
1. Open your homepage in browser
2. Right-click → **View Page Source** (or press Ctrl+U / Cmd+U)
3. Press Ctrl+F / Cmd+F and search for: `urlcoma-frontend-script`
4. You should find it in the `<head>` section (NOT before `</body>`)

### What to look for:
```html
<head>
  ...
  <script type="text/javascript" src="...url-content-mapper/assets/frontend-script.js?ver=1.3" id="urlcoma-frontend-script-js"></script>
  <script type="text/javascript" id="urlcoma-frontend-script-js-before">
(function(){
  'use strict';
  window.dataLayer = window.dataLayer || [];
  var matchedCategory = null;
  var matchedPriority = 999;
  ...
  </script>
  ...
</head>
```

**Expected Result:** Script in `<head>`, NOT wrapped in DOMContentLoaded ✅

---

## Test 4: Check dataLayer in Browser Console ✅ (CRITICAL)

### Step-by-Step:

1. **Open your site in INCOGNITO/PRIVATE mode** (important - clears cache)
2. **BEFORE loading the page:**
   - Press F12 (Windows) or Cmd+Option+I (Mac) to open DevTools
   - Click **Console** tab
3. **Now load your homepage**
4. In the console, type:
   ```javascript
   window.dataLayer
   ```
5. Press Enter

### What you should see:
```javascript
[
  {content_category: "homepage"},
  // ... possibly other GA4/GTM events
]
```

### Test Multiple Pages:

| Page | Command | Expected Result |
|------|---------|-----------------|
| Homepage `/` | `window.dataLayer` | `{content_category: "homepage"}` |
| Products `/products/humanity/` | `window.dataLayer` | `{content_category: "product"}` |
| Blog `/blog/any-post/` | `window.dataLayer` | `{content_category: "blog"}` |
| About `/about/` | `window.dataLayer` | `{content_category: "company"}` |

**Expected Result:** content_category appears in dataLayer BEFORE any GA4 events ✅

---

## Test 5: Verify No JavaScript Errors ✅

### In the same Console:
1. Look for any RED error messages
2. Check for errors mentioning:
   - `urlcoma`
   - `dataLayer`
   - `URLSearchParams`

**Expected Result:** No errors related to the plugin ✅

---

## Test 6: Test Query Parameter Matching ✅

### Test the wizard parameter:
1. Visit: `https://yoursite.com/?wizard=true`
2. Open Console
3. Type: `window.dataLayer`

**Expected Result:**
```javascript
{content_category: "homepage"}  // Because homepage (priority 2) beats wizard (priority 3)
```

**Note:** This is technically correct behavior. If you want wizard to win, you need to change cat_4 type to "exact" in your configuration.

---

## Test 7: GA4 DebugView (If Available) ✅

### Enable Debug Mode:
1. Install **Google Analytics Debugger** Chrome extension, OR
2. Add `?gtm_debug=1` to your URL

### Check DebugView:
1. Go to GA4 → **Admin** → **DebugView**
2. Navigate your site
3. Click on `page_view` events
4. Look for `content_category` parameter

**Expected Result:** content_category appears on page_view events ✅

---

## Test 8: Check for Timing Issues ✅

### Advanced Console Test:
```javascript
// Open Console BEFORE loading page
// Paste this code, then load the page:

(function() {
  var originalPush = window.dataLayer.push;
  window.dataLayer.push = function() {
    console.log('dataLayer.push called:', arguments[0]);
    return originalPush.apply(this, arguments);
  };
})();

// Then reload the page
// You should see content_category pushed FIRST, before GA4 events
```

**Expected Result:** content_category pushed before GA4's page_view ✅

---

## Test 9: Test on Different Browsers ✅

Test on at least 2 browsers:
- ✅ Chrome
- ✅ Firefox
- ✅ Safari
- ✅ Edge

**Expected Result:** Works on all modern browsers ✅

---

## Test 10: Production Data (24-48 hours) ✅

### After 24-48 hours:

1. Go to GA4 → **Reports** → **Engagement** → **Pages and Screens**
2. Add secondary dimension: **Content Group** (or your custom dimension name)
3. Check the percentage of "(not set)"

**Expected Result:**
- Before: 33,000+ sessions (high percentage) with "(not set)"
- After: Near 0% with "(not set)"

---

## Troubleshooting Guide

### Issue: Version still shows 1.2
**Solution:**
- Clear WordPress object cache
- Clear browser cache
- Hard refresh (Ctrl+Shift+R / Cmd+Shift+R)
- Check files actually uploaded via SFTP

### Issue: Script not in HEAD
**Solution:**
- Clear all WordPress caches
- Disable caching plugins temporarily
- Check functions.php uploaded correctly
- Look for PHP errors in wp-content/debug.log

### Issue: dataLayer not showing content_category
**Solutions:**

1. **Check script loaded:**
   - View page source
   - Search for `urlcoma-frontend-script`
   - Verify inline script is present

2. **Check for errors:**
   - Open Console
   - Look for JavaScript errors
   - Check if URLSearchParams is supported

3. **Check caching:**
   - Clear WordPress cache
   - Clear CDN cache (Cloudflare, etc.)
   - Clear browser cache
   - Test in incognito mode

4. **Check theme compatibility:**
   - Temporarily switch to default WordPress theme (Twenty Twenty-Three)
   - Test if it works
   - If yes, theme may be blocking wp_head()

### Issue: content_category wrong value
**Solution:**
- Check your configuration rules
- Remember priority system:
  - Priority 1: Exact path + query (most specific)
  - Priority 2: Exact path
  - Priority 3: Contains path + query
  - Priority 4: Contains path
- Lower priority number wins

### Issue: GA4 still shows "(not set)"
**Solutions:**

1. **Check GTM Configuration:**
   - Ensure content_category is configured as Data Layer Variable
   - Check it's sent with GA4 Configuration tag
   - Verify tag firing order (use Tag Assistant)

2. **Check Server-Side GTM:**
   - If using server-side container
   - Verify client-side dataLayer is forwarded
   - Check content_category in forwarded parameters

3. **Wait 24-48 hours:**
   - GA4 data is not real-time
   - Check DebugView for immediate feedback
   - Wait for production reports to update

### Issue: JavaScript errors about URLSearchParams
**Solution:**
- Browser doesn't support URLSearchParams (IE11)
- Add polyfill (or drop IE11 support)
- Polyfill URL: `https://cdn.jsdelivr.net/npm/url-search-params-polyfill@8.1.1/index.min.js`

---

## Quick Test Checklist

Copy and check off as you test:

- [ ] Plugin version shows 1.3
- [ ] Settings page loads correctly
- [ ] All 13 categories still present
- [ ] View page source shows script in `<head>`
- [ ] Script NOT wrapped in DOMContentLoaded
- [ ] Console shows dataLayer with content_category
- [ ] Homepage shows "homepage"
- [ ] Products page shows "product"
- [ ] Blog page shows "blog"
- [ ] No JavaScript errors in console
- [ ] Works in Chrome
- [ ] Works in Firefox/Safari
- [ ] GA4 DebugView shows content_category (if available)

---

## When to Consider It Successful

### Immediate Success Indicators (0-1 hour):
✅ Plugin version 1.3 visible
✅ Settings still intact
✅ Script in HEAD (view source)
✅ No DOMContentLoaded wrapper
✅ dataLayer shows content_category in console
✅ No JavaScript errors

### Short-term Success Indicators (24-48 hours):
✅ GA4 DebugView shows content_category on page_view
✅ Multiple page types categorized correctly
✅ No increase in error rates

### Long-term Success Indicators (1 week):
✅ "(not set)" percentage drops from high to near 0%
✅ 95%+ of pages properly categorized
✅ Clean analytics data for decision making

---

## Contact/Support

If tests fail:
1. Check COMPREHENSIVE-TEST-RESULTS.md for known issues
2. Check WordPress error log: wp-content/debug.log
3. Review browser console for specific errors
4. Verify all caches cleared

For rollback:
1. Replace files with backup copies via SFTP
2. Clear all caches
3. Re-test

---

## Summary

**Minimum Required Tests:**
1. ✅ Check version is 1.3
2. ✅ Check script in HEAD (view source)
3. ✅ Check dataLayer in console
4. ✅ Test 3-4 different page types
5. ✅ Check for JavaScript errors

**Total Time:** ~15 minutes

**Success Rate Expected:** 98%

---

**Created:** 2025-10-06
**Plugin Version:** 1.3
**Status:** Ready for Testing
