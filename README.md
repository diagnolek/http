Client Http
=================

Komponent do połączeń http oparta o curl.

Opis
-------

Komponent został stworzony w oparciu o zaimplementowane interface z [php-fig][1] by [github][2]
-standard PSR-7

Projekt wykorzystuje wzorzec Facade(DI) i EventDispatcher
-możliwość wstrzyknięcia adaptera i dispatchera

Dostępne metody
---------------

- get, post, put, delete, 
- execWithException -metody wytwórcza, zwraca exception w przypadku wytąpienia błedu
- sendRequest(RequestInterface $request)


Format danych
----------

* form-data
* json

Autoryzacja
----------

* Basic
* Jwt
* Cookie

Testy
------
Do projektowania i testów wykorzystano [phpspec][3]

* `php bin/phpspec run` uruchamia testy



DEMO
-------

`php bin/demo.php search "szukane słowo"` skrypt wyszukuje stronę w wikipedia i zwraca dane (json)

[1]: https://www.php-fig.org/psr/psr-7/
[2]: https://github.com/php-fig/http-message
[3]: http://phpspec.net/en/stable/