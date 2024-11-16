<?php

/**
 * Class to scrape amazon products
 * @author Muhammed Atef
 * @link http://www.deandev.com
 * @version 1.0
 */

/*
 * From Jan 2019 amazon has changed it's api usage policy. API feature is mapped with sale you made in last month.
 * https://docs.aws.amazon.com/es_es/AWSECommerceService/latest/DG/TroubleshootingApplications.html
 *
 */
class wp_automatic_amazon_api_less
{
    /**
     * cURL handle
     */
    private $ch = "";

    /**
     * Your Amazon Associate Tag
     * Now required, effective from 25th Oct.
     * 2011
     *
     * @access private
     * @var string
     */
    private $associate_tag = "YOUR AMAZON ASSOCIATE TAG";
    private $region = "";
    public $is_next_page_available = false;
    public $next_request_qid = null;
    public $update_agent_required = false;
    public $slugs = array();
    public $session_id = '';
    public $session_ubid = '';
    public function __construct(&$ch, $region)
    {
        $this->ch = $ch;
        $this->region = $region;
    }

    /**
     * Return details of a product searched by ASIN
     *
     * @param int $asin_code
     *            ASIN code of the product to search
     * @return mixed simpleXML object
     */
    public function getItemByAsin($asin_code, $slug = '')
    {

        // save timing limit
        sleep(rand(3, 5));

        // trim asin
        $asin_code = wp_automatic_trim($asin_code);

        // item URL
        $item_url = "https://www.amazon.{$this->region}/dp/$asin_code";
        $url_gcache = wp_automatic_trim($slug) == '' ? $item_url : "https://www.amazon.{$this->region}/$slug/dp/$asin_code";

        echo '<br>Item link:' . $url_gcache;

        curl_setopt($this->ch, CURLOPT_URL, "$url_gcache");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");

        $headers = array();
        $headers[] = "Authority: www.amazon.{$this->region}";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        // $headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.122 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
        $headers[] = "Sec-Fetch-Site: none";
        $headers[] = "Sec-Fetch-Mode: navigate";
        $headers[] = "Sec-Fetch-User: ?1";
        $headers[] = "Sec-Fetch-Dest: document";
        $headers[] = "Accept-Language: en-US,en;q=0.9,ar;q=0.8";

        // curl_setopt($this->ch, CURLOPT_ENCODING , "");

        // simulate location
        if ($this->session_ubid != '') {

            $headers[] = "Cookie: session-id={$this->session_id};  ubid-main={$this->session_ubid}; ";
            echo '<br>Custom location is requested.. setting session-id and ubid....';
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        $exec = curl_exec($this->ch);

        if (!stristr($exec, $asin_code)) {
            $gzdec = @gzdecode($exec);
            if (stristr($gzdec, $asin_code)) {
                $exec = $gzdec;
            }

        }

        // $exec = file_get_contents('test.txt');
        $x = curl_error($this->ch);
        $cuinfo = curl_getinfo($this->ch);
        $http_code = $cuinfo['http_code'];
        // validate returned content
        if (wp_automatic_trim($exec) == '' || wp_automatic_trim($x) != '') {
            throw new Exception('No valid reply returned from Amazon with a possible cURL err ' . $x);
        }

        // plan b: get item from google cache instead if Amazon failed to serve
        if (stristr($exec, '/captcha/') || $cuinfo['http_code'] == 503) {

            $reason = '503 error';
            if (stristr($exec, '/captcha/')) {
                'Captcha error';
            }

            echo "<br>Amazon refused to return the page( $reason ), using plan b";

            echo '<br>GcacheURL:' . $url_gcache;

            // http://webcache.googleusercontent.com/search?q=cache:https://www.amazon.com/dp/B002ZTVMDI
            $url_gcache = "http://webcache.googleusercontent.com/search?q=cache:$url_gcache";

            // curl get
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url_gcache));
            $exec_gcache = curl_exec($this->ch);

            if (stristr($exec_gcache, $asin_code) && !stristr($exec_gcache, ' 404 ') && !stristr($exec_gcache, 'unusual traffic from your computer')) {
                echo '<-- Perfect';

                $exec = $exec_gcache;
                $cuinfo['http_code'] = 200;
            } else {
                echo '<-- to no avail';
            }
        }

        // plan c google translate proxy
        if (stristr($exec, '/captcha/') || $cuinfo['http_code'] == 503) {
            echo '<br>Amazon asked for Capacha... Trying auto-proxy.......<br>';

            require_once 'proxy.GoogleTranslate.php';

            try {

                $GoogleTranslateProxy = new GoogleTranslateProxy($this->ch);
                $exec = $GoogleTranslateProxy->fetch($item_url);

                // capcha check return don't deactivate keyword
                if (stristr($exec, '/captcha/')) {
                    echo '<br><span style="color:red">Got a Captcha again, changing use agent for next call</span>';
                    $this->update_agent_required = true;
                    exit();
                }
            } catch (Exception $e) {

                echo '<br>ProxyViaGoogleException:' . $e->getMessage();
            }
        }

        // 404 not found
        if ($http_code == 404) {
            throw new Exception('404 Not founnd from Amazon when tried to load this product');
        }

