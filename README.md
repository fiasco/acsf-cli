# acsf-cli
CLI tool for talking to the Acquia Site Factory API

## Installation

Git clone and [composer](https://getcomposer.org/) install

```
git clone https://github.com/fiasco/acsf-cli.git
cd acsf-cli
composer install
```

Create a config file in the repository root named based on the site factory endpoint you which to query. For example, if I wanted to query a docroot called `hypercar` on the `01live` stack, the configuration file name would be `hypercar01live.conf.yml`.

Inside the config file, use the following yaml structure:

```yaml
endpoint: https://www.hypercar.acsitefactory.com/api/v1/
credentials:
  username: <Your factory username>
  key: <Your factory API key>
```

## Usage

Use the `sf-cli` executable to run commands against the API. See `sf-cli list` for a list of commands available.

Example:

```
./sf-cli sites:list hypercar01live
```

**Note**: Because config files are per stack, you can use the sf-cli tool to talk to many ACSF API endpoints based on the stack reference you specify including dev and staging environments.
