# Team23 EmailAttachments Extension

Add (PDF) attachments to transactional mails.

## Description

Configure all attachments for all transactional email types in Magento backend, and they will be added 
automatically to the mail. 

## Installation details

Installation is done via composer

### Add composer registry

```shell
composer config repositories.git.team23.de/171 '{"type": "composer", "url": "https://git.team23.de/api/v4/group/171/-/packages/composer/packages.json"}'
```

### Install package
```shell
composer require team23/module-email-attachments
```

Use following commands:

```shell
bin/magento module:enable Team23_EmailAttachments
bin/magento setup:upgrade
```