        // validate returned result
        if (!stristr($exec, $asin_code)) {

            echo $exec;

            throw new Exception('No valid reply returned from Amazon can not find the item asin');
        }

        //remove products viewed by customers also viewed
        //<!--CardsClient--><div class="_cerberus-shared_style_mainCardContaine .... </script>
        $exec = preg_replace('{<!--CardsClient--><div class="_cerberus-shared_style_mainCardContaine.*?</script>}s', '', $exec, -1, $count);

        //if content changed, report removal
        if ($count > 0) {
            echo '<br>Removed CardsClient [Items viewed by customers also viewed] count:' . $count;
        }

        // fix for eur getiting defected for amazon.it
        $exec = wp_automatic_str_replace('iso-8859-1', 'utf-8', $exec);

        // report location
        preg_match('!id="glow-ingress-line2">(.*?)</span>!s', $exec, $loc_match);
        if (isset($loc_match[1])) {
            echo '<br>Found location value:' . $loc_match[1];
        }

        // echo $exec;
        //exit;

        // dom
        $doc = new DOMDocument();
        @$doc->loadHTML($exec);

        $xpath = new DOMXpath($doc);

        // title
        $elements = $xpath->query('//*[@id="productTitle"]');

        $item_title = '';
        if ($elements->length > 0) {
            $title_element = $elements->item(0);
            $item_title = wp_automatic_trim($title_element->nodeValue);
        }

        if (wp_automatic_trim($item_title) == '') {
            preg_match('{<meta property="og:title" content="(.*?)"}', $exec, $title_matches);
            $item_title = isset($title_matches[1]) ? $title_matches[1] : '';
        }

        $ret['link_title'] = $item_title;

        // the description productDescription https://www.amazon.com/Smiling-Compatible-Tempered-Protector-Protective/dp/B089ZRVKC4 https://monosnap.com/file/ahLC53FKPu5gdSTNZadpvrj6uZ2TaR
        $item_description = '';
        preg_match_all('{<div id="productDescription".*?<p>(.*?)</p>[^<]}s', $exec, $description_matches);

        $description_matches = $description_matches[1];
        if (isset($description_matches[0])) {

            $item_description = $description_matches[0];
            $item_description = wp_automatic_str_replace('</p><p>', '<br>', $item_description);
            $item_description = wp_automatic_str_replace(array(
                '<p>',
                '</p>',
                '<![CDATA[',
            ), '', $item_description);
        }

        echo '<br>ProductDescription [Product description] extracted chars:' . '<--' . strlen($item_description);

        if (wp_automatic_trim($item_description) == '' && stristr($exec, 'id="aplus"')) {

            echo '<br>aplus [User Iframe description]  extracted:';

            $elements = $xpath->query('//*[@id="aplus"]');

            if ($elements->length > 0) {
                $item_description = $doc->saveHTML($elements->item(0));
                $item_description = preg_replace(array(
                    '{<style.*?style>}s',
                    '{<a.*?/a>}s',
                    '{<script.*?/script>}s',
                ), '', $item_description);
                $item_description = strip_tags($item_description, '<p><br><img><h3>');
            }

            echo '<--' . strlen($item_description);
        }

        // book description bookDesc_iframe_wrapper
        if (stristr($exec, 'book_description_expander')) {

            $elements_book = $xpath->query('//*[@data-a-expander-name="book_description_expander"]');

            if ($elements_book->length > 0) {
                $item_book_description = $doc->saveHTML($elements_book->item(0));

                // <a
                $item_book_description = preg_replace(array(

                    '{<a.*?/a>}s',

                ), '', $item_book_description);

                $item_book_description = strip_tags($item_book_description, '<p><br><img><h3>');

                $item_description = $item_book_description . '<br>' . $item_description;
                echo '<br>Book description found.. appending...';

                echo '<--' . strlen($item_book_description) . ' chars';
            }
        }

        //book description using class books-expander-content
        //Italian site editioon example https://www.amazon.it/dp/880623983X
        if (stristr($exec, 'books-expander-content') && !stristr($exec, 'book_description_expander')) {

            $elements_book = $xpath->query('//*[@class="books-expander-content"]');

            if ($elements_book->length > 0) {
                $item_book_description = $doc->saveHTML($elements_book->item(0));

                // <a
                $item_book_description = preg_replace(array(

                    '{<a.*?/a>}s',

                ), '', $item_book_description);

                $item_book_description = strip_tags($item_book_description, '<p><br><img><h3>');

                $item_description = $item_book_description . '<br>' . $item_description;
                echo '<br>Book description found (books-expander-content).. appending...';

                echo '<--' . strlen($item_book_description) . ' chars';
            }
        }

