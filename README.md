Alixar is a fork of Dolibarr powered with Alxarafe.

[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](https://github.com/Alxarafe/Alixar/issues?utf8=✓&q=is%3Aopen%20is%3Aissue)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alxarafe/alixar/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alxarafe/alixar/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/alxarafe/alixar/badges/build.png?b=master)](https://scrutinizer-ci.com/g/alxarafe/alixar/build-status/master)

Dolibarr:

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Dolibarr/dolibarr/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Dolibarr/dolibarr/?branch=develop)

Alxarafe:

[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](https://github.com/Alxarafe/Alxarafe/issues?utf8=✓&q=is%3Aopen%20is%3Aissue)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alxarafe/alxarafe/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alxarafe/alxarafe/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/alxarafe/alxarafe/badges/build.png?b=master)](https://scrutinizer-ci.com/g/alxarafe/alxarafe/build-status/master)


Alxarafe is a package (still in development), which offers the following features:
- Users identification.
- Connection with PDO databases.
- Management of tables.
- Aid to the debugging and development of the application (Log, debugging bar, etc).
- Manager of templates and skins using Twig.

Its modularity, allows to easily change the tools used to provide his functionalities.

You can find Alxarafe in the following repositories:
https://github.com/alxarafe/alxarafe
https://packagist.org/packages/alxarafe/alxarafe

To integrate it into your application you will need to install composer and execute
the following command:

composer require alxarafe/alxarafe

If you find ways to improve the code, do it.
PULL REQUEST welcome!

Why create Alixar?
------------------

Dolibarr is a management package that has a strong community, but its code is quite
disorganized and presents some drawbacks dragged from its previous versions, that in
the current software, they are usually quite outdated.

After analyzing it superficially, we verify that these drawbacks can be solved by
a more or less organized form and substantially improve the maintenance costs of the
application.

Some of these drawbacks are:
- Many points of entry to the code.
- General disorganization.
- A significant amount of duplicate code.
- Excessive use of global variables
- Little use of object-oriented programming.
- Mix of controllers, models and views.

Even so, the tool seems great, and that is why we are interested in solving them.

You will find the updated code in the following repository:
https://github.com/alxarafe/alixar

You can follow all the code improvements in our blog:
https://alxarafe.com/

Remember that you can collaborate to make a code more efficient.

Pull requests are always welcome

(The original text is in Spanish: https://alxarafe.es)