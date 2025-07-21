<?php
/**
 * @package Localization
 * @subpackage Locales
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Locales;

use AppLocalize\Localization\Locale\de_AT;
use AppLocalize\Localization\Locale\de_CH;
use AppLocalize\Localization\Locale\de_DE;
use AppLocalize\Localization\Locale\en_CA;
use AppLocalize\Localization\Locale\en_GB;
use AppLocalize\Localization\Locale\en_IE;
use AppLocalize\Localization\Locale\en_SG;
use AppLocalize\Localization\Locale\en_US;
use AppLocalize\Localization\Locale\es_ES;
use AppLocalize\Localization\Locale\es_MX;
use AppLocalize\Localization\Locale\fi_FI;
use AppLocalize\Localization\Locale\fr_BE;
use AppLocalize\Localization\Locale\fr_FR;
use AppLocalize\Localization\Locale\it_IT;
use AppLocalize\Localization\Locale\nl_NL;
use AppLocalize\Localization\Locale\pl_PL;
use AppLocalize\Localization\Locale\ro_RO;
use AppLocalize\Localization\Locale\sv_SE;
use AppUtils\ClassHelper;

/**
 * Canned list of known locales for easy access.
 *
 * Use {@see LocalesCollection::choose()} to access this.
 *
 * @package Localization
 * @subpackage Locales
 */
class CannedLocales
{
    private LocalesCollection $locales;

    public function __construct()
    {
        $this->locales = LocalesCollection::getInstance();
    }

    /**
     * Gets the locale `de_AT` for "German (Austria)".
     * 
     * @return de_AT
     */
    public function de_AT() : de_AT
    {
        return ClassHelper::requireObjectInstanceOf(
            de_AT::class,
            $this->locales->getByID(de_AT::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `de_CH` for "German (Switzerland)".
     * 
     * @return de_CH
     */
    public function de_CH() : de_CH
    {
        return ClassHelper::requireObjectInstanceOf(
            de_CH::class,
            $this->locales->getByID(de_CH::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `de_DE` for "German (Germany)".
     * 
     * @return de_DE
     */
    public function de_DE() : de_DE
    {
        return ClassHelper::requireObjectInstanceOf(
            de_DE::class,
            $this->locales->getByID(de_DE::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `en_CA` for "English (Canada)".
     * 
     * @return en_CA
     */
    public function en_CA() : en_CA
    {
        return ClassHelper::requireObjectInstanceOf(
            en_CA::class,
            $this->locales->getByID(en_CA::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `en_GB` for "English (Great Britain)".
     * 
     * @return en_GB
     */
    public function en_GB() : en_GB
    {
        return ClassHelper::requireObjectInstanceOf(
            en_GB::class,
            $this->locales->getByID(en_GB::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `en_IE` for "English (Ireland)".
     * 
     * @return en_IE
     */
    public function en_IE() : en_IE
    {
        return ClassHelper::requireObjectInstanceOf(
            en_IE::class,
            $this->locales->getByID(en_IE::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `en_SG` for "English (Singapore)".
     * 
     * @return en_SG
     */
    public function en_SG() : en_SG
    {
        return ClassHelper::requireObjectInstanceOf(
            en_SG::class,
            $this->locales->getByID(en_SG::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `en_US` for "English (America)".
     * 
     * @return en_US
     */
    public function en_US() : en_US
    {
        return ClassHelper::requireObjectInstanceOf(
            en_US::class,
            $this->locales->getByID(en_US::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `es_ES` for "Spanish (Spain)".
     * 
     * @return es_ES
     */
    public function es_ES() : es_ES
    {
        return ClassHelper::requireObjectInstanceOf(
            es_ES::class,
            $this->locales->getByID(es_ES::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `es_MX` for "Spanish (Mexico)".
     * 
     * @return es_MX
     */
    public function es_MX() : es_MX
    {
        return ClassHelper::requireObjectInstanceOf(
            es_MX::class,
            $this->locales->getByID(es_MX::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `fi_FI` for "Finnish (Finland)".
     * 
     * @return fi_FI
     */
    public function fi_FI() : fi_FI
    {
        return ClassHelper::requireObjectInstanceOf(
            fi_FI::class,
            $this->locales->getByID(fi_FI::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `fr_BE` for "French (Belgium)".
     * 
     * @return fr_BE
     */
    public function fr_BE() : fr_BE
    {
        return ClassHelper::requireObjectInstanceOf(
            fr_BE::class,
            $this->locales->getByID(fr_BE::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `fr_FR` for "French (France)".
     * 
     * @return fr_FR
     */
    public function fr_FR() : fr_FR
    {
        return ClassHelper::requireObjectInstanceOf(
            fr_FR::class,
            $this->locales->getByID(fr_FR::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `it_IT` for "Italian (Italy)".
     * 
     * @return it_IT
     */
    public function it_IT() : it_IT
    {
        return ClassHelper::requireObjectInstanceOf(
            it_IT::class,
            $this->locales->getByID(it_IT::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `nl_NL` for "Dutch (Netherlands)".
     * 
     * @return nl_NL
     */
    public function nl_NL() : nl_NL
    {
        return ClassHelper::requireObjectInstanceOf(
            nl_NL::class,
            $this->locales->getByID(nl_NL::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `pl_PL` for "Polish (Poland)".
     * 
     * @return pl_PL
     */
    public function pl_PL() : pl_PL
    {
        return ClassHelper::requireObjectInstanceOf(
            pl_PL::class,
            $this->locales->getByID(pl_PL::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `ro_RO` for "Romanian (Romania)".
     * 
     * @return ro_RO
     */
    public function ro_RO() : ro_RO
    {
        return ClassHelper::requireObjectInstanceOf(
            ro_RO::class,
            $this->locales->getByID(ro_RO::LOCALE_NAME)
        );
    }

    /**
     * Gets the locale `sv_SE` for "Swedish (Sweden)".
     * 
     * @return sv_SE
     */
    public function sv_SE() : sv_SE
    {
        return ClassHelper::requireObjectInstanceOf(
            sv_SE::class,
            $this->locales->getByID(sv_SE::LOCALE_NAME)
        );
    }
}