        // book detailBulletsWrapper_feature_div https://www.amazon.com/dp/B00117ZTOS https://monosnap.com/file/r9XyRm2SF8b0Lzv2Wd2Js5L8cjQWue
        if (stristr($exec, 'detailBulletsWrapper_feature_div')) {

            $detailelements = $xpath->query('//*[@id="detailBullets_feature_div"]/ul/li');

            $detailBulletsWrapper_feature_div = '';
            if ($detailelements->length > 0) {
                foreach ($detailelements as $element) {
                    $detailBulletsWrapper_feature_div .= (wp_automatic_str_replace("\n", '', ($element->nodeValue))) . '<br>';
                }
            }

            if (wp_automatic_trim($detailBulletsWrapper_feature_div) != '') {
                // $detailBulletsWrapper_feature_div = wp_automatic_str_replace("\n" , '' , $detailBulletsWrapper_feature_div);
                $item_description = $item_description . '<br>' . $detailBulletsWrapper_feature_div;
                echo '<br>detailBulletsWrapper details found.. appending...';
            }
        }

        //book on mobile gcache contains id productDescription_fullView, extracting description using this id if item descritpion is empty
        if (wp_automatic_trim($item_description) == '' && stristr($exec, 'productDescription_fullView')) {
            echo '<br>productDescription_fullView [Book description] found on mobile.. extracting...';

            $elements_book = $xpath->query('//*[@id="productDescription_fullView"]');

            //saving
            if ($elements_book->length > 0) {
                $item_book_description = $doc->saveHTML($elements_book->item(0));

                $item_description = $item_book_description;

                echo '<--' . strlen($item_book_description) . ' chars';
            } else {
                echo '<-- Failed to extract';
            }

        }

        $ret['item_description'] = $item_description;

        // features
        $elements = $xpath->query('//*[@id="feature-bullets"]//ul/li/span[@class="a-list-item"]');

        $item_features = array();
        if ($elements->length > 0) {
            foreach ($elements as $element) {
                $item_features[] = wp_automatic_trim(($element->nodeValue));
            }
            //unset($item_features[0]);
        }

        $ret['item_features'] = $item_features;

        // product details //*[@id="prodDetails"]/div

        $elements_details = $xpath->query('//*[@id="prodDetails"]/div');

        $item_details = '';
        if ($elements_details->length > 0) {
            foreach ($elements_details as $element) {
                $item_details = wp_automatic_trim($doc->saveHTML($element));
            }
        }

        // remove feedback div
        $item_details = preg_replace('{<div id="pricingFeedbackDiv.*?div>}s', '', $item_details);
        $item_details = preg_replace('{<table id="productDetails_feedback_sections.*?/table>}s', '', $item_details);

        $item_details = wp_automatic_str_replace('<h1 class="a-size-medium a-spacing-small secHeader"> Feedback </h1>', '', $item_details);

        $ret['item_details'] = $item_details;

        // manufacture description //*[@id="aplus"]/div
        $elements_details = $xpath->query('//*[@id="aplus"]/div');

        $item_details = '';
        if ($elements_details->length > 0) {
            foreach ($elements_details as $element) {
                $item_details = wp_automatic_trim($doc->saveHTML($element));
            }
        }

        $ret['item_manufacture_description'] = $item_details;

        // images large":"
        preg_match_all('{colorImages\': \{.*?large":".*?".*?script>}s', $exec, $imgs_matches);

        $possible_img_part = '';

        if (isset($imgs_matches[0][0])) {
            $possible_img_part = $imgs_matches[0][0];
        }

        if (wp_automatic_trim($possible_img_part) != '') {

            //hiRes
            preg_match_all('{hiRes":"(.*?)"}s', $possible_img_part, $imgs_matches);

            //if no hiRes found, get large
            if (count($imgs_matches[1]) == 0) {
                echo '<br>No hiRes found trying large';
                preg_match_all('{large":"(.*?)"}s', $possible_img_part, $imgs_matches);
            } else {
                echo '<br>hiRes Images found count:' . count($imgs_matches[1]);
            }

        } else {
            preg_match_all('{large":"(.*?)"}s', $exec, $imgs_matches);
        }

        $item_images = array_unique($imgs_matches[1]);

        if (count($item_images) == 0) {
            // no images maybe a book
            echo '<br>No images found using method #1 trying method #2';

            // ebooksImageBlockContainer imageGalleryData
            if (stristr($exec, 'imageGalleryData')) {
                preg_match('{imageGalleryData(.*?)dimensions}s', $exec, $poassible_book_imgs);
            } elseif (stristr($exec, 'ebooksImageBlockContainer')) {
                preg_match('{<div id="ebooksImageBlockContainer(.*?)div>\s</div>}s', $exec, $poassible_book_imgs);
            } elseif (stristr($exec, 'mainImageContainer')) {
                preg_match('{<div id="mainImageContainer(.*?)div>}s', $exec, $poassible_book_imgs);
            } elseif (stristr($exec, 'main-image-container')) {
                preg_match('{<div id="main-image-container(.*?)div>}s', $exec, $poassible_book_imgs);
            }

            if (isset($poassible_book_imgs)) {
                $poassible_book_imgs = $poassible_book_imgs[0];
            } else {
                $poassible_book_imgs = '';
            }

            if (wp_automatic_trim($poassible_book_imgs) != '') {
                preg_match_all('{https://.*?\.jpg}s', $poassible_book_imgs, $possible_book_img_srcs);
                $possible_book_img_srcs = $possible_book_img_srcs[0];

                if (count($possible_book_img_srcs) > 0) {
                    $final_img = end($possible_book_img_srcs);
                    $final_img = preg_replace('{,.*?\.}', '.', $final_img);
                    $item_images = array(
                        $final_img,
                    );
                }
            }
        }

