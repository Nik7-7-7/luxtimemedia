<?php
function flying_analytics_faq() {
?>
<div style="max-width:800px">
    <h3>Gtag.js vs Minimal Analytics</h3>
    <p><strong>Gtag.js</strong> (Global Site Tag) combines tracking for both Google Analytics and Google Ads, offering comprehensive features. It’s useful for users who need advanced tracking, like conversions for AdWords.</p>
    <p><strong>Minimal Analytics</strong> is a lightweight alternative (2KB GZipped) that tracks essential data such as page views, engagement time, scrolls, file downloads, and clicks. It's sufficient for about 90% of users who don't require advanced tracking features.</p>

    <h3>Why use Minimal Analytics?</h3>
    <p>Minimal Analytics is designed to provide core tracking features while minimizing the performance impact of third-party scripts. It connects directly to the Google Analytics API, making it efficient and simple to implement without additional tools like Google Tag Manager.</p>

    <h3>Why am I still seeing a request to https://www.google-analytics.com/collect?</h3>
    <p>The request you’re seeing is a beacon sent to Google’s servers for reporting purposes. It’s a small, lightweight request that doesn’t affect your website’s performance and is essential for Google Analytics to function properly.</p>

    <h3>Should I use Gtag.js or Minimal Analytics?</h3>
    <p>If you require advanced tracking features such as AdWords conversions, <strong>Gtag.js</strong> is the better option. For most users, <strong>Minimal Analytics</strong> offers a sufficient and more lightweight solution for basic analytics tracking.</p>
</div>
<?php
}