<img src="https://github.com/HackThisCode/CryptoPaste/raw/master/public/img/cryptopaste.png" align="left">
<h1>CryptoPaste</h1>
A secure, browser-side pastebin.

# Demonstration
An active demonstration of CryptoPaste can be found at https://cryptopaste.org

# Prerequisites
- PHP 7
- Composer
- MySQL or SQLite

# Install

1. Install `cryptopaste.sql` into your MySQL database and create a user with SELECT, INSERT, UPDATE, and DELETE grants
   - TODO: SQLite installation
   
2. Clone or download this repository, then `cd` into the directory, and run

`$ composer install`

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

# TODO
- (**Need Help!**) Tidy up all code and add testing
- (**Need Help!**) Fix the UI to have better responsive scaling and other improvements
- Finish README
