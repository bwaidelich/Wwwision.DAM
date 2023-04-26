# Wwwision.DAM

Proof of Concept of a simple Digital Asset Management built on top of the Event Sourced Content Repository

> **Warning**
> This package is currently a proof of concept. It is subject to change, but it might never make it to an actual product!

## Usage

### Installation

> **Warning**
> This package is not yet prepared to be installed on its own
> See https://github.com/bwaidelich/Wwwision.Neos.DAM for an example integration into [Neos CMS](https://neos.io).

Install using [composer](https://getcomposer.org):

```shell
composer require wwwision/dam
```

Afterwards call

```php
$this->dam->setUp();
```

in order to create required database tables and root nodes.

### PHP API

See [DAM.php](src/DAM.php)
