<?php

declare (strict_types=1);
namespace Laminas\Captcha;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;
use Laminas\Re_Captcha\Re_Captcha as ReCaptchaService;
use Override;
/**
 * ReCaptcha adapter
 *
 * Allows to insert captchas driven by ReCaptcha service
 *
 * @see http://recaptcha.net/apidocs/captcha/
 *
 * @final This class should not be extended
 */
class Re_Captcha extends Abstract_Adapter
{
    /**
     * Recaptcha service object
     *
     * @var ReCaptchaService
     */
    protected $service;
    /**
     * Parameters defined by the service
     *
     * @var array
     */
    protected $service_params = [];
    /**
     * Options defined by the service
     *
     * @var array
     */
    protected $service_options = [];
    /**#@+
     * Error codes
     */
    public const MISSING_VALUE = 'missingValue';
    public const ERR_CAPTCHA = 'errCaptcha';
    public const BAD_CAPTCHA = 'badCaptcha';
    /**#@-*/
    /**
     * Error messages
     *
     * @var array
     */
    protected $message_templates = [self::MISSING_VALUE => 'Missing captcha fields', self::ERR_CAPTCHA => 'Failed to validate captcha', self::BAD_CAPTCHA => 'Captcha value is wrong'];
    /**
     * Retrieve ReCaptcha Secret key
     *
     * @return string
     */
    public function get_secret_key()
    {
        return $this->get_service()->get_secret_key();
    }
    /**
     * Retrieve ReCaptcha Site key
     *
     * @return string
     */
    public function get_site_key()
    {
        return $this->get_service()->get_site_key();
    }
    /**
     * Set ReCaptcha private key
     *
     * @param  string $secretKey
     * @return ReCaptcha Provides a fluent interface
     */
    public function set_secret_key($secret_key)
    {
        $this->get_service()->set_secret_key($secret_key);
        return $this;
    }
    /**
     * Set ReCaptcha site key
     *
     * @param  string $siteKey
     * @return ReCaptcha Provides a fluent interface
     */
    public function set_site_key($site_key)
    {
        $this->get_service()->set_site_key($site_key);
        return $this;
    }
    /**
     * Retrieve ReCaptcha secret key (BC version)
     *
     * @deprecated
     *
     * @return string
     */
    public function get_priv_key()
    {
        return $this->get_secret_key();
    }
    /**
     * Retrieve ReCaptcha site key (BC version)
     *
     * @deprecated
     *
     * @return string
     */
    public function get_pub_key()
    {
        return $this->get_site_key();
    }
    /**
     * Set ReCaptcha secret key (BC version)
     *
     * @deprecated
     *
     * @param  string $key
     * @return ReCaptcha Provides a fluent interface
     */
    public function set_priv_key($key)
    {
        return $this->set_secret_key($key);
    }
    /**
     * Set ReCaptcha site key (BC version)
     *
     * @deprecated
     *
     * @param  string $key
     * @return ReCaptcha Provides a fluent interface
     */
    public function set_pub_key($key)
    {
        return $this->set_site_key($key);
    }
    /**
     * Constructor
     *
     * @param array<string, mixed>|null $options
     */
    public function __construct($options = null)
    {
        $this->set_service(new Re_Captcha_Service());
        $this->service_params = $this->get_service()->get_params();
        $this->service_options = $this->get_service()->get_options();
        if (!empty($options)) {
            if (array_key_exists('secret_key', $options) && is_string($options['secret_key'])) {
                $this->get_service()->set_secret_key($options['secret_key']);
            }
            if (array_key_exists('site_key', $options)) {
                $this->get_service()->set_site_key($options['site_key']);
            }
            // Support pubKey and pubKey for BC
            if (array_key_exists('privKey', $options) && is_string($options['privKey'])) {
                $this->get_service()->set_secret_key($options['privKey']);
            }
            if (array_key_exists('pubKey', $options)) {
                $this->get_service()->set_site_key($options['pubKey']);
            }
            $this->set_options($options);
        }
    }
    /**
     * Set service object
     *
     * @return ReCaptcha Provides a fluent interface
     */
    public function set_service(Re_Captcha_Service $service)
    {
        $this->service = $service;
        return $this;
    }
    /**
     * Retrieve ReCaptcha service object
     *
     * @return ReCaptchaService
     */
    public function get_service()
    {
        return $this->service;
    }
    /**
     * Set option
     *
     * If option is a service parameter, proxies to the service. The same
     * goes for any service options (distinct from service params)
     *
     * @param  string $key
     * @return $this Provides a fluent interface
     */
    #[Override]
    public function set_option($key, mixed $value)
    {
        $service = $this->get_service();
        if (array_key_exists($key, $this->service_params)) {
            $service->set_param($key, $value);
            return $this;
        }
        if (array_key_exists($key, $this->service_options)) {
            $service->set_option($key, $value);
            return $this;
        }
        return parent::set_option($key, $value);
    }
    /**
     * Generate captcha
     *
     * @see AbstractAdapter::generate()
     */
    #[Override]
    public function generate(): string
    {
        return '';
    }
    /**
     * Validate captcha.
     *
     * The value should contain the name of the key within the context that
     * contains the ReCaptcha data. The default within the ReCaptcha service
     * for this is "g-recaptcha-response"
     *
     * @see    \Laminas\Validator\ValidatorInterface::isValid()
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return bool
     */
    #[Override]
    public function is_valid($value, $context = null)
    {
        if (empty($value) && !is_array($context)) {
            $this->error(self::MISSING_VALUE);
            return false;
        }
        $service = $this->get_service();
        if ((is_string($value) || is_int($value)) && array_key_exists($value, $context)) {
            $res = $service->verify($context[$value]);
        } else {
            $res = $service->verify($value);
        }
        if (!$res->is_valid()) {
            $this->error(self::BAD_CAPTCHA);
            return false;
        }
        return true;
    }
    /**
     * Get helper name used to render captcha
     */
    #[Override]
    public function get_helper_name(): string
    {
        return 'captcha/recaptcha';
    }
}