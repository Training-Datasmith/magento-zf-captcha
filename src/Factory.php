<?php

declare (strict_types=1);
// phpcs:disable Generic.NamingConventions.ConstructorName.OldStyle,WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix
namespace Laminas\Captcha;

use function class_exists;
use function get_debug_type;
use function is_array;
use Laminas\Stdlib\Array_Utils;
use function sprintf;
use function strtolower;
use Traversable;
abstract class Factory
{
    /** @var array Known captcha types */
    protected static $class_map = ['dumb' => Dumb::class, 'figlet' => Figlet::class, 'image' => Image::class, 'recaptcha' => Re_Captcha::class];
    /**
     * Create a captcha adapter instance
     *
     * @param  iterable<string, mixed> $options
     * @return AdapterInterface
     * @throws Exception\InvalidArgumentException For a non-array, non-Traversable $options.
     * @throws Exception\DomainException If class is missing or invalid.
     */
    public static function factory($options)
    {
        if ($options instanceof Traversable) {
            $options = Array_Utils::iterator_to_array($options);
        }
        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf('%s expects an array or Traversable argument; received "%s"', __METHOD__, get_debug_type($options)));
        }
        if (!isset($options['class'])) {
            throw new Exception\DomainException(sprintf('%s expects a "class" attribute in the options; none provided', __METHOD__));
        }
        $class = $options['class'];
        if (isset(static::$class_map[strtolower($class)])) {
            $class = static::$class_map[strtolower($class)];
        }
        if (!class_exists($class)) {
            throw new Exception\DomainException(sprintf('%s expects the "class" attribute to resolve to an existing class; received "%s"', __METHOD__, $class));
        }
        unset($options['class']);
        if (isset($options['options'])) {
            $options = $options['options'];
        }
        $captcha = new $class($options);
        if (!$captcha instanceof Adapter_Interface) {
            throw new Exception\DomainException(sprintf('%s expects the "class" attribute to resolve to a valid %s instance; received "%s"', __METHOD__, Adapter_Interface::class, $class));
        }
        return $captcha;
    }
}