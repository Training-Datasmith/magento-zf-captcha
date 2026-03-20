# Architecture: magento-zf-captcha

## Purpose

Magento's fork of Zend Framework's CAPTCHA component. Provides multiple CAPTCHA types (image-based, Figlet ASCII art, reCAPTCHA, and a dumb/development no-op) with a common adapter interface for use in Magento forms.

## Directory Structure

```
src/
  Adapter_Interface.php   — Common CAPTCHA contract: generate(), isValid(), getHelperName()
  Abstract_Adapter.php    — Base implementation: session storage, word generation
  Abstract_Word.php       — Base for text-based CAPTCHAs: word length, character set
  Image.php               — GD-rendered image CAPTCHA with noise lines and fonts
  Figlet.php              — ASCII-art CAPTCHA using Zend_Text_Figlet fonts
  Re_Captcha.php          — Google reCAPTCHA v2 adapter
  Dumb.php                — No-op CAPTCHA for testing/development
  Factory.php             — Creates CAPTCHA instances by type name
  Exception/
    Domain_Exception.php
    Extension_Not_Loaded_Exception.php
    Image_Not_Loadable_Exception.php
    Invalid_Argument_Exception.php
    No_Font_Provided_Exception.php
    Runtime_Exception.php
    Exception_Interface.php
```

## Key Design Decisions

- **Adapter pattern**: All CAPTCHA types implement `Adapter_Interface`, allowing forms to swap implementations without code changes
- **Session-based word storage**: The generated word is stored in the session; `isValid()` compares user input against the session value and clears it after verification
- **Typed exceptions**: Each error condition has its own typed exception, enabling granular catch blocks

## Extension Points

- Implement `Adapter_Interface` to create a custom CAPTCHA type
- Use `Factory::factory($type, $options)` to instantiate from configuration

## Dependency Flow

```
Factory::factory('Image', $options)
  → Image extends Abstract_Word extends Abstract_Adapter implements Adapter_Interface
  → GD extension (image generation)
  → Magento session (word storage)
```
