## project-3.quater-IP
#Školní projekt CRUD
- program na editaci databáze
- je zapotřebí po stáhnutí kódu nainstalovat potřebné balíčky
# Composer
- jako první si stáhněte composer, přes který si stáhnete i ostatní balíčky
- https://getcomposer.org/
#Tracy a mustache
- následuje instalace zbytku balíčků
```
$composer require tracy/tracy
$composer require mustache/mustache
```
#Změna configu 
- ve složce includes najdete LocalConfig.class.example.php
- Změňte si data v ní podle své potřeby (SERVER, USER, PASSWORD) pro připojení k databázi a oddělejte z názvu example
- Následně ještě přidáme v Config.class.php databáz, ke které se chceme připojit.

#Teď už jste takzvaně ready to go.
