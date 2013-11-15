# Tagaliser

Analyses the pull requests made against tags in a Github repository, and then updates the release notes for the tag.

## Installation

Clone this repository or download the [zip](https://github.com/eddieajau/tagaliser/archive/master.zip).

From the root folder where `composer.json` is located, install the Composer dependencies:

```bash
$ composer install
```

Copy the `/etc/config.dist.json` file to `/etc/config.json`.

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
           --dry-run      Runs the application without adding any data.

Examples:  php -f tagaliser.php -h
           php -f tagaliser.php -- --user=foo --repo=bar
```

## Example Results

[https://github.com/eddieajau/tagaliser/releases/tag/v1.2](https://github.com/eddieajau/tagaliser/releases/tag/v1.2)
