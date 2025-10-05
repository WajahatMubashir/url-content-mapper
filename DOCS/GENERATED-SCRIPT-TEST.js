(function(){
  'use strict';
  window.dataLayer = window.dataLayer || [];
  var matchedCategory = null;
  var matchedPriority = 999;
  function hasQueryParam(key, value) {
    if (!key) return false;
    var params = new URLSearchParams(window.location.search);
    if (!params.has(key)) return false;
    return value ? params.get(key) === value : true;
  }
  function setCategory(category, priority) {
    if (priority < matchedPriority) {
      matchedCategory = category;
      matchedPriority = priority;
    }
  }
  var pathname = window.location.pathname;
  var href = window.location.href;
  if (pathname === '/' || pathname === '' || pathname === '/index.php') { setCategory('homepage', 2); }
  if (pathname.indexOf('/') !== -1) { setCategory('homepage', 3); }
  if (pathname.indexOf('/products/') !== -1) { setCategory('product', 4); }
  if (pathname.indexOf('/solutions/') !== -1) { setCategory('solutions', 4); }
  if (pathname.indexOf('/industries/') !== -1) { setCategory('solutions', 4); }
  if (pathname.indexOf('/') !== -1 && hasQueryParam('wizard', 'true')) { setCategory('intent', 3); }
  if (pathname.indexOf('/talk-to-an-expert/') !== -1) { setCategory('intent', 4); }
  if (pathname.indexOf('/get-started/') !== -1) { setCategory('intent', 4); }
  if (pathname.indexOf('/roi-calculator/') !== -1) { setCategory('intent', 4); }
  if (pathname.indexOf('/customer-stories/') !== -1) { setCategory('intent', 4); }
  if (pathname.indexOf('/customer-story/') !== -1) { setCategory('intent', 4); }
  if (pathname.indexOf('/blog/') !== -1) { setCategory('blog', 4); }
  if (pathname.indexOf('/news/') !== -1) { setCategory('news', 4); }
  if (pathname.indexOf('/resource-library/') !== -1) { setCategory('resources', 4); }
  if (pathname.indexOf('/downloads/') !== -1) { setCategory('resources', 4); }
  if (pathname.indexOf('/articles/') !== -1) { setCategory('resources', 4); }
  if (pathname.indexOf('/webinars/') !== -1) { setCategory('webinars', 4); }
  if (pathname.indexOf('/webinar/') !== -1) { setCategory('webinars', 4); }
  if (pathname.indexOf('/events/') !== -1) { setCategory('events', 4); }
  if (pathname.indexOf('/lp/') !== -1) { setCategory('landing-pages', 4); }
  if (pathname.indexOf('/partners/') !== -1) { setCategory('partners', 4); }
  if (pathname.indexOf('/careers/') !== -1) { setCategory('company', 4); }
  if (pathname.indexOf('/about/') !== -1) { setCategory('company', 4); }
  if (pathname.indexOf('/meet-the-team/') !== -1) { setCategory('company', 4); }
  if (pathname.indexOf('/legal/') !== -1) { setCategory('company', 4); }
  if (pathname.indexOf('/security/') !== -1) { setCategory('company', 4); }
  if (pathname.indexOf('/privacy-policy/') !== -1) { setCategory('company', 4); }
  if (pathname.indexOf('/thank-you/') !== -1) { setCategory('company', 4); }
  if (pathname.indexOf('/contact/') !== -1) { setCategory('company', 4); }
  if (matchedCategory) {
    window.dataLayer.push({'content_category': matchedCategory});
  }
})();
