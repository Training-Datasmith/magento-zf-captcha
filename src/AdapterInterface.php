<?php

declare (strict_types=1);
namespace Laminas\Captcha;

use Laminas\Validator\Validator_Interface;
/**
 * Generic Captcha adapter interface
 *
 * Each specific captcha implementation should implement this interface
 */
interface Adapter_Interface extends Validator_Interface
{
    /**
     * Generate a new captcha
     *
     * @return string new captcha ID
     */
    public function generate();
    /**
     * Set captcha name
     *
     * @param  string $name
     * @return AdapterInterface
     */
    public function set_name($name);
    /**
     * Get captcha name
     *
     * @return string
     */
    public function get_name();
    /**
     * Get helper name to use when rendering this captcha type
     *
     * @return string
     */
    public function get_helper_name();
}