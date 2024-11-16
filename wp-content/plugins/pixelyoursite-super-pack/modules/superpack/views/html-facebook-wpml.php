<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use PixelYourSite;

?>

<?php
$isWpmlActive = isWPMLActive();

if($isWpmlActive) : // Show lang select for main pixel
    $savedLang = (array)PixelYourSite\Facebook()->getOption("pixel_lang");
    if(!$savedLang) $savedLang = array();
    $languageCodes = array_keys(apply_filters( 'wpml_active_languages',null,null));

    if(count($savedLang) > 0 && $savedLang[0] != "") { // load pixel settings for first pixel
        $languageCodeArray = [];
        $languages = explode("_", $savedLang[0]);
        $i = 0;
        while ($i < count($languages)) {
            $languageCode = $languages[$i]; // Код языка

            if (isset($languages[$i + 1]) && ctype_upper($languages[$i + 1]) !== false) {
                $languageCode .= "_" . $languages[$i + 1]; // Объединяем с региональным кодом
                $i++; // Пропускаем следующий элемент, так как он уже объединен
            }

            $languageCodeArray[] = $languageCode;
            $i++;
        }
        $activeLang = $languageCodeArray;
    } else {
        $activeLang = $languageCodes;
    }
    // print lang checkbox list
    if ( !empty( $languageCodes ) ) : ?>
    <div class="plate">
        <div class="row  pb-3">
            <div class="col-12">
                <?php printLangList($activeLang,$languageCodes,PixelYourSite\Facebook()->getSlug()); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>
