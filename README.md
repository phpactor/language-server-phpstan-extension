LanguageServerPhpstan
=====================

[![Build Status](https://travis-ci.org/phpactor/language-server-phpstan-extension.svg?branch=master)](https://travis-ci.org/phpactor/language-server-phpstan-extension)

Phpstan Language Server and [Phpactor](https://github.com/phpactor/phpactor) Extension.

Provides [Language
Server](https://microsoft.github.io/language-server-protocol/specification) diagnostics from [Phpstan](https://phpstan.org/).

Usage
-----

### Phpactor Extension

If you are using the Phpactor Language Server

```
$ phpactor extension:install "phpactor/language-server-phpstan"
```

### Standalone

Manually install it:

```
$ git clone git@github.com:phpactor/language-server-phpstan-extension some/path
$ cd language-server-phpstan-extension
$ composer install
```

The process of enabling the server with your client will vary. If you are
using VIM and [CoC](https://github.com/neoclide/coc.nvim) it will look
something like (`:CocConfig`):

```
{
    "languageserver": {
        "phpstan": {
            "enable": true,
            "revealOutputChannelOn": "never",
            "command": "/some/path/bin/phpstan-ls",
            "args": ["language-server"],
            "filetypes": ["php"]
        }
    }
}
```

PHPStan Configuration
---------------------

The extension depends on having a `phpstan.neon` which defines your projects
`level` and analysis `paths` e.g.:

```
# phpstan.neon
parameters:
    level: 7
    paths: [ src ]
```

Configuration
-------------

- `language_server_phpstan.bin`: Relative or absolute path to Phpstan. Default
  is `'%project_root%/vendor/bin/phpstan'`
