# Tagaliser

Analyses the pull requests made against tags in a Github repository, and then updates the release notes for the tag.

## Installation

Clone this repository or download the [zip](https://github.com/eddieajau/tagaliser/archive/master.zip).

From the root folder where `composer.json` is located, install the Composer dependencies:

```bash
$ composer install
```

Copy the `/etc/config.dist.json` file to `/etc/config.json`. Update the default profile as required and/or add new profiles.

## Usage

```bash
$ php -f bin/tagaliser.php -- --help

Tagaliser 2.0

Usage:     php -f tagaliser.php -- [switches]

Switches:  -h | --help    Prints this usage information.
           --user         The name of the Github user (associated with the repository).
           --repo         The name of the Github repository.
           --username     Your Github login username.
           --password     Your Github login password.
           --tag          Specifies a single tag to update.
           --dry-run      Runs the application without adding any data.
                          Use "Not tagged" to get the list of changes for the next tag.
           --profile      Use a connection profile from the configuration file.

Examples:  php -f tagaliser.php -h
           php -f tagaliser.php -- --user=foo --repo=bar
           php -f tagaliser.php -- --tag=v1.0
           php -f tagaliser.php -- --tag="Not tagged"
           php -f tagaliser.php -- --dry-run
           php -f tagaliser.php -- --profile=work
```

## Using the phar executable

To make the phar file:

```bash
$ phing phar
```

This will create `/build/tagaliser.phar`. The phar file expects to find a configuration file called `tagaliser.json`
in the same folder.

## Example Results

[https://github.com/eddieajau/tagaliser/releases/tag/v1.2](https://github.com/eddieajau/tagaliser/releases/tag/v1.2)