        // mobile images data-zoom-hires
        if (count($item_images) == 0 && strpos($exec, 'data-zoom-hires')) {

            echo '<br>Mobile images data-zoom-hires found';

            preg_match_all('{data-zoom-hires="(.*?)"}', $exec, $mobile_imgs_matches);

            $item_images = $mobile_imgs_matches[1];
        }

        // mobile images data-a-hires
        if (count($item_images) == 0 && strpos($exec, 'data-a-hires')) {

            echo '<br>Mobile images data-a-hires found';

            preg_match_all('{data-a-hires="(.*?1500_.jpg)"}', $exec, $mobile_imgs_matches);

            $item_images = $mobile_imgs_matches[1];
        }

        $ret['item_images'] = $item_images;

        // prices priceblock_ourprice
        unset($elements);

        if (stristr($exec, 'id="priceblock_dealprice') || stristr($exec, 'id=priceblock_dealprice')) {
            $elements = $xpath->query('//*[@id="priceblock_dealprice"]');

            echo '<br>Price tag:priceblock_dealprice';
        } elseif (stristr($exec, 'id="priceblock_ourprice') || stristr($exec, 'id=priceblock_ourprice')) {
            $elements = $xpath->query('//*[@id="priceblock_ourprice"]');

            echo '<br>Price tag:priceblock_ourprice';
        } elseif (stristr($exec, 'id="priceblock_saleprice') || stristr($exec, 'id=priceblock_saleprice')) {

            echo '<br>Price tag:priceblock_saleprice';

            $elements = $xpath->query('//*[@id="priceblock_saleprice"]');
        } elseif (stristr($exec, 'id="price_inside_buybox') || stristr($exec, 'id=price_inside_buybox')) {

            echo '<br>Price tag:price_inside_buybox';
            $elements = $xpath->query('//*[@id="price_inside_buybox"]');
        } elseif (stristr($exec, 'id="newBuyBoxPrice') || stristr($exec, 'id=newBuyBoxPrice')) {

            echo '<br>Price tag:newBuyBoxPrice';
            $elements = $xpath->query('//*[@id="newBuyBoxPrice"]');
        }

        // remove <span class="a-size-large a-color-price">-</span>
        // this is a fake and does not really contain a price example:https://www.amazon.com/dp/B08N5LNQCX
        $exec = preg_replace('{<span class="a-size-large a-color-price">-</span>}s', '', $exec);

        $item_price = '';
        if (isset($elements) && $elements->length > 0) {
            $item_price = wp_automatic_trim($elements->item(0)->nodeValue);
            $item_price = preg_replace('{ -.*}', '', $item_price);
        } elseif (stristr($exec, ' offer-price ')) {

            echo '<br>Price tag:offer-price';

            // <span class="a-size-medium a-color-price offer-price a-text-normal">$16.98</span>
            preg_match_all('{ offer-price .*?>(.*?)</span>}s', $exec, $possible_price_matches);
            $possible_price_matches = $possible_price_matches[1];

            if (isset($possible_price_matches[0]) && wp_automatic_trim($possible_price_matches[0]) != '') {
                $item_price = $possible_price_matches[0];
            }

        } elseif (stristr($exec, '<span class="a-size-small a-color-price">') && !stristr($exec, '<span class="a-size-small a-color-price">(')) {

            echo '<br>Price tag:a-color-price';

            preg_match_all('{<span class="a-size-small a-color-price">(.*?)</span>}s', $exec, $possible_price_matches);
            $possible_price_matches = $possible_price_matches[1];

            if (isset($possible_price_matches[0]) && wp_automatic_trim($possible_price_matches[0]) != '') {
                $item_price = wp_automatic_trim($possible_price_matches[0]);
            }

        } elseif (stristr($exec, '<span class="a-size-large a-color-price">')) {

            echo '<br>Price tag:a-size-large a-color-price';

            preg_match_all('{<span class="a-size-large a-color-price">(.*?)</span>}s', $exec, $possible_price_matches);
            $possible_price_matches = $possible_price_matches[1];

            if (isset($possible_price_matches[0]) && wp_automatic_trim($possible_price_matches[0]) != '') {
                $item_price = wp_automatic_trim($possible_price_matches[0]);
            }

        } elseif (stristr($exec, '<span class="a-price a-text-price a-size-medium" data-a-size="b" data-a-color="price"><span class="a-offscreen">')) {

            // <span class="a-price a-text-price a-size-medium" data-a-size="b" data-a-color="price"><span class="a-offscreen">$8.48</span>

            echo '<br>Price tag: a-price a-text-price a-size-medium" data-a-size="b" data-a-color="price"  ';

            preg_match_all('{<span class="a-price a-text-price a-size-medium" data-a-size="b" data-a-color="price"><span class="a-offscreen">(.*?)</span>}s', $exec, $possible_price_matches);
            $possible_price_matches = $possible_price_matches[1];

            if (isset($possible_price_matches[0]) && wp_automatic_trim($possible_price_matches[0]) != '') {
                $item_price = wp_automatic_trim($possible_price_matches[0]);
            }

        } elseif (stristr($exec, 'data-a-color="price"><span class="a-offscreen">')) {

            // data-a-color="price"><span class="a-offscreen">$8.48</span>
            echo '<br>Price tag: data-a-color="price"';

            preg_match_all('{data-a-color="price"><span class="a-offscreen">(.*?)</span>}s', $exec, $possible_price_matches);
            $possible_price_matches = $possible_price_matches[1];

            if (isset($possible_price_matches[0]) && wp_automatic_trim($possible_price_matches[0]) != '') {
                $item_price = wp_automatic_trim($possible_price_matches[0]);
            }

        } elseif (stristr($exec, 'data-a-color="base"><span class="a-offscreen">')) {

            // data-a-color="price"><span class="a-offscreen">$8.48</span>
            echo '<br>Price tag: data-a-color="base"';

            preg_match_all('{data-a-color="base"><span class="a-offscreen">([^\s]*?)</span>}s', $exec, $possible_price_matches);

            $possible_price_matches = $possible_price_matches[1];

            if (isset($possible_price_matches[0]) && wp_automatic_trim($possible_price_matches[0]) != '') {
                $item_price = wp_automatic_trim($possible_price_matches[0]);
            }

        }

