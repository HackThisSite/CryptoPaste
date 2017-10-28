<img src="public/img/cryptopaste.png" align="left">
<h1>CryptoPaste</h1>
A secure, browser-side encrypted pastebin.
<a href="https://travis-ci.org/HackThisCode/CryptoPaste" title="Travis-CI Build Status"><img src="https://travis-ci.org/HackThisCode/CryptoPaste.svg?branch=master"></a> <a href='https://www.versioneye.com/user/projects/59f514dd2de28c1e03d950b2'><img src='https://www.versioneye.com/user/projects/59f514dd2de28c1e03d950b2/badge.svg?style=flat-square' alt="Dependency Status" /></a>

# About

CryptoPaste is a secure pastebin service inspired by [CryptoBin](https://cryptobin.org). Like CryptoBin, CryptoPaste strives to be a secure, stable and clean pastebin service, especially now that CryptoBin has seemingly shut its doors indefinitely.  The goal is to perform all encryption, decryption, and data handling in the user's browser so that the CryptoPaste host has both plausible deniability and the inability to comply with court orders or takedown requests.

CryptoPaste is a [HackThisSite](https://www.hackthissite.org) project.

# Features
- Pastes are encrypted before being sent to the server
- No passwords stored
- All identifying information is anonymized
- Expired content is deleted forever
- CRON for enforced expiration

# Demonstration
An active demonstration of CryptoPaste can be found at https://cryptopaste.org

# TODO
- (**Need Help!**) Write legitimate testing
- (**Need Help!**) Tidy up all code
- (**Need Help!**) Fix the UI to have better responsive scaling and other improvements

# Prerequisites
- PHP 7
- Composer
- MySQL or SQLite

# Install

1. Clone or download this repository, then `cd` into the directory, and run

`$ composer install`

2. Install `resources/cryptopaste.mysql.sql` into your MySQL database and create a user with SELECT, INSERT, UPDATE, and DELETE grants
   - For SQLite, use the `resources/cryptopaste.sqlite.sql` file

3. Copy `config.ini.example` to `config.ini` and edit the values

4. Edit your `nginx.conf` and add this to your `http` block:

<pre>
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
</pre>

5. In your `nginx.conf`, in the `server` block, this is all you need to run the CryptoPaste app:

<pre>
      location ~ /securimage/(images/.*|securimage(_play\.swf|\.js|\.css))$ {
        try_files $uri $uri/ =404;
        alias /var/www/cryptopaste/vendor/dapphp;
      }

      location / {
        try_files $uri /index.php$is_args$args;
      }

      location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php-fpm.sock; # Change this to reflect how your PHP-FPM is running
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_script_name;
        fastcgi_param SERVER_NAME $host;
      }
</pre>

6. Add a CRON entry to force deletion of expired pastes.  Here is an example crontab entry that is run every 5 minutes as the `www-data` user:

<pre>
*/5	*	*	*	*	www-data	/usr/bin/php /var/www/cryptopaste/src/cron.php >> /var/log/cryptopaste-cron.log
</pre>

# Upgrade

Any time you upgrade, you must make sure to flush the `cache/twig/` folder of all content (minus the `.gitignore` file, of course).

# License

CryptoPaste is licensed under the **GNU General Public License v3.0**. More details are in the [LICENSE](LICENSE) file.

# Acknowledgements

CryptoPaste uses the following technologies:
* Sensio Labs frameworks ([Silex](https://silex.sensiolabs.org), [Symfony components](http://symfony.com/components), [Twig](https://twig.sensiolabs.org))
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
