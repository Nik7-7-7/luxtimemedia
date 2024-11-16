<?php

namespace FluentBookingPro\App\Utils\Enqueuer;

use FluentBookingPro\App\App;
use FluentBookingPro\Framework\Framework\Support\Arr;

class Webpack extends Enqueuer
{
    /**
     * @method static enqueueScript(string $handle, string $src, array $dependency = [], string|null $version = null, bool|null $inFooter = false)
     * @method static enqueueStyle(string $handle, string $src, array $dependency = [], string|null $version = null)
     */

    protected static $instance = null;
    protected static $lastJsHandel = null;


    public static function __callStatic($method, $params)
    {
        if (static::$instance == null) {
            static::$instance = new static();
        }
        return call_user_func_array(array(static::$instance, $method), $params);
    }

    private function enqueueScript($handle, $src, $dependency = [], $version = null, $inFooter = false)
    {

        $srcPath = static::getEnqueuePath($src);
        wp_enqueue_script(
            $handle,
            $srcPath,
            $dependency,
            $version,
            $inFooter
        );
        return $this;
    }

    static function with($params)
    {
        if (!is_array($params) || !Arr::isAssoc($params) || empty(static::$lastJsHandel)) {
            static::$lastJsHandel = null;
            return;
        }

        foreach ($params as $key => $val) {
            wp_localize_script(static::$lastJsHandel, $key, $val);
        }
        static::$lastJsHandel = null;
    }

    private function enqueueStyle($handle, $src, $dependency = [], $version = null)
    {
        wp_enqueue_style(
            $handle,
            static::getEnqueuePath($src),
            $dependency,
            $version
        );
    }

    private function enqueueStaticScript($handle, $src, $dependency = [], $version = null, $inFooter = false)
    {
        static::enqueueScript(
            $handle,
            static::getEnqueuePath($src),
            $dependency,
            $version,
            $inFooter
        );
    }

    private function enqueueStaticStyle($handle, $src, $dependency = [], $version = null)
    {
        static::enqueueStyle(
            $handle,
            static::getEnqueuePath($src),
            $dependency,
            $version
        );
    }

    static function getEnqueuePath($path = ''): string
    {
        return static::getAssetPath() . $path;
    }

    static function getStaticFilePath($path = ''): string
    {
        return static::getEnqueuePath($path);
    }


}
