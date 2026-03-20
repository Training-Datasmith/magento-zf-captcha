<?php

declare (strict_types=1);
// phpcs:disable SlevomatCodingStandard.TypeHints.DeclareStrictTypes.DeclareStrictTypesMissing
namespace Laminas\Captcha;

use function in_array;
use function is_array;
use Laminas\Validator\Abstract_Validator;
use function method_exists;
use Override;
use function property_exists;
use function strtolower;
use Traversable;
use function ucfirst;
/**
 * Base class for Captcha adapters
 *
 * Provides some utility functionality to build on
 */
abstract class Abstract_Adapter extends Abstract_Validator implements Adapter_Interface
{
    /**
     * Captcha name
     *
     * Useful to generate/check form fields
     *
     * @var string
     */
    protected $name;
    /**
     * Captcha options
     *
     * @var array<string, mixed>
     */
    protected $options = [];
    /**
     * Options to skip when processing options
     *
     * @var list<string>
     */
    protected $skip_options = ['options', 'config'];
    /**
     * Get name
     *
     * @return string
     */
    #[Override]
    public function get_name()
    {
        return $this->name;
    }
    /**
     * Set name
     *
     * @param string $name
     * @return AbstractAdapter Provides a fluent interface
     */
    #[Override]
    public function set_name($name)
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Set single option for the object
     *
     * @param  string $key
     * @return $this Provides a fluent interface
     */
    public function set_option($key, mixed $value)
    {
        if (in_array(strtolower($key), $this->skip_options)) {
            return $this;
        }
        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            // Setter exists; use it
            $this->{$method}($value);
            $this->options[$key] = $value;
        } elseif (property_exists($this, $key)) {
            // Assume it's metadata
            $this->{$key} = $value;
            $this->options[$key] = $value;
        }
        return $this;
    }
    /** @inheritDoc */
    #[Override]
    public function set_options($options = [])
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }
        foreach ($options as $key => $value) {
            $this->set_option($key, $value);
        }
        return $this;
    }
    /** @inheritDoc */
    #[Override]
    public function get_options()
    {
        return $this->options;
    }
    /**
     * Get helper name used to render captcha
     *
     * By default, return empty string, indicating no helper needed.
     *
     * @return string
     */
    #[Override]
    public function get_helper_name()
    {
        return '';
    }
}