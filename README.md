[![Latest Stable Version](https://poser.pugx.org/moxio/captainhook-eslint/v/stable)](https://packagist.org/packages/moxio/captainhook-eslint)

moxio/captainhook-eslint
========================
This project is a plugin for [CaptainHook](https://github.com/captainhookphp/captainhook) to check JavaScript files
using [ESLint](https://eslint.org/) in a git pre-commit hook. By default all `*.js` and `*.mjs` files are checked, and
the commit is blocked when one or more errors are found. Warnings produced by ESLint are ignored.

Installation
------------
Install as a development dependency using composer:
```
$ composer require --dev moxio/captainhook-eslint
```

Usage
-----
Add ESLint validation as a `pre-commit` to your `captainhook.json` configuration file:
```json
{
    "pre-commit": {
        "enabled": true,
        "actions": [
            {
                "action": "\\Moxio\\CaptainHook\\ESLint\\ESLintAction"
            }
        ]
    }
}
```

The action expects [ESLint](https://eslint.org/) to be installed as a local NPM package (i.e. available at
`node_modules/.bin/eslint`). It should be [configured](https://eslint.org/docs/user-guide/configuring#configuring-eslint)
in a way that automatically finds the appropriate configuration, e.g. as an `.eslintrc.*` file or with the `eslintConfig`
field in `package.json`.

### Conditional usage
If you want to perform ESLint validation only when ESLint is installed (i.e. available at `node_modules/.bin/eslint`),
you can add a corresponding condition to the action:
```json
{
    "pre-commit": {
        "enabled": true,
        "actions": [
            {
                "action": "\\Moxio\\CaptainHook\\ESLint\\ESLintAction",
                "conditions": [
                    {
                        "exec": "\\Moxio\\CaptainHook\\ESLint\\Condition\\ESLintInstalled"
                    }
                ]
            }
        ]
    }
}
```
This may be useful in scenarios where you have a shared CaptainHook configuration file that is
[included](https://captainhookphp.github.io/captainhook/configure.html#includes) both in projects that use ESLint and
projects that don't. If ESLint is installed, the action is run. In projects without ESLint, the validation is skipped.

### Configuring checked file extensions

By default, committed files with a `.js` or `.mjs` extension will be checked. If you want to customize this,
e.g. to also validate TypeScript files, you can do so with the `extensions` option, which accepts an array
with extensions of files to lint:
```json
{
    "pre-commit": {
        "enabled": true,
        "actions": [
            {
                "action": "\\Moxio\\CaptainHook\\ESLint\\ESLintAction",
				"options": {
					"extensions": [ "js", "mjs", "ts", "tsx" ]
				}
            }
        ]
    }
}
```

Versioning
----------
This project adheres to [Semantic Versioning](http://semver.org/).

License
-------
This project is released under the MIT license.
