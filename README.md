#Memorable Passwords

A utility PHP class for generating easily-memorable yet secure passwords (with a slight UK bias if using place names).

The class can also just generate a memorable word, for example, to populate a CAPTCHA image.

Check out the class constants to see how the output can be modified.

##Usage

All examples assume:

~~~~
use voidstate\MemorablePassword;
~~~~

**Memorable password with 2 digits and one capitalised letter:**

~~~~
$memorablePassword = new MemorablePassword( 2, MemorablePassword::CAPITALISE_MODE_ONE );
$password = $memorablePassword->generate();
~~~~

**Memorable password with 2 digits and one capitalised letter, only using UK place names:**

~~~~
$memorablePassword = MemorablePassword( 1, MemorablePassword::CAPITALISE_MODE_ONE, MemorablePassword::WORD_LIST_MODE_ONLY_UK );
$password = $memorablePassword->generate();
~~~~

**Word with length of up to 10 characters:**

~~~~
$memorablePassword = MemorablePassword();
$password = $memorablePassword->getWord( 10 );
~~~~