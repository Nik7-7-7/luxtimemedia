<?php

/** Functions useful for term slugs and post slugs */
class TRP_IN_SP_Meta_Based_Strings {

    protected $human_translated_slug_meta     = '_trp_translated_slug_';
    protected $automatic_translated_slug_meta = '_trp_automatically_translated_slug_';

    /**
     * @return string
     */
    public function get_human_translated_slug_meta() {
        return $this->human_translated_slug_meta;
    }

    /**
     * @return string
     */
    public function get_automatic_translated_slug_meta() {
        return $this->automatic_translated_slug_meta;
    }

}