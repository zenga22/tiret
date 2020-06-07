
**Tiret** is a simple frontend for the Amazon S3 storage service, designed to expose large
quantity of files to a large number of users.

# Features

* users divided into groups
* possible roles: general administrator, group administrator and user
* private folders for users and common folders for groups
* only administrators can create and remove files
* auto-assignment of uploaded files based on their name
* tracking status of sent emails
* importing users from CSV files
* possibility of customizing the graphic theme

# Requirements

* PHP >= 5.5.9
* composer ( https://getcomposer.org/ )
* an account AWS ( http://aws.amazon.com/ )
* a webserver and a database

# Installation

```
git clone https://github.com/zenga/tiret
cd tiret
composer install
cp .env.example .env
(edit .env with your database, S3 and SMTP access parameters)
php artisan migrate
php artisan db:seed
php artisan key:generate
```

The default credentials are username: admin, password: cippalippa

# Auto-Assignment

You can make sure that files arbitrarily uploaded to _storage / app_ are assigned
to users or groups according to their name. From the panel "_Assignment Rules_" are specified
your regular expressions, with which to extract the name of the destination folder.

Laravel's internal scheduler must be enabled for the assignment routine to come
executed: to do this, use the cron command `php artisan schedule: run` every
minute.

By defining the `ADMIN_NOTIFY_MAIL = example @ test.com` parameter in` .env`, the defined email address
will receive a short report on the status of assignment activities.

# Mail tracking

If emails are forwarded via Amazon SES, an SNS topic can be registered
subscribe to the URL `http: // mydomain.com / mail / status` to track the status of messages in
Exit.

Once the `TRACK_MAIL_STATUS = true` parameter is defined in` .env`, the system will
keep the history of the emails managed, and in some cases will be able to try a new one
forwarding if unsuccessful.

# Plugins

A simple mechanism is provided to implement custom functionality within the
application, for more details see the example in app / Plugins / SampleFileHandler.php

The events that have existed so far, for which plugins can register, are:

** FileToHandle **: launched by the file auto-assignment command, it allows you to intercept
special files and treat them separately. We recommend that you delete or move the file if necessary
processed by the folder intended for uploads.

# Themes

The graphic aspect of ** Tiret ** is deliberately simple and limited, to allow for easy
customization and easy adaptation to the look'n'feel of existing sites.

To customize the look and feel you can create a new file in
_public / themes / name_of_theme / views / app.blade.php_, containing your own base template
inside which the content of the page will be injected. Take the file as an example
_resources / views / app.blade.php_.

Once the theme has been created, you can activate it by changing its configuration in
_config / themes.php_

# History

** Tiret ** was initially developed for a tax assistance agency with the need
to distribute and display payroll documents to their 6000+ customers, for a total of
several gigabytes per month.

The Piedmontese name _tirét_ stands for _drawer_.

# License

** Tiret ** is distributed under AGPLv3 license.

Copyright (C) 2015 Officine Digitali <info@officinedigitali.org>.
