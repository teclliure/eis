Easy Invoice System (EIS)
===================

[![Build Status](https://travis-ci.org/teclliure/eis.png)](https://travis-ci.org/teclliure/eis)

Easy Invoice System (EIS) is a Symfony2 based easy to use invoice system.
Application is actually in early development phase.

The original idea is to build an easy to use/flexible invoice application
using Symfony2. I've used [Siwapp](http://www.siwapp.org/) but actually
it seems abandoned (at least PHP-Symfony version). This application will
provide a migration path from Siwapp.


Demo
----



Documentation
-------------

The bulk of the documentation is stored in the `config/Resources/doc/`
directory in this application:

[Read the Documentation](https://github.com/teclliure/eis/blob/master/config/Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](https://github.com/teclliure/eis/blob/master/config/Resources/doc/install.md)

TODO
----

- Send by email
- Move all Doctrine query to repository and inject it http://php-and-symfony.matthiasnoback.nl/2014/05/inject-a-repository-instead-of-an-entity-manager
- Refactor managers to use http://php-and-symfony.matthiasnoback.nl/2014/05/inject-the-manager-registry-instead-of-the-entity-manager
- Refactor to a more event-listener design to decouple

License
------
This bundle is under the MIT license. See the complete license in the file:

    LICENSE

Application developed by [Teclliure][1] and sponsored by [ruiz+company Studio][2]

[1]: http://www.teclliure.net/
[2]: http://www.ruizcompany.com/