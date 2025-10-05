// This is what your plugin CURRENTLY generates based on your configuration
// Source: functions.php:25-65 + your JSON configuration

window.dataLayer = window.dataLayer || [];
document.addEventListener('DOMContentLoaded', function() {

// cat_0: homepage (exact) - URL: /
if (window.location.pathname === '/' || window.location.pathname === '' || window.location.pathname === '/index.php') {
    window.dataLayer.push({"content_category": "homepage"});
}

// cat_1: homepage (contains) - URL: /?
if ((window.location.pathname === '/' && window.location.search !== '')) {
    window.dataLayer.push({"content_category": "homepage"});
}

// cat_2: product (contains) - URL: /products/
if (window.location.href.includes('/products/')) {
    window.dataLayer.push({"content_category": "product"});
}

// cat_3: solutions (contains) - URLs: /solutions/, /industries/
if (window.location.href.includes('/solutions/')) {
    window.dataLayer.push({"content_category": "solutions"});
}
if (window.location.href.includes('/industries/')) {
    window.dataLayer.push({"content_category": "solutions"});
}

// cat_4: intent (contains) - URLs: /?wizard=true, /talk-to-an-expert/, etc.
if ((window.location.pathname === '/' && window.location.search !== '')) {
    window.dataLayer.push({"content_category": "intent"});
}
if (window.location.href.includes('/talk-to-an-expert/')) {
    window.dataLayer.push({"content_category": "intent"});
}
if (window.location.href.includes('/get-started/')) {
    window.dataLayer.push({"content_category": "intent"});
}
if (window.location.href.includes('/roi-calculator/')) {
    window.dataLayer.push({"content_category": "intent"});
}
if (window.location.href.includes('/customer-stories/')) {
    window.dataLayer.push({"content_category": "intent"});
}
if (window.location.href.includes('/customer-story/')) {
    window.dataLayer.push({"content_category": "intent"});
}

// cat_5: blog (contains) - URL: /blog/
if (window.location.href.includes('/blog/')) {
    window.dataLayer.push({"content_category": "blog"});
}

// cat_6: news (contains) - URL: /news/
if (window.location.href.includes('/news/')) {
    window.dataLayer.push({"content_category": "news"});
}

// cat_7: resources (contains) - URLs: /resource-library/, /downloads/, /articles/
if (window.location.href.includes('/resource-library/')) {
    window.dataLayer.push({"content_category": "resources"});
}
if (window.location.href.includes('/downloads/')) {
    window.dataLayer.push({"content_category": "resources"});
}
if (window.location.href.includes('/articles/')) {
    window.dataLayer.push({"content_category": "resources"});
}

// cat_8: webinars (contains) - URLs: /webinars/, /webinar/
if (window.location.href.includes('/webinars/')) {
    window.dataLayer.push({"content_category": "webinars"});
}
if (window.location.href.includes('/webinar/')) {
    window.dataLayer.push({"content_category": "webinars"});
}

// cat_9: events (contains) - URL: /events/
if (window.location.href.includes('/events/')) {
    window.dataLayer.push({"content_category": "events"});
}

// cat_10: landing-pages (contains) - URL: /lp/
if (window.location.href.includes('/lp/')) {
    window.dataLayer.push({"content_category": "landing-pages"});
}

// cat_11: partners (contains) - URL: /partners/
if (window.location.href.includes('/partners/')) {
    window.dataLayer.push({"content_category": "partners"});
}

// cat_12: company (contains) - URLs: /careers/, /about/, etc.
if (window.location.href.includes('/careers/')) {
    window.dataLayer.push({"content_category": "company"});
}
if (window.location.href.includes('/about/')) {
    window.dataLayer.push({"content_category": "company"});
}
if (window.location.href.includes('/meet-the-team/')) {
    window.dataLayer.push({"content_category": "company"});
}
if (window.location.href.includes('/legal/')) {
    window.dataLayer.push({"content_category": "company"});
}
if (window.location.href.includes('/security/')) {
    window.dataLayer.push({"content_category": "company"});
}
if (window.location.href.includes('/privacy-policy/')) {
    window.dataLayer.push({"content_category": "company"});
}
if (window.location.href.includes('/thank-you/')) {
    window.dataLayer.push({"content_category": "company"});
}
if (window.location.href.includes('/contact/')) {
    window.dataLayer.push({"content_category": "company"});
}

});