        // translation extra space removal
        $item_price = wp_automatic_str_replace('$ ', '$', $item_price);

        $ret['item_price'] = $item_price;

        // pre-sale price
        $elements = $xpath->query("//*[contains(@class, 'priceBlockStrikePriceString')]");

        $item_pre_price = $item_price;
        if ($elements->length > 0) {

            $item_pre_price = wp_automatic_trim($elements->item(0)->nodeValue);
            $item_pre_price = preg_replace('{ -.*}', '', $item_pre_price);

            echo '<br>Pre price tag:priceBlockStrikePriceString:' . $item_pre_price;

        } elseif (stristr($exec, 'data-a-strike="true" data-a-color="secondary">')) {

            // data-a-strike="true" data-a-color="secondary"><span class="a-offscreen">$169.00</span>
            // data-a-strike="true" data-a-color="secondary"><span class="a-offscreen"><span dir="rtl">جنيه</span>8,599.00</span>
            echo '<br>Pre price tag:data-a-strike="true"';

            preg_match('{data-a-strike="true" data-a-color="secondary"><span class="a-offscreen">(.*?)</span>}s', $exec, $possible_price_matches_pre);

            //if dir="rtl exists, try another match
            if (isset($possible_price_matches_pre[1]) && stristr($possible_price_matches_pre[1], 'dir="rtl"')) {
                preg_match('{data-a-strike="true" data-a-color="secondary"><span class="a-offscreen"><span dir="rtl">(.*?)</span>(.*?)</span>}s', $exec, $possible_price_matches_pre);

                //modify match 1 and add 2 before it
                $possible_price_matches_pre[1] = $possible_price_matches_pre[1] . ' ' . $possible_price_matches_pre[2];

            }

            $possible_price_matches_pre = $possible_price_matches_pre[1];

            if (wp_automatic_trim($possible_price_matches_pre) != '') {
                $item_pre_price = $possible_price_matches_pre;
            }

            //data-a-strike="true" data-a-color="tertiary"><span class="a-offscreen">₹1,776</span>
        } elseif (stristr($exec, 'data-a-strike="true" data-a-color="tertiary"><span class="a-offscreen">')) {
            echo '<br>Pre price tag:data-a-strike="true"';

            preg_match('{data-a-strike="true" data-a-color="tertiary"><span class="a-offscreen">(.*?)</span>}s', $exec, $possible_price_matches_pre);
            $possible_price_matches_pre = $possible_price_matches_pre[1];

            if (wp_automatic_trim($possible_price_matches_pre) != '') {
                $item_pre_price = $possible_price_matches_pre;
            }
        } else {
            echo '<br>Pre price tag: Not found';
        }

        $ret['item_pre_price'] = $item_pre_price;

        // item link
        $ret['item_link'] = 'https://amazon.' . $this->region . '/dp/' . $asin_code;
        $ret['item_reviews'] = 'https://www.amazon.' . $this->region . '/reviews/iframe?akid=AKIAJDYHK6WW2AYDNYJA&alinkCode=xm2&asin=' . $asin_code . '&atag=iatefpro&exp=2035-07-19T16%3A07%3A21Z&v=2&sig=ofoCKfF6T0LDaPzBPX%252BB2tnjuzE3gCl%252BstWxTFdnCJQ%253D';

        // item rating <div id="averageCustomerReviews" ..... a-star-4">
        preg_match('{<div id="averageCustomerReviews" .*?a-star-(\d[-\d]*)}s', $exec, $rating_matches);

        $item_rating = '';
        $ret['item_rating'] = ''; // default
        if (isset($rating_matches[1]) && wp_automatic_trim($rating_matches[1]) != '') {
            $rating_matches[1] = wp_automatic_str_replace('-', '.', $rating_matches[1]);
            if (is_numeric($rating_matches[1])) {
                $item_rating = $rating_matches[1];
                $ret['item_rating'] = $item_rating;
                echo '<br>Item rating found:' . $item_rating;
            }
        }

