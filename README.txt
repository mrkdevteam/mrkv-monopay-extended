=== Morkva Monobank Extended ===
Contributors: bandido
Plugin Name: Morkva Monobank Extended
Tags: Mono, MonoPay, Моно, Монопей, Монобанк, Monobank
Tested up to: 6.3
Stable tag: 0.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Платіжний модуль MonoPay з callback.

== Description ==

Функціонал плагіну:


* можливість додати спосіб оплати MonoPay

* можливість додати опис способу оплати

* можливість додати ключ API

* callback при success i non-success трансакціях (відповідно статус замовлення буде або processing або cancelled)

* валюта лише гривня


Отринати тестовий ключ АРІ можна тут: https://api.monobank.ua



Потрібна підтримка чи додатковий функціонал? support@morkva.co.ua

= 0.3.1 =
* [fix] виправили виклик властивостей товарів
* [fix] виправили помилку у ціні товарів у запиті 

= 0.3.0 =
* [new] додали підтримку High-Performance Order Storage (HPOS)

= 0.2.2 =
* змінили параметр x-cms
* допрацювання UI

= 0.2.0 =
* [new] перевірено сумісність з WordPress 6.3

= 0.2.0 =
* [fixed] поправили обробку callback

= 0.1.1 =
* [fixed] поправили розмір дефолтного зображення монобанку
* перевірили сумісність з WooCommerce 7.8

= 0.1.0 =
* [fixed] поправили callback, тепер він працює на сторінці thank you
* [new] додали підтримку USD, EUR (банк все рівно проведе конвертацію у гривні за своїм курсом)
* [new] одали можливість додати іконку до назви оплаты
* [new] замінити тип поля Description
* [new] додали поле code до схеми товару

= 0.0.1 =
* реліз плагіна