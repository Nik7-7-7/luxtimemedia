<?php
if (!defined('ABSPATH')) exit;

$optifer_folder = optifer_cache . '/combine-js/';
if (!file_exists($optifer_folder)) {
    mkdir($optifer_folder, 0755, true);
}
?>
<script>
    <?php
    $jq = file_get_contents(ABSPATH . '/wp-includes/js/jquery/jquery.min.js');
    echo $jq;
    ?>
</script>
<?php
/* if (!file_exists($optifer_folder.'/combine.css')) { */
// echo '<script defer src="' . includes_url('/js/jquery/jquery.min.js') . '"></script>';
$combine_js = '';
$combine_js = $combine_js . "\n" . $this->inline_js_data;

// foreach ($this->js_urls as $url) {
//     $file_path = ABSPATH . explode('?', str_replace(get_site_url(), '', $url))[0];
//     $content = file_get_contents($file_path);
//     $combine_js = $combine_js . "\n" . $content;
// }
$combine_js = $combine_js . "\n" . $this->inline_js;
/* var_dump($this->inline_js); */
// file_put_contents($optifer_folder . '/combine_' . md5($_SERVER['REQUEST_URI']) . '.js', $combine_js);
/* } */
/* var_dump(floor(microtime(true) * 1000) - $time_start); */
?>
<script><?php echo $combine_js; ?></script>

<script>
    window.onload = function () {
        let scripts = <?php echo json_encode($this->js_urls); ?>;

        setTimeout(() => {
            for (let key in scripts) {
                let url = scripts[key];
                let link = document.createElement('script');
                link.src = url;
                document.querySelector("body").appendChild(link);
            }

        }, 3000);

    }
</script>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            let inta_em = document.querySelectorAll('body');
            inta_em.forEach(item => {
                var script = document.createElement('script');
                script.src = '<?php echo esc_url(get_site_url() . '/wp-content/cache/optifer/combine-js/combine_' . md5($_SERVER['REQUEST_URI']) . '.js') ?>';
                item.appendChild(script);
            })
        }, 3000);
    });
</script> -->



<script defer>

    // Check browser support for WebP
    function supportsWebP() {
        const img = new Image();

        // Create a data URL with a WebP image
        img.src = 'data:image/webp;base64,UklGRjIAAABXRUJQVlA4TBEAAAAvAAAAAAfQ//73v/+BiOh/AAA=';

        // Check if the browser can decode the WebP image
        return img.onload === null;
    }

    // Replace CSS background images with WebP versions
    function replaceBackgroundImagesWithWebP() {

        var lazyImages = [].slice.call(document.querySelectorAll(`img[loading="lazy"]`));

        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.removeAttribute("loading");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });

            lazyImages.forEach(function (lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // Fallback for browsers that don't support Intersection Observer
            lazyImages.forEach(function (lazyImage) {
                lazyImage.src = lazyImage.dataset.src;
                lazyImage.removeAttribute("loading");
            });
        }

        // var images = document.querySelectorAll('img');

        // for (var i = 0; i < images.length; i++) {
        //     var image = images[i];
        //     var src = image.getAttribute('src');
        //     var webpSrc = src.replace(/\.(png|jpg|jpeg)$/, '.webp');

        //     if (supportsWebP()) {
        //         // Replace image source with WebP version
        //         image.setAttribute('src', webpSrc);
        //     }
        // }

        // var elements = document.querySelectorAll('[style*="background-image"]');

        // for (var i = 0; i < elements.length; i++) {
        //     var element = elements[i];
        //     var style = element.getAttribute('style');
        //     var updatedStyle = style.replace(/\.(png|jpg|jpeg)/g, '.webp');

        //     if (supportsWebP()) {
        //         // Replace background image URL with WebP version
        //         element.setAttribute('style', updatedStyle);
        //     }
        // }

        function getInactiveBackgroundImages(element) {
            var inactiveBackgroundImages = [];

            var styleSheets = document.styleSheets;
            var elementStyles = getComputedStyle(element);

            for (var i = 0; i < styleSheets.length; i++) {
                var styleSheet = styleSheets[i];

                try {
                    var cssRules = styleSheet.cssRules || styleSheet.rules;

                    for (var j = 0; j < cssRules.length; j++) {
                        var rule = cssRules[j];
                        var ruleStyles = rule.style;

                        if (element.matches(rule.selectorText)) {
                            var backgroundImage = ruleStyles.getPropertyValue('background-image');

                            if (backgroundImage && backgroundImage !== 'none') {
                                // Check if the background image is active
                                if (elementStyles.getPropertyValue('background-image') !== backgroundImage) {
                                    inactiveBackgroundImages.push(backgroundImage);
                                }
                            }
                        }
                    }
                } catch (error) {
                    // Handle any cross-origin or security errors
                    console.error('Error accessing CSS rules:', error);
                }

            }

            return inactiveBackgroundImages;
        }

        // var elements = document.querySelectorAll('.elementor-element');

        // for (var i = 0; i < elements.length; i++) {
        //     let element = elements[i];
        //     var style = window.getComputedStyle(element);
        //     let backgroundImage = getInactiveBackgroundImages(element)[0];

        //     // console.log(getInactiveBackgroundImages(element));

        //     if (backgroundImage && backgroundImage !== 'none') {
        //         let updatedBackgroundImage = backgroundImage.replace(/\.(png|jpg|jpeg)/g, '.webp');

        //         if (supportsWebP() && updatedBackgroundImage) {
        //             element.style.backgroundImage = updatedBackgroundImage;
        //         }
        //     }
        // }
    }

    // Execute the function after the DOM has loaded
    document.addEventListener('DOMContentLoaded', replaceBackgroundImagesWithWebP);

</script>

<script defer>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            root: null, // null means the viewport
            rootMargin: '0px', // adjust as needed
            threshold: 0.1 // adjust as needed
        };

        var observer = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    let item = entry.target;
                    let iframe = item.getAttribute('data-embed');
                    item.appendChild(iframe);

                    observer.unobserve(item);
                }
            });
        }, options);

        let yt_em = document.querySelectorAll('.berqwp-lazy-youtube');
        yt_em.forEach(function (item) {
            observer.observe(item);
        });
    });
</script>