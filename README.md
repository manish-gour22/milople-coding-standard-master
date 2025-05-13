# Milople Coding Standard

A set of [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) rules for Extensions developed for Milople Technologies.

## Installation within a Magento 2 site

To use within your Magento 2 project you can use:

```bash
composer require --dev manish-gour22/milople-coding-standard
```

### Verify installation

Command should return the list of installed coding standards including MCS.

```bash
vendor/bin/phpcs -i
```

## Usage

Once installed, you can run `phpcs` from the command-line to analyze your code `MyAwesomeExtension`

```bash
vendor/bin/phpcs --standard=MCS app/code/Milople/MyAwesomeExtension
```