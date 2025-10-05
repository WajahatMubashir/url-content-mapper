// Expected JavaScript output for version 1.3
// Based on your configuration from url-content-mapper-export-2025-10-05.json

(function(){
  'use strict';
  window.dataLayer = window.dataLayer || [];
  var pushed = false;
  function hasQueryParam(key, value) {
    var params = new URLSearchParams(window.location.search);
    if (!params.has(key)) return false;
    return value ? params.get(key) === value : true;
  }
  function pushCategory(category) {
    if (!pushed) {
      window.dataLayer.push({'content_category': category});
      pushed = true;
    }
  }
  var pathname = window.location.pathname;
  var href = window.location.href;

  // cat_0: homepage (exact) - URL: /
  if (pathname === '/' || pathname === '' || pathname === '/index.php') { pushCategory('homepage'); }

  // cat_1: homepage (contains) - URL: /?
  // This pattern has ONLY a query param, no specific value
  // So it matches homepage with ANY query string
  if (pathname === '/' && hasQueryParam('')) { pushCategory('homepage'); }
  // NOTE: This is problematic - hasQueryParam('') will likely fail
  // The pattern "/?" means "homepage with any query string"
  // Better implementation would be: if (pathname === '/' && window.location.search !== '')

  // cat_2: product (contains) - URL: /products/
  if (pathname.indexOf('/products/') !== -1) { pushCategory('product'); }

  // cat_3: solutions (contains) - URLs: /solutions/, /industries/
  if (pathname.indexOf('/solutions/') !== -1) { pushCategory('solutions'); }
  if (pathname.indexOf('/industries/') !== -1) { pushCategory('solutions'); }

  // cat_4: intent (contains) - URLs: /?wizard=true, /talk-to-an-expert/, etc.
  if (pathname === '/' && hasQueryParam('wizard', 'true')) { pushCategory('intent'); }
  if (pathname.indexOf('/talk-to-an-expert/') !== -1) { pushCategory('intent'); }
  if (pathname.indexOf('/get-started/') !== -1) { pushCategory('intent'); }
  if (pathname.indexOf('/roi-calculator/') !== -1) { pushCategory('intent'); }
  if (pathname.indexOf('/customer-stories/') !== -1) { pushCategory('intent'); }
  if (pathname.indexOf('/customer-story/') !== -1) { pushCategory('intent'); }

  // cat_5: blog (contains) - URL: /blog/
  if (pathname.indexOf('/blog/') !== -1) { pushCategory('blog'); }

  // cat_6: news (contains) - URL: /news/
  if (pathname.indexOf('/news/') !== -1) { pushCategory('news'); }

  // cat_7: resources (contains) - URLs: /resource-library/, /downloads/, /articles/
  if (pathname.indexOf('/resource-library/') !== -1) { pushCategory('resources'); }
  if (pathname.indexOf('/downloads/') !== -1) { pushCategory('resources'); }
  if (pathname.indexOf('/articles/') !== -1) { pushCategory('resources'); }

  // cat_8: webinars (contains) - URLs: /webinars/, /webinar/
  if (pathname.indexOf('/webinars/') !== -1) { pushCategory('webinars'); }
  if (pathname.indexOf('/webinar/') !== -1) { pushCategory('webinars'); }

  // cat_9: events (contains) - URL: /events/
  if (pathname.indexOf('/events/') !== -1) { pushCategory('events'); }

  // cat_10: landing-pages (contains) - URL: /lp/
  if (pathname.indexOf('/lp/') !== -1) { pushCategory('landing-pages'); }

  // cat_11: partners (contains) - URL: /partners/
  if (pathname.indexOf('/partners/') !== -1) { pushCategory('partners'); }

  // cat_12: company (contains) - URLs: /careers/, /about/, etc.
  if (pathname.indexOf('/careers/') !== -1) { pushCategory('company'); }
  if (pathname.indexOf('/about/') !== -1) { pushCategory('company'); }
  if (pathname.indexOf('/meet-the-team/') !== -1) { pushCategory('company'); }
  if (pathname.indexOf('/legal/') !== -1) { pushCategory('company'); }
  if (pathname.indexOf('/security/') !== -1) { pushCategory('company'); }
  if (pathname.indexOf('/privacy-policy/') !== -1) { pushCategory('company'); }
  if (pathname.indexOf('/thank-you/') !== -1) { pushCategory('company'); }
  if (pathname.indexOf('/contact/') !== -1) { pushCategory('company'); }

})();

// TEST CASES TO VERIFY BEHAVIOR:

// URL: https://yoursite.com/
// Expected: content_category = "homepage" (from cat_0)
// Matches: cat_0 (pathname === '/')
// Result: ✓

// URL: https://yoursite.com/?utm_source=google
// Expected: content_category = "homepage" (from cat_1)
// Matches: cat_0 first (pathname === '/'), pushed = true, cat_1 blocked
// Result: ✓ (but note: cat_0 wins, not cat_1)

// URL: https://yoursite.com/?wizard=true
// Expected: content_category = "intent" (from cat_4)
// Matches: cat_0 first (pathname === '/'), pushed = true, cat_4 blocked
// Result: ✗ PROBLEM! cat_0 wins instead of cat_4

// URL: https://yoursite.com/products/humanity/
// Expected: content_category = "product"
// Matches: cat_2 (pathname.indexOf('/products/') !== -1)
// Result: ✓

// ISSUE IDENTIFIED:
// The "pushed once" logic means the FIRST matching rule wins.
// cat_0 (homepage exact) will match before cat_4 (/?wizard=true)
// This is a problem with the configuration order.

// RECOMMENDATION:
// Either:
// 1. Remove cat_1 (duplicate of cat_0 anyway)
// 2. Re-order rules so specific patterns (like /?wizard=true) come before generic ones (like /)
// 3. Remove "pushed once" logic and let ALL matching categories be pushed (GA4 will use the last one)
