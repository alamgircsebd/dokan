# Dokan Pro

Premium version of Dokan

## Instalation

```bash
git clone git@github.com:weDevsOfficial/dokan-pro.git dokan-pro
cd dokan-pro
composer install
npm install
```

## Dev

* To fix coding standard automatically via `php-cs-fixer`, run `php-cs-fixer fix /path/to/file.php`
* To check for PHP Codesniffer, run: `./vendor/bin/phpcs /path/to/file`
* Auto fix PHPCS fixable issues: `./vendor/bin/phpcbf /path/to/file`

### Vue Development

* Run `npm run dev` to run webpack watch mode.

## To Release

1. Bump the versions in `package.json`
1. Add changelogs to `changelog.txt` file
1. Run: `npm run build`
2. If you have used `DOKAN_PRO_SINCE` in comments, run `npm run version` to replace with the current version from `package.json`
3. Generate pot file: `npm run makepot`
1. To generate zip files: `npm run zip`. It'll generate zip files for each package in the `/build` directory.
2. Run `npm run clean`, it'll clean the zips from the build directory.


### Links

* [Free Version Github](https://github.com/weDevsOfficial/dokan)
* [WordPress.org](https://wordpress.org/plugins/dokan-lite/)
* [Landing Page](https://wedevs.com/dokan/)
