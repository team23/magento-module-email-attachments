# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

[Unreleased]

### Changed

- Update LICENSE and README
- Magento 2.4.7 compatibility
- Refactor code in favor for PHP 8+

### Added

- Add `CHANGELOG.md` file
- Add `team23/module-core` dependency

### Removed

- Drop PHP 7.4 support

## [2.1.0] - 2024-03-05

### Changed

- Fix sending out transaction mails twice
- Declare all dependencies correct in `module.xml` and `composer.json` files

## [2.0.0] - 2023-06-14

### Changed

- Use `Laminas_Mime` instead of `Zend_Mime`

## [1.1.1] - 2023-03-08

### Changed

- Prevent mails from getting sent twice if non attachment is included

## [1.1.0] - 2022-11-28

### Changed

- Update PHP requirements

## [1.0.0] 2021-12-09

- Public release
