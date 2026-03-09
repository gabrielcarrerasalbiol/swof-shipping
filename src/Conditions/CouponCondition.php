<?php declare(strict_types=1);

namespace Trs\Conditions;

use Symfony\Component\Process\Exception\LogicException;
use TrsVendors\Dgm\Shengine\Conditions\Common\Enum\AbstractEnumCondition;
use TrsVendors\Dgm\Shengine\Conditions\Package\AbstractPackageCondition;
use TrsVendors\Dgm\Shengine\Interfaces\IPackage;
use WC_Coupon;


class CouponCondition extends AbstractPackageCondition
{
    public const ANY_FREE_SHIPPING_COUPON = ' ';


    public function __construct(array $value, string $operator)
    {
        self::check($operator, [], []);
        $this->value = $operator === 'empty' ? [] : array_map('strtolower', $value);
        $this->operator = $operator;
    }

    protected function isSatisfiedByPackage(IPackage $package): bool
    {
        $coupons = $package->getCoupons();
        if (in_array(self::ANY_FREE_SHIPPING_COUPON, $this->value, true)) {
            foreach ($coupons as $coupon) {
                $c = new WC_Coupon($coupon);
                if ($c->get_free_shipping()) {
                    $coupons[] = self::ANY_FREE_SHIPPING_COUPON;
                    break;
                }
            }
        }

        return self::check($this->operator, $this->value, $coupons);
    }

    /**
     * @var list<string>
     */
    private $value;

    /**
     * @var AbstractEnumCondition
     */
    private $operator;

    private static function check(string $operator, array $value, array $coupons): bool {
        switch ($operator) {
            case 'intersect':
                return !empty(array_intersect($value, $coupons));
            case 'disjoint':
                return empty(array_intersect($value, $coupons));
            case 'empty':
                return empty($coupons);
            default:
                throw new LogicException("unknown coupon operator '$operator'");
        }
    }
}