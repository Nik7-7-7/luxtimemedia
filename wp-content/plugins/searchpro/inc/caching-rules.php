<?php

// Check if the server is Apache
if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {

    // Get the path to the .htaccess file
    $htaccessFile = ABSPATH . '.htaccess';

    // Check if the file exists
    if (file_exists($htaccessFile)) {

        // Get the current content of the .htaccess file
        $currentContent = file_get_contents($htaccessFile);

        // Check if caching rules are already present
        if (strpos($currentContent, '# BerqWP Caching Rules') === false) {

            // Append caching rules to the .htaccess file
            $newRules = <<<EOD

# BerqWP Caching Rules
<IfModule mod_expires.c>
ExpiresActive On
ExpiresByType image/jpg "access 1 year"
ExpiresByType image/webp "access 1 year"
ExpiresByType image/jpeg "access 1 year"
ExpiresByType image/gif "access 1 year"
ExpiresByType image/png "access 1 year"
ExpiresByType image/svg "access 1 year"
ExpiresByType text/css "access 1 month"
ExpiresByType application/pdf "access 1 month"
ExpiresByType application/javascript "access 1 month"
ExpiresByType application/x-javascript "access 1 month"
ExpiresByType application/x-shockwave-flash "access 1 month"
ExpiresByType image/x-icon "access 1 year"
ExpiresDefault "access 3 days"
</IfModule>

<filesMatch ".(ico|pdf|flv|jpg|jpeg|webp|png|gif|svg|js|css|woff|woff2|ttf|swf)$">
    Header set Cache-Control "max-age=96000, public"
</filesMatch>

<FilesMatch "\.(js|css|html|xml|txt)$">
    SetOutputFilter DEFLATE
</FilesMatch>

FilesMatch "\.(pdf|doc|avi|mp4|zip)$">
    Header set Cache-Control "max-age=604800, public"
</FilesMatch>
# BerqWP Caching Rules Ends Here
EOD;

            // Append the new rules to the .htaccess file
            file_put_contents($htaccessFile, $currentContent . $newRules);

        }

    }

}
