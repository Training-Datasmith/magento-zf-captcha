<?php

declare (strict_types=1);
// phpcs:disable WebimpressCodingStandard.NamingConventions.ValidVariableName.final NotCamelCaps
namespace Laminas\Captcha;

use Directory_Iterator;
use function extension_loaded;
use function file_exists;
use function floor;
use function function_exists;
use function imagecolorallocate;
use function imagecolorat;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagefilledellipse;
use function imagefilledrectangle;
use function imageftbbox;
use function imagefttext;
use function imageline;
use function imagepng;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use Laminas\Stdlib\Error_Handler;
use function mt_rand;
use Override;
use function rtrim;
use function sin;
use function strlen;
use function substr;
use function time;
use function unlink;
/**
 * Image-based captcha element
 *
 * Generates image displaying random word
 *
 * @final This class should not be extended
 */
class Image extends Abstract_Word
{
    /**
     * Directory for generated images
     *
     * @var string
     */
    protected $img_dir = 'public/images/captcha/';
    /**
     * URL for accessing images
     *
     * @var string
     */
    protected $img_url = '/images/captcha/';
    /**
     * Image's alt tag content
     *
     * @var string
     */
    protected $img_alt = '';
    /**
     * Image suffix (including dot)
     *
     * @var string
     */
    protected $suffix = '.png';
    /**
     * Image width
     *
     * @var int
     */
    protected $width = 200;
    /**
     * Image height
     *
     * @var int
     */
    protected $height = 50;
    /**
     * Font size
     *
     * @var int
     */
    protected $fsize = 24;
    /**
     * Image font file
     *
     * @var string
     */
    protected $font;
    /**
     * Image to use as starting point
     * Default is blank image. If provided, should be PNG image.
     *
     * @var string
     */
    protected $start_image;
    /**
     * How frequently to execute garbage collection
     *
     * @var int
     */
    protected $gc_freq = 10;
    /**
     * How long to keep generated images
     *
     * @var int
     */
    protected $expiration = 600;
    /**
     * Number of noise dots on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $dot_noise_level = 100;
    /**
     * Number of noise lines on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $line_noise_level = 5;
    /**
     * Constructor
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct()
    {
        if (!extension_loaded('gd')) {
            throw new Exception\Extension_Not_Loaded_Exception('Image CAPTCHA requires GD extension');
        }
        if (!function_exists('imagepng')) {
            throw new Exception\Extension_Not_Loaded_Exception('Image CAPTCHA requires PNG support');
        }
        if (!function_exists('imageftbbox')) {
            throw new Exception\Extension_Not_Loaded_Exception('Image CAPTCHA requires FT fonts support');
        }
    }
    /**
     * @return string
     */
    public function get_img_alt()
    {
        return $this->img_alt;
    }
    /**
     * @return string
     */
    public function get_start_image()
    {
        return $this->start_image;
    }
    /**
     * @return int
     */
    public function get_dot_noise_level()
    {
        return $this->dot_noise_level;
    }
    /**
     * @return int
     */
    public function get_line_noise_level()
    {
        return $this->line_noise_level;
    }
    /**
     * Get captcha expiration
     *
     * @return int
     */
    public function get_expiration()
    {
        return $this->expiration;
    }
    /**
     * Get garbage collection frequency
     *
     * @return int
     */
    public function get_gc_freq()
    {
        return $this->gc_freq;
    }
    /**
     * Get font to use when generating captcha
     *
     * @return string
     */
    public function get_font()
    {
        return $this->font;
    }
    /**
     * Get font size
     *
     * @return int
     */
    public function get_font_size()
    {
        return $this->fsize;
    }
    /**
     * Get captcha image height
     *
     * @return int
     */
    public function get_height()
    {
        return $this->height;
    }
    /**
     * Get captcha image directory
     *
     * @return string
     */
    public function get_img_dir()
    {
        return $this->img_dir;
    }
    /**
     * Get captcha image base URL
     *
     * @return string
     */
    public function get_img_url()
    {
        return $this->img_url;
    }
    /**
     * Get captcha image file suffix
     *
     * @return string
     */
    public function get_suffix()
    {
        return $this->suffix;
    }
    /**
     * Get captcha image width
     *
     * @return int
     */
    public function get_width()
    {
        return $this->width;
    }
    /**
     * @param string $startImage
     * @return Image Provides a fluent interface
     */
    public function set_start_image($start_image)
    {
        $this->start_image = $start_image;
        return $this;
    }
    /**
     * @param int $dotNoiseLevel
     * @return Image Provides a fluent interface
     */
    public function set_dot_noise_level($dot_noise_level)
    {
        $this->dot_noise_level = $dot_noise_level;
        return $this;
    }
    /**
     * @param int $lineNoiseLevel
     * @return Image Provides a fluent interface
     */
    public function set_line_noise_level($line_noise_level)
    {
        $this->line_noise_level = $line_noise_level;
        return $this;
    }
    /**
     * Set captcha expiration
     *
     * @param  int $expiration
     * @return Image Provides a fluent interface
     */
    public function set_expiration($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }
    /**
     * Set garbage collection frequency
     *
     * @param  int $gcFreq
     * @return Image Provides a fluent interface
     */
    public function set_gc_freq($gc_freq)
    {
        $this->gc_freq = $gc_freq;
        return $this;
    }
    /**
     * Set captcha font
     *
     * @param  string $font
     * @return Image Provides a fluent interface
     */
    public function set_font($font)
    {
        $this->font = $font;
        return $this;
    }
    /**
     * Set captcha font size
     *
     * @param  int $fsize
     * @return Image Provides a fluent interface
     */
    public function set_font_size($fsize)
    {
        $this->fsize = $fsize;
        return $this;
    }
    /**
     * Set captcha image height
     *
     * @param  int $height
     * @return Image Provides a fluent interface
     */
    public function set_height($height)
    {
        $this->height = $height;
        return $this;
    }
    /**
     * Set captcha image storage directory
     *
     * @param  string $imgDir
     * @return Image Provides a fluent interface
     */
    public function set_img_dir($img_dir)
    {
        $this->img_dir = rtrim($img_dir, '/\\') . '/';
        return $this;
    }
    /**
     * Set captcha image base URL
     *
     * @param  string $imgUrl
     * @return Image Provides a fluent interface
     */
    public function set_img_url($img_url)
    {
        $this->img_url = rtrim($img_url, '/\\') . '/';
        return $this;
    }
    /**
     * @param string $imgAlt
     * @return Image Provides a fluent interface
     */
    public function set_img_alt($img_alt)
    {
        $this->img_alt = $img_alt;
        return $this;
    }
    /**
     * Set captcha image filename suffix
     *
     * @param  string $suffix
     * @return Image Provides a fluent interface
     */
    public function set_suffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }
    /**
     * Set captcha image width
     *
     * @param  int $width
     * @return Image Provides a fluent interface
     */
    public function set_width($width)
    {
        $this->width = $width;
        return $this;
    }
    /**
     * Generate random frequency
     *
     * @return float
     */
    protected function random_freq()
    {
        return mt_rand(700000, 1000000) / 15000000;
    }
    /**
     * Generate random phase
     *
     * @return float
     */
    protected function random_phase()
    {
        // random phase from 0 to pi
        return mt_rand(0, 3141592) / 1000000;
    }
    /**
     * Generate random character size
     *
     * @return float|int
     */
    protected function random_size()
    {
        return mt_rand(300, 700) / 100;
    }
    /**
     * Generate captcha
     *
     * @return string captcha ID
     */
    #[Override]
    public function generate()
    {
        $id = parent::generate();
        $tries = 5;
        // If there's already such file, try creating a new ID
        while ($tries-- && file_exists($this->get_img_dir() . $id . $this->get_suffix())) {
            $id = $this->generate_random_id();
            $this->set_id($id);
        }
        $this->generate_image($id, $this->get_word());
        if (mt_rand(1, $this->get_gc_freq()) === 1) {
            $this->gc();
        }
        return $id;
    }
    /**
     * Generate image captcha
     *
     * Override this function if you want different image generator
     * Wave transform from http://www.captcha.ru/captchas/multiwave/
     *
     * @param string $id Captcha ID
     * @param string $word Captcha word
     * @throws Exception\NoFontProvidedException If no font was set.
     * @throws Exception\ImageNotLoadableException If start image cannot be loaded.
     * @return void
     */
    protected function generate_image($id, $word)
    {
        $font = $this->get_font();
        if (empty($font)) {
            throw new Exception\No_Font_Provided_Exception('Image CAPTCHA requires font');
        }
        $w = $this->get_width();
        $h = $this->get_height();
        $fsize = $this->get_font_size();
        $img_file = $this->get_img_dir() . $id . $this->get_suffix();
        if (empty($this->start_image)) {
            $img = imagecreatetruecolor($w, $h);
        } else {
            // Potential error is change to exception
            Error_Handler::start();
            $img = imagecreatefrompng($this->start_image);
            $error = Error_Handler::stop();
            if (!$img || $error) {
                throw new Exception\Image_Not_Loadable_Exception("Can not load start image '{$this->start_image}'", 0, $error);
            }
            $w = imagesx($img);
            $h = imagesy($img);
        }
        $text_color = imagecolorallocate($img, 0, 0, 0);
        $bg_color = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bg_color);
        $textbox = imageftbbox($fsize, 0, $font, $word);
        $x = ($w - ($textbox[2] - $textbox[0])) / 2;
        $y = ($h - ($textbox[7] - $textbox[1])) / 2;
        $x = (int) $x;
        $y = (int) $y;
        imagefttext($img, $fsize, 0, $x, $y, $text_color, $font, $word);
        // generate noise
        for ($i = 0; $i < $this->dot_noise_level; $i++) {
            imagefilledellipse($img, mt_rand(0, $w), mt_rand(0, $h), 2, 2, $text_color);
        }
        for ($i = 0; $i < $this->line_noise_level; $i++) {
            imageline($img, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $text_color);
        }
        // transformed image
        $img2 = imagecreatetruecolor($w, $h);
        $bg_color = imagecolorallocate($img2, 255, 255, 255);
        imagefilledrectangle($img2, 0, 0, $w - 1, $h - 1, $bg_color);
        // apply wave transforms
        $freq1 = $this->random_freq();
        $freq2 = $this->random_freq();
        $freq3 = $this->random_freq();
        $freq4 = $this->random_freq();
        $ph1 = $this->random_phase();
        $ph2 = $this->random_phase();
        $ph3 = $this->random_phase();
        $ph4 = $this->random_phase();
        $szx = $this->random_size();
        $szy = $this->random_size();
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $sx = $x + (sin($x * $freq1 + $ph1) + sin($y * $freq3 + $ph3)) * $szx;
                $sy = $y + (sin($x * $freq2 + $ph2) + sin($y * $freq4 + $ph4)) * $szy;
                $sx = (int) $sx;
                $sy = (int) $sy;
                if ($sx < 0) {
                    continue;
                }
                if ($sy < 0) {
                    continue;
                }
                if ($sx >= $w - 1) {
                    continue;
                }
                if ($sy >= $h - 1) {
                    continue;
                }
                $color = imagecolorat($img, $sx, $sy) >> 16 & 0xff;
                $color_x = imagecolorat($img, $sx + 1, $sy) >> 16 & 0xff;
                $color_y = imagecolorat($img, $sx, $sy + 1) >> 16 & 0xff;
                $color_xy = imagecolorat($img, $sx + 1, $sy + 1) >> 16 & 0xff;
                if ($color === 255 && $color_x === 255 && $color_y === 255 && $color_xy === 255) {
                    // ignore background
                    continue;
                }
                if ($color === 0 && $color_x === 0 && $color_y === 0 && $color_xy === 0) {
                    // transfer inside of the image as-is
                    $newcolor = 0;
                } else {
                    // do antialiasing for border items
                    $frac_x = $sx - floor($sx);
                    $frac_y = $sy - floor($sy);
                    $frac_x1 = 1 - $frac_x;
                    $frac_y1 = 1 - $frac_y;
                    $newcolor = $color * $frac_x1 * $frac_y1 + $color_x * $frac_x * $frac_y1 + $color_y * $frac_x1 * $frac_y + $color_xy * $frac_x * $frac_y;
                }
                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, (int) $newcolor, (int) $newcolor, (int) $newcolor));
            }
        }
        // generate noise
        for ($i = 0; $i < $this->dot_noise_level; $i++) {
            imagefilledellipse($img2, mt_rand(0, $w), mt_rand(0, $h), 2, 2, $text_color);
        }
        for ($i = 0; $i < $this->line_noise_level; $i++) {
            imageline($img2, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $text_color);
        }
        imagepng($img2, $img_file);
    }
    /**
     * Remove old files from image directory
     *
     * @return void
     */
    protected function gc()
    {
        $expire = time() - $this->get_expiration();
        $imgdir = $this->get_img_dir();
        if (!$imgdir || strlen($imgdir) < 2) {
            // safety guard
            return;
        }
        $suffix_length = strlen($this->suffix);
        foreach (new Directory_Iterator($imgdir) as $file) {
            if ($file->is_dot()) {
                continue;
            }
            if ($file->is_dir()) {
                continue;
            }
            if (!file_exists($file->get_pathname())) {
                continue;
            }
            if (!($file->get_m_time() < $expire)) {
                continue;
            }
            // only deletes files ending with $this->suffix
            if (substr($file->get_filename(), -$suffix_length) !== $this->suffix) {
                continue;
            }
            Error_Handler::start();
            unlink($file->get_pathname());
            Error_Handler::stop();
        }
    }
    /**
     * Get helper name used to render captcha
     */
    #[Override]
    public function get_helper_name(): string
    {
        return 'captcha/image';
    }
}