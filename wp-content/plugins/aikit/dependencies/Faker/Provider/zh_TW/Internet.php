<?php

namespace AIKit\Dependencies\Faker\Provider\zh_TW;

/**
 * @deprecated Use {@link \AIKit\Dependencies\Faker\Provider\Internet} instead
 * @see \AIKit\Dependencies\Faker\Provider\Internet
 */
class Internet extends \AIKit\Dependencies\Faker\Provider\Internet
{
    /**
     * @deprecated Use {@link \AIKit\Dependencies\Faker\Provider\Internet::userName()} instead
     * @see \AIKit\Dependencies\Faker\Provider\Internet::userName()
     */
    public function userName()
    {
        return parent::userName();
    }

    /**
     * @deprecated Use {@link \AIKit\Dependencies\Faker\Provider\Internet::domainWord()} instead
     * @see \AIKit\Dependencies\Faker\Provider\Internet::domainWord()
     */
    public function domainWord()
    {
        return parent::domainWord();
    }
}