        // out of stock yes or no <div id="outOfStock
        // example https://www.amazon.es/dp/B01LVXGKSQ
        $ret['item_out_of_stock'] = '';

        if (wp_automatic_trim($ret['item_price']) == '' && stristr($exec, '<div id="outOfStock')) {
            $ret['item_out_of_stock'] = 'yes';
        }

        // categories <ul class="a-unordered-list a-horizontal a-size-small">
        $ret['item_cats'] = '';
        preg_match('!<ul class="a-unordered-list a-horizontal a-size-small">(.*?)</ul>!s', $exec, $whole_cat_matches);

        if (isset($whole_cat_matches[1]) && wp_automatic_trim($whole_cat_matches[1]) != '') {

            // cats found
            preg_match_all('!<a.*?>(.*?)</a>!s', $whole_cat_matches[1], $cats_matches);

            if (isset($cats_matches[1]) && is_array($cats_matches[1])) {
                $ret['item_cats'] = implode(' > ', array_map('trim', $cats_matches[1]));
            }
        }

        //brand <tr class="a-spacing-small po-brand"> <td class="a-span3">        <span class="a-size-base a-text-bold">Brand</span>      </td> <td class="a-span9">    <span class="a-size-base po-break-word">BLACK+DECKER</span>
        //get the tr with class contains  po-brand
        $elements = $xpath->query("//*[contains(@class, 'po-brand')]");

        $item_brand = '';
        if ($elements->length > 0) {
            $item_brand = wp_automatic_trim($elements->item(0)->nodeValue);

            //remove the first word  then trim the result example Brand           Apple
            //explode the string by space
            $brand_array = explode(' ', $item_brand);
            //remove the first word
            unset($brand_array[0]);
            //join the array
            $item_brand = implode(' ', $brand_array);
            //trim the result
            $item_brand = wp_automatic_trim($item_brand);

        }

        echo '<br>Brand: ' . $item_brand;

        $ret['item_brand'] = $item_brand;

        //book author

        /**
         * <span class="author notFaded" data-width="">
        <a class="a-link-normal" href="/Jon-Duckett/e/B001IR3Q7I/ref=dp_byline_cont_book_1">Jon Duckett</a>       <span class="contribution" spacing="none">
        <span class="a-color-secondary">(Author)</span> </span>
        </span>
         *
         */

        $elements = $xpath->query("//*[contains(@class, 'author notFaded')]/a");

        //ini product_author
        $item_author = '';
        $ret['product_author'] = '';

        if ($elements->length > 0) {

            //value of the first element
            $item_author = wp_automatic_trim($elements->item(0)->nodeValue);

            //echo
            echo '<br>Author: ' . $item_author;

            //set
            $ret['product_author'] = $item_author;

        } else {
            echo '<br>Author: Not found';
        }

        //getting product summary id product-summary

        //ini
        $item_summary = '';
        $ret['product_summary'] = '';

        //get the div with id product-summary
        $elements = $xpath->query("//*[contains(@id, 'product-summary')]");

        if ($elements->length > 0) {

            //html of the match
            $item_summary = $doc->saveHTML($elements->item(0));

            //echo length of summary
            echo '<br>Summary found with length: ' . strlen($item_summary);

            //set
            $ret['product_summary'] = $item_summary;

        } else {
            echo '<br>AI Summary: Not found';
        }

        //customer reviews data-hook="review" for every review
        //get all reviews with data-hook="review"
        $elements = $xpath->query("//*[@data-hook='review']");

        //ini
        $item_reviews = array();
        $item_reviews_text = '';
        $ret['product_reviews'] = array();
        $ret['product_reviews_text'] = '';

        if ($elements->length > 0) {

            //loop through the reviews
            foreach ($elements as $element) {

                //get the html of the review

                $review = $doc->saveHTML($element);

                //build document
                $doc_review = new DOMDocument();

                //load the review html
                @$doc_review->loadHTML($review);

                //xpath
                $xpath_review = new DOMXpath($doc_review);

                //extract reviewer name by class <span class="a-profile-name">Christine</span>
                $elements_review = $xpath_review->query("//*[contains(@class, 'a-profile-name')]");

                //ini
                $reviewer_name = '';

                if ($elements_review->length > 0) {
                    $reviewer_name = wp_automatic_trim($elements_review->item(0)->nodeValue);
                }

                //extract rating by class <span class="a-icon-alt">5.0 out of 5 stars</span>
                $elements_review = $xpath_review->query("//*[contains(@class, 'a-icon-alt')]");

                //ini
                $review_rating = '';

                if ($elements_review->length > 0) {
                    $review_rating = wp_automatic_trim($elements_review->item(0)->nodeValue);
                }

                //extract review title by xpath a[data-hook="review-title"]/span[2]
                $elements_review = $xpath_review->query("//a[@data-hook='review-title']/span[2]");

                //ini
                $review_title = '';

                if ($elements_review->length > 0) {
                    $review_title = wp_automatic_trim($elements_review->item(0)->nodeValue);
                }

                //extract review text by xpath div[@data-hook="review-collapsed"]/span
                $elements_review = $xpath_review->query("//div[@data-hook='review-collapsed']/span");

                //ini
                $review_text = '';

                if ($elements_review->length > 0) {
                    $review_text = wp_automatic_trim($elements_review->item(0)->nodeValue);
                }

                //build review text
                $review = '<b>Reviewer:</b> ' . $reviewer_name . '<br><b>Rating:</b> ' . $review_rating . '<br><b>Title:</b> ' . $review_title . '<br><b>Review:</b> ' . $review_text;

                //build review array
                $review_arr = array(
                    'reviewer_name' => $reviewer_name,
                    'review_rating' => $review_rating,
                    'review_title' => $review_title,
                    'review_text' => $review_text,
                );

                $item_reviews[] = $review_arr;

                //get the text of the review
                $item_reviews_text .= $review . '<br><br>';
            }

            //set
            $ret['product_reviews'] = $item_reviews;
            $ret['product_reviews_text'] = $item_reviews_text;

            //echo length of reviews
            echo '<br>Reviews found with count: ' . count($item_reviews);

        } else {
            echo '<br>Reviews: Not found';
        }

