this is a web application representing the gwap-enabler[1] raw implementation for the NightKnights[2] game, developed under the EU H2020 project STARS4ALL[3]

to test it:
a) run the sql scripts under the Db folder to create and populate the MySQL db used by the application
b) copy the gwap-enabler/App folder under the root of a web server to reach it in a browser from http:/localhost/gwap-enabler/app
c) change the connection params in gwap-enabler/App/secret/globals.php
d) install composer[5] and run ``` composer require "spomky-labs/jose:^7.0" ``` from command shell under the gwap-enabler/App/api folder for PHP 7.0+;
   for PHP 5.6 run ``` composer require "spomky-labs/jose:^6.1" ``` instead[6]

to enable a social network login register the app in the corresponding registration page and update the index.html with the corresponding client ID obtained;
for twitter you need to register it also on the HelloJS[4] proxy service at https://auth-server.herokuapp.com/

[1] https://figshare.com/s/b7cf82ff5dbdde50395d
[2] https://figshare.com/s/5bd9c9f96c8dcee121b5
[3] http://www.stars4all.eu/
[4] http://adodson.com/hello.js/
[5] https://getcomposer.org/
[6] https://github.com/Spomky-Labs/jose