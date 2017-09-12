# Memorable Passwords

A utility PHP class for generating easily-memorable yet secure passwords (with a slight UK bias if using place names).

The class can also just generate a memorable word, for example, to populate a CAPTCHA image.

## Passwords

Passwords consist of a single English-language word, with a series of digits added at a random point. Some of the characters may also be capitalised.

These are easier to remember than random sequences, as you only need to remember the word plus how it's changed. For example, *nottingh89aM* might be remembered as: "my password is Nottingham but with 89 after the H and a capital M".

### Examples

* be67tweEn
* dAchs66hund
* ba2826sE
* lonDonder43ry

## Modifying Output

Check out the class constants to see how the output can be modified.

In brief, you can control three aspects of generation:

1. Which word list to use (`setWordListMode` method).
2. How many digits to add (`setDigitCount` method).
3. How many letters to capitalise  (`setCapitaliseMode` method).

## Usage Examples

All examples assume:

````php
use Voidstate\MemorablePassword;
````

**Memorable password with 2 digits and 1 capitalised letter:**

````php
$memorablePassword = new MemorablePassword( 2, MemorablePassword::CAPITALISE_MODE_ONE );
$password = $memorablePassword->generate();
````

**Memorable password with 1 digit and 1 capitalised letter, only using UK place names:**

````php
$memorablePassword = new MemorablePassword( 1, MemorablePassword::CAPITALISE_MODE_ONE, MemorablePassword::WORD_LIST_MODE_ONLY_UK );
$password = $memorablePassword->generate();
````

**Word with length of up to 10 characters:**

````php
$memorablePassword = new MemorablePassword();
$password = $memorablePassword->getWord( 10 );
````

## Versioning

This project uses [SemVer](http://semver.org/). Given a `MAJOR.MINOR.PATCH` version number, we will increment the:
- `MAJOR` version when existing content is changed in such a way that it can break consumers of the data. 
- `MINOR` version when new content is added in a backwards-compatible manner, or existing content is changed in a backwards-compatible manner. 
- `PATCH` version when fixing mistakes in existing content. 

## History

See the [Releases tab](https://github.com/voidstate/memorable-passwords/releases) in Github.

## Contributors

* Voidstate (https://github.com/voidstate)

## License
MIT. See LICENSE.md
