<?php
namespace Trs;

/**
 * @property-read bool $preferCustomPackagePrice;
 * @property-read bool $includeNonShippableItems;
 */
class Settings
{
    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = self::fromEnv();
        }

        return self::$instance;
    }

    function __get($property)
    {
        return $this->{$property};
    }

    protected static function fromEnv(): self
    {
        $preferCustomPackagePrice = null;
        $includeNonShippableItems = null;

        $_settings = get_option('trs_settings');
        if (is_array($_settings)) {

            if (isset($_settings['preferCustomPackagePrice'])) {
                $preferCustomPackagePrice = (bool)$_settings['preferCustomPackagePrice'];
            }

            if (isset($_settings['includeNonShippableItems'])) {
                $includeNonShippableItems = (bool)$_settings['includeNonShippableItems'];
            }
            $includeNonShippableItems = apply_filters('trs_include_non_shippable_items', $includeNonShippableItems);
        }

        return new self($preferCustomPackagePrice, $includeNonShippableItems);
    }

    protected function __construct(bool $preferCustomPackagePrice = null, bool $includeNonShippableItems = null)
    {
        $this->preferCustomPackagePrice = $preferCustomPackagePrice ?? true;
        $this->includeNonShippableItems = $includeNonShippableItems ?? false;
    }

    /** @var self */
    private static $instance;

    private $preferCustomPackagePrice;
    private $includeNonShippableItems;
}