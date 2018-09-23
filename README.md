<img src="web/img/cryptopaste.png" align="left">
<h1>CryptoPaste</h1>
A secure, browser-side encrypted pastebin.

[![Build Status](https://travis-ci.org/HackThisSite/CryptoPaste.svg?branch=master)](https://travis-ci.org/HackThisSite/CryptoPaste)

# About

CryptoPaste is a secure pastebin service inspired by [CryptoBin](https://cryptobin.org). Like CryptoBin, CryptoPaste strives to be a secure, stable and clean pastebin service, especially now that CryptoBin has seemingly shut its doors indefinitely. The goal is to perform all encryption, decryption, and data handling in the user's browser so that the CryptoPaste host has both plausible deniability and the inability to comply with court orders or takedown requests.

CryptoPaste is a [HackThisSite](https://www.hackthissite.org) project.

# Features
- Pastes are encrypted in-browser before being sent to the server
- No passwords stored
- All identifying information is anonymized
- Expired content is deleted forever
- Web cron available
- Template override supported

# Demonstration
An active demonstration of CryptoPaste can be found at https://cryptopaste.org

# Prerequisites
- PHP >= 7.0
- Composer
- MySQL / MariaDB

# Install

1. Clone or download this repository, then `cd` into the directory, and run

`$ composer install`

Because CryptoPaste uses the Symfony framework, Composer will automatically prompt you for configuration settings. Unfortunately this does not include definitions in the prompts, so please read the comments in `app/config/parameters.yml.dist` for an explanation of what the configuration settings are.

If you want to change the configuration settings later (i.e. changing to a DB user will limited permissions), they are saved in the `app/config/parameters.yml` file.

2. Install the database using the following command:

`$ php bin/console doctrine:migrations:migrate`

This will install the database schema using the username and password you supplied in step 1. The database user you specified will need enough permissions to create the database and so forth. If you want to specify a different user to do this, edit the `app/config/parameters.yml` file.

3. Edit your `nginx.conf` and make it look something like the following:

<pre>
http {

  # These maps anonymize IP addresses in nginx logs

  map $remote_addr $ip_anonym1 {
   default 0.0.0;
   "~(?P&lt;ip>(\d+)\.(\d+)\.(\d+))\.\d+" $ip;
   "~(?P&lt;ip>[^:]+:[^:]+):" $ip;
  }

  map $remote_addr $ip_anonym2 {
   default .0;
   "~(?P&lt;ip>(\d+)\.(\d+)\.(\d+))\.\d+" .0;
   "~(?P&lt;ip>[^:]+:[^:]+):" ::;
  }

  map $ip_anonym1$ip_anonym2 $ip_anonymized {
   default 0.0.0.0;
   "~(?P&lt;ip>.*)" $ip;
  }

  log_format anonymized '$ip_anonymized - $remote_user [$time_local] '
     '"$request" $status $body_bytes_sent '
     '"$http_referer" "$http_user_agent"';

  access_log /var/log/nginx/access.log anonymized;
}

server {
  listen 80;
  server_name _;
  access_log /var/log/nginx/access.log anonymized;

  root /path/to/cryptopaste/web;

  index app.php;
  location ~ /\.ht {
    deny  all;
  }
  location / {
    try_files $uri /app.php$is_args$args;
  }

  location ~ ^/app\.php(/|$) {
    fastcgi_pass unix:/var/run/php-fpm.sock;
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_script_name;
    fastcgi_param SERVER_NAME $host;
  }
}
</pre>

4. If you chose not to enable the web cron, add an entry to your crontab to ensure deletion of expired pastes and sessions. Here is an example crontab entry that is run every hour as the `www-data` user:

<pre>
0	*	*	*	*	www-data	/usr/bin/php /path/to/cryptopaste/bin/console cron:run
</pre>

# Upgrade

Because Silex is now end-of-life, CryptoPaste v1.0 is a complete recode in the Symfony 3.4 framework. This has resulted in some major changes, but your data should be portable from a v0.x version of CryptoPaste.

## Warning about SQLite

CryptoPaste v1.x no longer supports SQLite. If you were using SQLite, please convert it to a MySQL database, table name of `cryptopaste`, and follow the upgrade procedure below.

## Upgrade from v0.1.x to v1.x

1. Make sure to backup your database and your `config.ini` file! You will need the data from your `config.ini` in the next steps.

2. Delete your old v0.x CryptoPaste installation, but do not delete your database!

3. Follow **all** of the instructions for Installation above, including for cron jobs and nginx settings. When prompted for configuration settings during the `composer install` phase, use the values from your `config.ini`. You will need to provide MySQL credentials for a user that can create and alter tables.

4. When you run the `bin/console doctrine:migrations:migrate` command from the Installation instructions, this will automatically convert your database for CryptoPaste v1.x.

## Upgrade v1.x

1. Make sure you backup your database and `app/config/parameters.yml` file!

2. If you cloned this repository, `cd` into your CryptoPaste installation directory and run `git pull`. If not, you will need to clone or download this repository to a new directory, then move your old `app/config/parameters.yml` file and any custom templates into your that new directory.

3. Next, run

`$ composer install`

Because CryptoPaste uses the Symfony framework, Composer will automatically prompt you for any new configuration settings. Unfortunately this does not include definitions in the prompts, so please read the comments in `app/config/parameters.yml.dist` for an explanation of what the configuration settings are.

If you want to change the configuration settings later (i.e. changing to a DB user will limited permissions), they are saved in the `app/config/parameters.yml` file.

4. Update the database using the following command:

`$ php bin/console doctrine:migrations:migrate`

This will update the database schema using the username and password specified in your `app/config/parameters.yml` file. The database user you specified will need enough permissions to modify the database and so forth. If you want to specify a different user to do this, edit the `app/config/parameters.yml` file.

5. Clear the CryptoPaste application cache by running the following command:

`$ php bin/console cache:clear`

# Modify the template

Note that all template files are written in [Twig 2.0](https://twig.symfony.com). You can find the documentation for it [here](https://twig.symfony.com/doc/2.x/).

## Override default template files

All default template files reside in `app/Resources/views/default/`. If you want to override any file here, simply copy the file to the `custom/` directory one level up and modify as needed. For example, if you want to override the FAQ page, you would copy `app/Resources/views/default/pages/faq.html.twig` to `app/Resources/views/custom/pages/faq.html.twig`.

## Add custom static pages

If you want to add custom static pages, i.e. a privacy policy or terms of service, you can do that quite easily as described below.

### Custom page file

First, create a file in `app/Resources/views/custom/pages/` that must end in `.html.twig`. The part before `.html.twig` will become the page *slug*, or identifier, used in the URL and menu. For example, if you create `app/Resources/views/custom/pages/privacy.html.twig`, then "privacy" becomes the slug.

Two Twig settings must exist within a custom page:

* **extends** - Must be set exactly as shown below in the example
* **content** block - The HTML to display

There are also some optional settings:

* **title** - Page title that shows up in the `<title>` HTML tag
* **breadcrumb** - Breadcrumb that appears on the left side of the top menu bar. Hash-map of two values:
  * **icon** - Fontawesome icon ([reference](https://fontawesome.com/icons))
  * **text** - Text to show in the breadcrumb
* **head** block - HTML content to put just before the `</head>` closing tag
* **footerjs** block - HTML content to put just before the `</body>` closing tag

Shown below is an example of a privacy policy custom page.

<pre>
{% extends ["custom/_template.html.twig", "default/_template.html.twig"] %}
{% set title = "Privacy Policy" %}
{% set breadcrumb = {'icon': 'lock', 'text': 'Privacy Policy'} %}

{% block content %}

... privacy policy HTML ...

{% endblock %}
</pre>

All page URL slugs are prefixed with `/p/`, such as `/p/privacy` for the above privacy policy page (if your CryptoPaste is accessible at `http://cryptopaste/` then this would be `http://cryptopaste/p/privacy`).

If your page filename starts with an underscore (`_`), then the page will not be visible and will return a 404 error.

### Modify the menu

If you want your custom page or an outside URL to be visible in the top menu bar, you will need to create a `app/Resources/views/custom/_menu.yaml.twig` file. You can copy the default `app/Resources/views/default/_menu.yaml.twig` to start and modify as needed. You can also use this to hide the default FAQ page if you don't want to show it.

The `_menu.yaml.html` file contents must start with a `menu` key, and each menu item is a list of two keys. You must have the **name** key set, and either **slug** or **url**:
* **name** - The name to show in the menu
* **slug** - The slug of your custom page (the part of the filename just before the `.html.twig` extension)
* **url** - A URL to point to

Shown below is an example of a custom menu with the default FAQ page, a privacy policy custom page, and a link to an outside website. Note that this list is ordered top-to-bottom == left-to-right. So the menu example below would render the menu as: 'HackThisSite | Privacy Policy | FAQ | New Paste' ('New Paste' is always shown).

<pre>
menu:
    -
        name: 'HackThisSite'
        url: 'https://www.hackthissite.org'
    -
        name: 'Privacy Policy'
        slug: privacy
    -
        name: 'FAQ'
        slug: faq
</pre>

If a menu slug starts with an underscore (`_`), that menu item will be hidden.

If you don't want to show anything in the menu except the 'New Paste' button, simply create an empty `_menu.yaml.twig` file in the `custom/` folder.

# TODO
- (**Need Help!**) Write legitimate testing
- (**Need Help!**) Fix the UI to have better responsive scaling and other improvements

# License

CryptoPaste is licensed under the **GNU General Public License v3.0**. More details are in the [LICENSE](LICENSE) file.

# Acknowledgements

CryptoPaste uses the following technologies:
* [Symfony framework](http://symfony.com)
* [Composer](https://getcomposer.org), [phpUnit](https://phpunit.de)
* Client-side JavaScript libraries and frameworks:
  * [Bootstrap](http://getbootstrap.com) and [jQuery](https://jquery.com)
  * AES encryption and password generation from the [Stanford Javascript Crypto Library](https://crypto.stanford.edu/sjcl/)
  * AES backward compatability by [Moveable Type](http://www.movable-type.co.uk/scripts/aes.html)
  * Syntax highlighting by [Highlight.js](https://highlightjs.org)
  * Password strength testing by [zxcvbn](https://github.com/dropbox/zxcvbn)
  * [smoothscroll polyfill](https://iamdustan.github.io/smoothscroll)
  * [clipboard.js](https://zenorocha.github.io/clipboard.js)
* [Travis-CI](https://travis-ci.org), [CLA Assistant](https://cla-assistant.io)
