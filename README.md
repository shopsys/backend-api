# Shopsys Backend API

[![Build Status](https://travis-ci.org/shopsys/backend-api.svg?branch=master)](https://travis-ci.org/shopsys/backend-api)
[![Downloads](https://img.shields.io/packagist/dt/shopsys/backend-api.svg)](https://packagist.org/packages/shopsys/backend-api)

This bundle for [Shopsys Framework](https://www.shopsys.com) adds backend API using [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle), [oauth2-bundle](https://github.com/trikoder/oauth2-bundle) and [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle).
The bundle is dedicated for projects based on Shopsys Framework (i.e. created from [`shopsys/project-base`](https://github.com/shopsys/project-base)) exclusively.
This repository is maintained by [shopsys/shopsys] monorepo, information about changes are in [monorepo CHANGELOG.md](https://github.com/shopsys/shopsys/blob/master/CHANGELOG.md).

## Installation
The plugin and all requirements are a Symfony bundles and are installed in the same way:

### Download
First, you download the package using [Composer](https://getcomposer.org/):
```
composer require shopsys/backend-api
```

### Register
For the bundles to be loaded in your application you need to register them in `registerBundles()` method in the `app/AppKernel.php` file of your project:
```diff
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
+       new FOS\RestBundle\FOSRestBundle(),
+       new JMS\SerializerBundle\JMSSerializerBundle($this),
+       new Shopsys\BackendApiBundle\ShopsysBackendApiBundle(),
    );
}
```

## Documentation
[Documentation](https://github.com/shopsys/shopsys/docs/api/introduction-to-backend-api.md) can be found in our monorepo repository.

## Contributing
Thank you for your contributions to Shopsys Read Model package.
Together we are making Shopsys Framework better.

This repository is READ-ONLY.
If you want to [report issues](https://github.com/shopsys/shopsys/issues/new) and/or send [pull requests](https://github.com/shopsys/shopsys/compare),
please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

Please, check our [Contribution Guide](https://github.com/shopsys/shopsys/blob/master/CONTRIBUTING.md) before contributing.

## Support
What to do when you are in troubles or need some help? Best way is to contact us on our Slack [http://slack.shopsys-framework.com/](http://slack.shopsys-framework.com/)

If you want to [report issues](https://github.com/shopsys/shopsys/issues/new), please use the main [Shopsys repository](https://github.com/shopsys/shopsys).

[shopsys/shopsys]:(https://github.com/shopsys/shopsys)
