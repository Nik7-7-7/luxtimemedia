<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Captcha extends Quform_Element_Field
{
    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @param  int             $id
     * @param  Quform_Form     $form
     * @param  Quform_Session  $session
     */
    public function __construct($id, Quform_Form $form, Quform_Session $session)
    {
        parent::__construct($id, $form);

        $this->session = $session;
    }

    /**
     * Get the HTML attributes for the image tag
     *
     * @return array
     */
    protected function getImageAttributes()
    {
        $attributes = array(
            'class' => 'quform-captcha-image-img',
            'src' => $this->generateImageData(),
            'alt' => __('CAPTCHA image', 'quform'),
            'data-element-id' => $this->getId()
        );

        $imageWidth = $this->config('captchaWidth');
        $imageHeight = $this->config('captchaHeight');

        if ( ! is_numeric($imageWidth)) {
            $imageWidth = self::getDefaultConfig('captchaWidth');
        }

        if ( ! is_numeric($imageHeight)) {
            $imageHeight = self::getDefaultConfig('captchaHeight');
        }

        $attributes['width'] = $imageWidth;
        $attributes['height'] = $imageHeight;

        $attributes = apply_filters('quform_captcha_image_attributes', $attributes, $this, $this->form);
        $attributes = apply_filters('quform_captcha_image_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form);

        return $attributes;
    }

    /**
     * Get the HTML for the captcha image
     *
     * @return string
     */
    protected function getImageHtml()
    {
        $output = '<div class="quform-captcha quform-cf">';
        $output .= sprintf('<div class="quform-captcha-image quform-captcha-image-%s">', $this->getIdentifier());
        $output .= Quform::getHtmlTag('img', $this->getImageAttributes());
        $output .= '</div></div>';

        return $output;
    }

    /**
     * Get the HTML attributes for the field
     *
     * @param  array  $context
     * @return array
     */
    protected function getFieldAttributes(array $context = array())
    {
        $attributes = array(
            'type' => 'text',
            'id' => $this->getUniqueId(),
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context))
        );

        if ( ! $this->isEmpty()) {
            $attributes['value'] = $this->getValue();
        }

        $placeholder = $this->form->replaceVariablesPreProcess($this->config('placeholder'));
        if (Quform::isNonEmptyString($placeholder)) {
            $attributes['placeholder'] = $placeholder;
        }
        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context);

        return $attributes;
    }

    /**
     * Get the classes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldClasses(array $context = array())
    {
        $classes = array(
            'quform-field',
            'quform-field-captcha',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->form->config('tooltipsEnabled') && Quform::isNonEmptyString($this->config('tooltip')) && Quform::get($context, 'tooltipType') == 'field') {
            $classes[] = sprintf('quform-tooltip-%s', Quform::get($context, 'tooltipEvent'));
        }

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
        }

        $classes = apply_filters('quform_field_classes', $classes, $this, $this->form, $context);
        $classes = apply_filters('quform_field_classes_' . $this->getIdentifier(), $classes, $this, $this->form, $context);

        return $classes;
    }

    /**
     * Get the HTML for the field
     *
     * @param   array   $context
     * @return  string
     */
    protected function getFieldHtml(array $context = array())
    {
        return Quform::getHtmlTag('input', $this->getFieldAttributes($context));
    }

    /**
     * Get the HTML for the element input wrapper
     *
     * @param   array   $context
     * @return  string
     */
    protected function getInputHtml(array $context = array())
    {
        $output = sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getInputClasses($context))));
        $output .= $this->getFieldHtml($context);
        $output .= $this->getFieldIconsHtml();

        if ($this->form->config('tooltipsEnabled') && Quform::isNonEmptyString($this->config('tooltip')) && Quform::get($context, 'tooltipType') == 'field') {
            $output .= sprintf('<div class="quform-tooltip-content">%s</div>', $this->config('tooltip'));
        }

        $output .= '</div>';
        $output .= $this->getImageHtml();

        return $output;
    }

    /**
     * Generates a string of characters of the given length
     *
     * @param   int     $length
     * @return  string
     */
    protected function generateCode($length)
    {
        // The character pool, similar looking characters removed
        $characters = '23456789bcdfghjkmnpqrstvwxyz';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= substr($characters, mt_rand(0, strlen($characters) - 1), 1);
        }

        return $code;
    }

    /**
     * Get the complete base64 encoded image data for use in an &lt;img&gt; tag and set generated code in session
     *
     * @return string
     */
    public function generateImageData()
    {
        if ($this->supportsDynamicImageGeneration()) {
            $code = $this->generateCode((int) $this->config('captchaLength'));
            $data = $this->generateDynamicImage($code);
        } else {
            $code = 'catch';
            $data = $this->getStaticImageData();
        }

        $this->session->set($this->form->getSessionKey() . '.captcha.' . $this->getName(), $code);

        return 'data:image/png;base64,' . $data;
    }

    /**
     * Get the path to the given font
     *
     * Copies the font file to a temporary folder it is a Windows server
     *
     * @param   string  $font  The filename of the font
     * @return  string         The full path to the font
     */
    protected function getFontPath($font)
    {
        $originalFontPath = QUFORM_LIBRARY_PATH . '/fonts/' . $font;
        $path = $originalFontPath;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Copy the fonts to the temp dir or they become locked on Windows servers when used and prevent plugin upgrades
            $tmpDir = Quform::getTempDir('quform/fonts');
            $cachedFontPath = $tmpDir . '/' . $font;

            if (file_exists($cachedFontPath)) {
                $path = $cachedFontPath;
            } else {
                if ( ! is_dir($tmpDir)) {
                    wp_mkdir_p($tmpDir);
                }

                if (wp_is_writable($tmpDir)) {
                    copy($originalFontPath, $cachedFontPath);
                    $path = $cachedFontPath;
                }

                if ( ! file_exists($cachedFontPath)) {
                    $path = $originalFontPath;
                }
            }
        }

        return $path;
    }

    /**
     * @return bool
     */
    protected function supportsDynamicImageGeneration()
    {
        return function_exists('imagecreate') &&
               function_exists('imagecolorallocate') &&
               function_exists('imagettftext') &&
               function_exists('imagepng') &&
               function_exists('imagedestroy');

    }

    /**
     * Generate the captcha image from the given code
     *
     * @param   string  $code  The code to generate
     * @return  string
     */
    protected function generateDynamicImage($code)
    {
        $yOffsetAdjustMin = 5;
        $yOffsetAdjustMax = 10;
        $width = (int) $this->config('captchaWidth');
        $height = (int) $this->config('captchaHeight');
        $minFontSize = (int) $this->config('captchaMinFontSize');
        $maxFontSize = (int) $this->config('captchaMaxFontSize');
        $bgAlpha = (int) ((1 - $this->config('captchaBgColorRgba.a', 1)) * 127);
        $textAlpha = (int) ((1 - $this->config('captchaTextColorRgba.a', 1)) * 127);

        if ($this->config('captchaRetina')) {
            $width = $width * 2;
            $height = $height * 2;
            $minFontSize = $minFontSize * 2;
            $maxFontSize = $maxFontSize * 2;
            $yOffsetAdjustMin = $yOffsetAdjustMin * 2;
            $yOffsetAdjustMax = $yOffsetAdjustMax * 2;
        }

        $image = imagecreate($width, $height);

        imagecolorallocatealpha(
            $image,
            $this->config('captchaBgColorRgba.r'),
            $this->config('captchaBgColorRgba.g'),
            $this->config('captchaBgColorRgba.b'),
            $bgAlpha
        );

        $textColor = imagecolorallocatealpha(
            $image,
            $this->config('captchaTextColorRgba.r'),
            $this->config('captchaTextColorRgba.g'),
            $this->config('captchaTextColorRgba.b'),
            $textAlpha
        );

        for($i = 0; $i < $this->config('captchaLength'); $i++) {
            $counter = mt_rand(0, 1);

            if ($counter == 0) {
                $angle = mt_rand($this->config('captchaMinAngle'), $this->config('captchaMaxAngle'));
            }

            if ($counter == 1) {
                $angle = mt_rand(360 - $this->config('captchaMaxAngle'), 360 - $this->config('captchaMinAngle'));
            }

            imagettftext(
                $image,
                mt_rand($minFontSize, $maxFontSize),
                $angle,
                (($i + 1) * $maxFontSize) - ($maxFontSize / 2),
                mt_rand($maxFontSize + $yOffsetAdjustMin, $maxFontSize + $yOffsetAdjustMax),
                $textColor,
                $this->getFontPath($this->config('captchaFont')),
                substr($code, $i, 1)
            );
        }

        ob_start();
        imagepng($image);
        $data = base64_encode(ob_get_clean());
        imagedestroy($image);

        return $data;
    }

    /**
     * Base 64 encoded captcha image when dynamic image generation is not available
     *
     * Displays the string 'catch'
     *
     * @return  string            The image data as base 64
     */
    protected function getStaticImageData()
    {
        // TODO these are the same, make seperate retina/non-retina versions
        if ($this->config('captchaRetina')) {
            return 'iVBORw0KGgoAAAANSUhEUgAAAJsAAAAoBAMAAAAbEZVkAAAAG1BMVEX///8iIiKQkJBZWVk9PT2srKzj4+PHx8d0dHSp1wYyAAABiElEQVRIie2TQU/CQBCFF0qXHnntIhwhaPRIjSYcKRrkCEnFa6vouQQNV0pC/Nvs7lSSEmLb4Mn0XTqZ7n77tvPKWKlSpf6HetFR43V7Bo2jn25YwBm4+gHXE98aN/KCP8B9AqB7u1+uU5T4QRu4h8k20MXTO4a6F3vAOA+jenefVD5EROakOkx9soi5Lf2uK6YLOw/Opa1q8yMu6ATsntWzIiSnqVvLFjOdPObEfNbQVThmlp3g6IDQ0RxdNhgXOXBGk9XI09JhZjuFkx1m0igq8kgE2TjJMmiONZv5dB8LU7Kk0rbaJMuYdxzuU7jWT8VvXBxwXDmpqZR42rshl8XTbJzlBCtyNwOE0BgDUawgVZm/OiXFkhPpdrJxMmRJaDGacxH2Cafzwb124NPFqzarxw85eG+YBJoLNeZwSDi64kIGkIKyErchsMnGHeRdr2N0CTcHhecSI31WBXCM3UsBGlsC7Zk2YMpAp40Yg6scY02J9waRudOlj8L/+69aF/VSqlSpE9oD+HU3KouM3WcAAAAASUVORK5CYII=';
        } else {
            return 'iVBORw0KGgoAAAANSUhEUgAAAJsAAAAoBAMAAAAbEZVkAAAAG1BMVEX///8iIiKQkJBZWVk9PT2srKzj4+PHx8d0dHSp1wYyAAABiElEQVRIie2TQU/CQBCFF0qXHnntIhwhaPRIjSYcKRrkCEnFa6vouQQNV0pC/Nvs7lSSEmLb4Mn0XTqZ7n77tvPKWKlSpf6HetFR43V7Bo2jn25YwBm4+gHXE98aN/KCP8B9AqB7u1+uU5T4QRu4h8k20MXTO4a6F3vAOA+jenefVD5EROakOkx9soi5Lf2uK6YLOw/Opa1q8yMu6ATsntWzIiSnqVvLFjOdPObEfNbQVThmlp3g6IDQ0RxdNhgXOXBGk9XI09JhZjuFkx1m0igq8kgE2TjJMmiONZv5dB8LU7Kk0rbaJMuYdxzuU7jWT8VvXBxwXDmpqZR42rshl8XTbJzlBCtyNwOE0BgDUawgVZm/OiXFkhPpdrJxMmRJaDGacxH2Cafzwb124NPFqzarxw85eG+YBJoLNeZwSDi64kIGkIKyErchsMnGHeRdr2N0CTcHhecSI31WBXCM3UsBGlsC7Zk2YMpAp40Yg6scY02J9waRudOlj8L/+69aF/VSqlSpE9oD+HU3KouM3WcAAAAASUVORK5CYII=';
        }
    }

    /**
     * Render the CSS for this element
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = parent::renderCss($context);

        if ($context['fieldWidth'] == 'custom' && Quform::isNonEmptyString($context['fieldWidthCustom'])) {
            $css .= sprintf('.quform-input-captcha.quform-input-%s { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        return $css;
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_captcha', array(
            // Basic
            'label' => __('Please type the characters', 'quform'),
            'description' => __('This helps us prevent spam, thank you.', 'quform'),
            'descriptionAbove' => '',

            // Styles
            'labelIcon' => '',
            'fieldIconLeft' => '',
            'fieldIconRight' => '',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'captchaLength' => '5',
            'captchaWidth' => '115',
            'captchaHeight' => '40',
            'captchaBgColor' => '#FFFFFF',
            'captchaBgColorRgba' => array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 1),
            'captchaTextColor' => '#222222',
            'captchaTextColorRgba' => array('r' => 34, 'g' => 34, 'b' => 34, 'a' => 1),
            'captchaFont' => 'Typist.ttf',
            'captchaMinFontSize' => '12',
            'captchaMaxFontSize' => '19',
            'captchaMinAngle' => '0',
            'captchaMaxAngle' => '20',
            'captchaRetina' => true,
            'customClass' => '',
            'customElementClass' => '',
            'styles' => array(),

            // Labels
            'placeholder' => '',
            'subLabel' => '',
            'subLabelAbove' => '',
            'tooltip' => '',
            'tooltipType' => 'inherit',
            'tooltipEvent' => 'inherit',
            'labelPosition' => 'inherit',
            'labelWidth' => '',

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Advanced
            'visibility' => '',

            // Translations
            'messageRequired' => '',
            'messageCaptchaNotMatch' => ''
        ));

        $config['type'] = 'captcha';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