        //print_r($ret);
        //exit;

        return $ret;
    }

    /**
     * Return details of a product searched by keyword
     *
     * @param string $keyword
     *            keyword to search
     * @param string $product_type
     *            type of the product
     * @return mixed simpleXML object
     */
    public function getItemByKeyword($keyword, $ItemPage, $product_type, $additionalParam = array(), $min = '', $max = '')
    {

        // next page flag reset
        $this->is_next_page_available = false;

        // encoded keyword
        $keyword_encoded = urlencode(wp_automatic_trim($keyword));

        $search_url = "https://www.amazon.{$this->region}/s?k=$keyword_encoded&ref=nb_sb_noss";

        // https://www.amazon.co.uk/s?k=iphone&ref=nb_sb_noss
        if ($ItemPage != 1) {

            $search_url .= "&page=$ItemPage";
        }

        echo $search_url;

        // curl get
        $x = 'error';
        $url = $search_url;
        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, 'accept-encoding: utf-8');
        curl_setopt($this->ch, CURLOPT_ENCODING, "");

        $exec = curl_exec($this->ch);
        $x = curl_error($this->ch);

        // validate returned content
        if (wp_automatic_trim($exec) == '' || wp_automatic_trim($x) != '') {
            throw new Exception('No valid reply returned from Amazon with a possible cURL err ' . $x);
        }

        // validate products found
        if (!stristr($exec, 'data-asin')) {
            // throw new Exception('No items found') ;
            echo '<br>No items found';

            // echo $exec;

            return array();
        }

        // extract products
        preg_match_all('{data-asin="(.*?)"}', $exec, $productMatchs);
        $asins = $productMatchs[1];

        // last page
        if (stristr($exec, 'proceedWarning')) {
            echo '<br>Reached end page of items';
            return array();
        }

        // next page flag
        $possible_next_page = $ItemPage + 1;
        if (stristr($exec, 'page=' . $possible_next_page . '&')) {
            $this->is_next_page_available = true;
        }

        return ($asins);
    }

    /**
     * Return list of ASINs of items by scraping the page
     *
     * @param string $pageUrl
     * @param string $ch
     *            curl handler
     */
    public function getASINs($moreUrl, $htmlAdded = false)
    {
        if (!$htmlAdded) {

            // save timing limit
            sleep(rand(3, 5));

            // curl get
            $x = 'error';
            $url = $moreUrl;

            // echo '<br>Call URL is: ' . $moreUrl;

            /*
             * $this->ch = curl_init ();
             * $verbose=fopen('/Applications/MAMP/htdocs/wordpress/wp-content/plugins/wp-automatic/verbose.txt', 'w');
             * curl_setopt($this->ch, CURLOPT_VERBOSE , 1);
             * curl_setopt($this->ch, CURLOPT_STDERR,$verbose);
             */

            $headers = array();
            $headers[] = "Authority: www.amazon.{$this->region}";
            $headers[] = "Upgrade-Insecure-Requests: 1";
            // $headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.122 Safari/537.36";
            $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
            $headers[] = "Sec-Fetch-Site: none";
            $headers[] = "Sec-Fetch-Mode: navigate";
            $headers[] = "Sec-Fetch-Dest: document";
            $headers[] = "Accept-Language: en-US,en;q=0.9";

            //accept encoding
            $headers[] = "Accept-Encoding: deflate, br";

            // simulate location
            if ($this->session_ubid != '') {

                $headers[] = "Cookie: session-id={$this->session_id};  ubid-main={$this->session_ubid}; ";
                echo '<br>Custom location is requested.. setting session-id and ubid....';
            }

            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($this->ch, CURLOPT_ENCODING , "");
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
            curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));

            $exec = curl_exec($this->ch);

            $x = curl_error($this->ch);
            $cuinfo = curl_getinfo($this->ch);
        } else {
            $exec = $moreUrl;

            //if whishlist, only grab list items and ignore suggested products at the end of the page <ul id="g-items"
            if (stristr($exec, '<ul id="g-items"')) {

                $exec_parts = explode('<ul id="g-items"', $exec);
                $exec_parts2 = explode('</ul>', $exec_parts[1]);

                if ($exec_parts2[0] != '') {
                    $exec = $exec_parts2[0];
                    echo '<br>Whishlist items found, adpating HTML...';

                }

            }

        }

        // Validate reply
        if (wp_automatic_trim($exec) == '') {
            throw new Exception('Empty reply from Amazon with possible curl error ' . $x);
        }

        if (!stristr($exec, 'amazon')) {
            $gzdec = @gzdecode($exec);
            if (stristr($gzdec, 'amazon')) {
                $exec = $gzdec;
            }
        }

        // current location report id="glow-ingress-line2">
        // New York 10001&zwnj;
        // </span>
        preg_match('!id="glow-ingress-line2">(.*?)</span>!s', $exec, $loc_match);
        if (isset($loc_match[1])) {
            echo '<br>Found location value:' . $loc_match[1];
        }

        // Capacha check
        if (stristr($exec, '/captcha/') || (isset($cuinfo) && $cuinfo['http_code'] == 503)) {
            echo '<br>Amazon asked for Capacha..  Trying auto-proxy....';

            require_once 'proxy.GoogleTranslate.php';

            try {

                $GoogleTranslateProxy = new GoogleTranslateProxy($this->ch);
                $exec = $GoogleTranslateProxy->fetch($url);

                // capcha check return don't deactivate keyword
                if (stristr($exec, '/captcha/')) {
                    echo '<br><span style="color:red">Got a Captcha again, changing use agent for next call</span>';
                    $this->update_agent_required = true;
                    exit();
                }
            } catch (Exception $e) {

                echo '<br>ProxyViaGoogleException:' . $e->getMessage();
            }
        }

        // best selling pages pter/dp/B08L5M9BTJ/re
        $asins = array();
        if (!stristr($exec, 'data-asin') && stristr($exec, '/dp/')) {
            echo '<br>No data-asin search but there are products, lets grab them...';
            preg_match_all('!/dp/(.{10})[/|"|\?]!', $exec, $asins_matches);

            $asins = (array_values(array_unique($asins_matches[1])));
        }

        // validate products found
        if (!stristr($exec, 'data-asin') && (!isset($asins) || count($asins) == 0)) {
            // throw new Exception('No items found') ;
            echo '<br>No items found for search results';

            echo $exec;

            return array();
        }

        // extract from search results
        $all_valid_items_html = '';
        if (count($asins) == 0) {

            // dom data-index
            // dom
            $doc = new DOMDocument();
            @$doc->loadHTML($exec);

            $xpath = new DOMXpath($doc);

            // on a search page, every product has a data-index attribute check https://monosnap.com/file/f6KhBp7wb2HDZGPBFcwxOCMkf9eszE
            // on a best seller page, every product has an id gridItemRoot check https://monosnap.com/file/Sg1ReIp1akztHoGkwddWo0rl8iUXwH
            $elements = $xpath->query('//*[@data-index]');

            //if count is 0, try to get the products by id gridItemRoot
            if ($elements->length == 0) {
                $elements = $xpath->query('//*[@id="gridItemRoot"]');

                //report
                echo '<br>Found ' . $elements->length . ' products by id gridItemRoot';

            } else {

                echo '<br>Found ' . $elements->length . ' products by data-index';

            }

            $found_products_with_rice = 0;
            foreach ($elements as $single_asin_element) {

                $item_html = $doc->saveHtml($single_asin_element);

                //a valid item from a search page has a-price-whole and not a-row a-spacing-micro
                if (!stristr($item_html, 'a-row a-spacing-micro') && stristr($item_html, 'a-price-whole')) {
                    $all_valid_items_html .= $item_html;

                    $found_products_with_rice++;

                } elseif (stristr($item_html, 'gridItemRoot')) {
                    //a valid item from a best seller page has a gridItemRoot
                    $all_valid_items_html .= $item_html;
                    $found_products_with_rice++;
                }
            }

            //report found products count
            echo '<br>Found ' . $found_products_with_rice . ' products with price to import';

            // extract products
            preg_match_all('{data-asin="(.*?)"}', $all_valid_items_html, $productMatchs);
            $asins = array_values(array_filter($productMatchs[1]));

        }

        if (stristr($exec, 'proceedWarning')) {
            echo '<br>Reached end page of items';
            return array();
        }

        // next page qid for next call amp;qid=1586898902&amp
        preg_match('{amp;qid\=(\d*?)&}', $exec, $qid_matches);

        if (isset($qid_matches[1]) && is_numeric($qid_matches[1])) {
            $this->next_request_qid = $qid_matches[1];
        }

        // get all slugs href="/Mermaid-Glitters-Decorations-Manicure-Accessory/dp/B07JC1QLZP
        $slugs = array(); // ini
        foreach ($asins as $product_asin) {
            preg_match('{/([^/]*?)/dp/' . $product_asin . '}', $all_valid_items_html, $slug_match);
            if (isset($slug_match[1])) {
                $slugs[] = $slug_match[1];
            }

        }

        $this->slugs = $slugs;

        return ($asins);
    }
}
