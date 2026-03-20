<?php

declare (strict_types=1);
namespace Laminas\Captcha;

use Laminas\Text\Figlet\Figlet as FigletManager;
use Override;
/**
 * Captcha based on figlet text rendering service
 *
 * Note that this engine seems not to like numbers
 *
 * @final This class should not be extended
 */
class Figlet extends Abstract_Word
{
    /**
     * Figlet text renderer
     *
     * @var FigletManager
     */
    protected \Laminas\Text\Figlet\Figlet $figlet;
    /**
     * Constructor
     *
     * @param null|iterable<string, mixed> $options
     */
    public function __construct($options = null)
    {
        $this->figlet = new Figlet_Manager($options);
    }
    /**
     * Retrieve the composed figlet manager
     *
     * @return FigletManager
     */
    public function get_figlet()
    {
        return $this->figlet;
    }
    /**
     * Generate new captcha
     *
     * @return string
     */
    #[Override]
    public function generate()
    {
        $this->use_numbers = false;
        return parent::generate();
    }
    /**
     * Get helper name used to render captcha
     */
    #[Override]
    public function get_helper_name(): string
    {
        return 'captcha/figlet';
    }
}