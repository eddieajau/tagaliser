# Tagaliser

Analyses tags in a Github repository.

## Installation

Clone this repository or download the [zip](https://github.com/eddieajau/tagaliser/archive/master.zip).

From the root folder where `composer.json` is located, install the Composer dependencies:

```bash
$ composer install
```

## Usage

```bash
$ php -f bin/tagaliser.php -- --help

Tagaliser 1.2

Usage:     php -f tagaliser.php -- [switches]

Switches:  -h | --help    Prints this usage information.
           --user         The name of the Github user (associated with the repository).
           --repo         The name of the Github repository.
           --username     Your Github login username.
           --password     Your Github login password.

Examples:  php -f tagaliser.php -h
           php -f tagaliser.php -- --user=foo --repo=bar
```
