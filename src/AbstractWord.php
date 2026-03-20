<?php

declare (strict_types=1);
namespace Laminas\Captcha;

use function class_exists;
use function count;
use function is_array;
use Laminas\Session\Container;
use function md5;
use Override;
use function preg_match;
use function random_bytes;
use function random_int;
use function strlen;
use function strtolower;
use function substr;
/**
 * AbstractWord-based captcha adapter
 *
 * Generates random word which user should recognise
 */
abstract class Abstract_Word extends Abstract_Adapter
{
    // @codingStandardsIgnoreStart
    /**#@+
     * @var array Character sets
     */
    /** @var list<string> */
    public static $V = ['a', 'e', 'i', 'o', 'u', 'y'];
    /** @var list<string> */
    public static $VN = ['a', 'e', 'i', 'o', 'u', 'y', '2', '3', '4', '5', '6', '7', '8', '9'];
    /** @var list<string> */
    public static $C = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'z'];
    /** @var list<string> */
    public static $CN = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'z', '2', '3', '4', '5', '6', '7', '8', '9'];
    /**#@-*/
    // @codingStandardsIgnoreEnd
    /**
     * Random session ID
     *
     * @var string|null
     */
    protected $id;
    /**
     * Generated word
     *
     * @var string|null
     */
    protected $word;
    /**
     * Session
     *
     * @var Container|null
     */
    protected $session;
    /**
     * Class name for sessions
     *
     * @var class-string<Container>
     */
    protected $session_class = Container::class;
    /**
     * Should the numbers be used or only letters
     *
     * @var bool
     */
    protected $use_numbers = true;
    /**
     * Should both cases be used or only lowercase
     *
     * @var bool
     */
    // protected $useCase = false;
    /**
     * Session lifetime for the captcha data
     *
     * @var int
     */
    protected $timeout = 300;
    /**
     * Should generate() keep session or create a new one?
     *
     * @var bool
     */
    protected $keep_session = false;
    /**#@+
     * Error codes
     */
    public const MISSING_VALUE = 'missingValue';
    public const MISSING_ID = 'missingID';
    public const BAD_CAPTCHA = 'badCaptcha';
    /**#@-*/
    /**
     * Error messages
     *
     * @var array<string, string>
     */
    protected $message_templates = [self::MISSING_VALUE => 'Empty captcha value', self::MISSING_ID => 'Captcha ID field is missing', self::BAD_CAPTCHA => 'Captcha value is wrong'];
    /**
     * Length of the word to generate
     *
     * @var int
     */
    protected $wordlen = 8;
    /**
     * Retrieve session class to utilize
     *
     * @return string
     */
    public function get_session_class()
    {
        return $this->session_class;
    }
    /**
     * Set session class for persistence
     *
     * @param  class-string $sessionClass
     * @return AbstractWord Provides a fluent interface
     */
    public function set_session_class($session_class)
    {
        $this->session_class = $session_class;
        return $this;
    }
    /**
     * Retrieve word length to use when generating captcha
     *
     * @return int
     */
    public function get_wordlen()
    {
        return $this->wordlen;
    }
    /**
     * Set word length of captcha
     *
     * @param int $wordlen
     * @return AbstractWord Provides a fluent interface
     */
    public function set_wordlen($wordlen)
    {
        $this->wordlen = $wordlen;
        return $this;
    }
    /**
     * Retrieve captcha ID
     *
     * @return string
     */
    public function get_id()
    {
        if (null === $this->id) {
            $this->id = $this->generate_random_id();
        }
        return $this->id;
    }
    /**
     * Set captcha identifier
     *
     * @param string $id
     * @return AbstractWord Provides a fluent interface
     */
    protected function set_id($id)
    {
        $this->id = $id;
        return $this;
    }
    /**
     * Set timeout for session token
     *
     * @param  int $ttl
     * @return AbstractWord Provides a fluent interface
     */
    public function set_timeout($ttl)
    {
        $this->timeout = (int) $ttl;
        return $this;
    }
    /**
     * Get session token timeout
     *
     * @return int
     */
    public function get_timeout()
    {
        return $this->timeout;
    }
    /**
     * Sets if session should be preserved on generate()
     *
     * @param bool $keepSession Should session be kept on generate()?
     * @return AbstractWord Provides a fluent interface
     */
    public function set_keep_session($keep_session)
    {
        $this->keep_session = $keep_session;
        return $this;
    }
    /**
     * Numbers should be included in the pattern?
     *
     * @return bool
     */
    public function get_use_numbers()
    {
        return $this->use_numbers;
    }
    /**
     * Set if numbers should be included in the pattern
     *
     * @param  bool $useNumbers numbers should be included in the pattern?
     * @return AbstractWord Provides a fluent interface
     */
    public function set_use_numbers($use_numbers)
    {
        $this->use_numbers = $use_numbers;
        return $this;
    }
    /**
     * Get session object
     *
     * @throws Exception\InvalidArgumentException
     * @return Container
     */
    public function get_session()
    {
        if (!isset($this->session)) {
            $id = $this->get_id();
            if (!class_exists($this->session_class)) {
                throw new Exception\InvalidArgumentException("Session class {$this->session_class} not found");
            }
            $this->session = new $this->session_class('Laminas_Form_Captcha_' . $id);
            $this->session->set_expiration_hops(1, null);
            $this->session->set_expiration_seconds($this->get_timeout());
        }
        return $this->session;
    }
    /**
     * Set session namespace object
     *
     * @return $this Provides a fluent interface
     */
    public function set_session(Container $session)
    {
        $this->session = $session;
        $this->keep_session = true;
        return $this;
    }
    /**
     * Get captcha word
     *
     * @return string
     */
    public function get_word()
    {
        if (empty($this->word)) {
            $session = $this->get_session();
            $this->word = $session->word;
        }
        return $this->word;
    }
    /**
     * Set captcha word
     *
     * @param  string $word
     * @return AbstractWord Provides a fluent interface
     */
    protected function set_word($word)
    {
        $session = $this->get_session();
        $session->word = $word;
        $this->word = $word;
        return $this;
    }
    /**
     * Generate new random word
     *
     * @return string
     */
    protected function generate_word()
    {
        $word = '';
        $word_len = $this->get_word_len();
        $vowels = $this->use_numbers ? static::$VN : static::$V;
        $consonants = $this->use_numbers ? static::$CN : static::$C;
        $tot_index_con = count($consonants) - 1;
        $tot_index_vow = count($vowels) - 1;
        for ($i = 0; $i < $word_len; $i += 2) {
            // generate word with mix of vowels and consonants
            $consonant = $consonants[random_int(0, $tot_index_con)];
            $vowel = $vowels[random_int(0, $tot_index_vow)];
            $word .= $consonant . $vowel;
        }
        if (strlen($word) > $word_len) {
            return substr($word, 0, $word_len);
        }
        return $word;
    }
    /**
     * Generate new session ID and new word
     *
     * @return string session ID
     */
    #[Override]
    public function generate()
    {
        if (!$this->keep_session) {
            $this->session = null;
        }
        $id = $this->generate_random_id();
        $this->set_id($id);
        $word = $this->generate_word();
        $this->set_word($word);
        return $id;
    }
    /**
     * Generate a random identifier
     *
     * @return string
     */
    protected function generate_random_id()
    {
        return md5(random_bytes(32));
    }
    /**
     * Validate the word
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
        if (!is_array($value)) {
            if (!is_array($context)) {
                $this->error(self::MISSING_VALUE);
                return false;
            }
            $value = $context;
        }
        $name = $this->get_name();
        if (isset($value[$name])) {
            $value = $value[$name];
        }
        if (!isset($value['input'])) {
            $this->error(self::MISSING_VALUE);
            return false;
        }
        $input = strtolower((string) $value['input']);
        $this->set_value($input);
        if (!isset($value['id']) || !preg_match('/^[a-f0-9][a-f0-9_\\\\]+$/i', (string) $value['id'])) {
            $this->error(self::MISSING_ID);
            return false;
        }
        $this->id = $value['id'];
        if ($input !== $this->get_word()) {
            $this->error(self::BAD_CAPTCHA);
            return false;
        }
        //Invalidate the captcha by generating a new word after successful use
        $this->set_word($this->generate_word());
        return true;
    }
    /**
     * Get helper name used to render captcha
     *
     * @return string
     */
    #[Override]
    public function get_helper_name()
    {
        return 'captcha/word';
    }
}