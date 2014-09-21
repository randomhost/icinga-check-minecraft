[![Build Status](https://travis-ci.org/Random-Host/PHP_Icinga_Minecraft.svg)](https://travis-ci.org/Random-Host/PHP_Icinga_Minecraft)

PHP_Icinga_Minecraft
====================

This package provides Icinga check commands for Minecraft.

Usage
-----

A basic approach at using this package could look like this:

```php
<?php
namespace randomhost\Icinga\Checks\Minecraft;

require 'psr0.autoloader.php';

$check = new PlayerCount();
$check->run();
```

This will instantiate the `PlayerCount` class for the Minecraft server and check
the amount of players currently connected to the server.

`PlayerCount` is currently the only available check but more may follow in the
future.

#### Command line parameters

| Parameter           | Description                             |
| ------------------- | --------------------------------------- |
| --host              | Minecraft server IP address or hostname |
| --port              | JSONAPI port                            |
| --user              | JSONAPI user                            |
| --password          | JSONAPI password                        |
| --salt              | JSONAPI salt                            |
| --thresholdWarning  | Threshold to trigger the WARNING state  |
| --thresholdCritical | Threshold to trigger the CRITICAL state |

System-Wide Installation
------------------------

PHP_Icinga_Minecraft should be installed using the [PEAR Installer](http://pear.php.net).
This installer is the PHP community's de-facto standard for installing PHP
components.

    sudo pear channel-discover pear.random-host.com
    sudo pear install --alldeps randomhost/PHP_Icinga_Minecraft

As A Dependency On Your Component
---------------------------------

If you are creating a component that relies on PHP_Icinga_Minecraft, please make sure that
you add PHP_Icinga_Minecraft to your component's package.xml file:

```xml
<dependencies>
  <required>
    <package>
      <name>PHP_Icinga_Minecraft</name>
      <channel>pear.random-host.com</channel>
      <min>1.0.0</min>
      <max>1.999.9999</max>
    </package>
  </required>
</dependencies>
```

Development Environment
-----------------------

If you want to patch or enhance this component, you will need to create a
suitable development environment. The easiest way to do that is to install
phix4componentdev:

    # phix4componentdev
    sudo apt-get install php5-xdebug
    sudo apt-get install php5-imagick
    sudo pear channel-discover pear.phix-project.org
    sudo pear -D auto_discover=1 install -Ba phix/phix4componentdev

You can then clone the git repository:

    # PHP_Icinga_Minecraft
    git clone https://github.com/Random-Host/PHP_Icinga_Minecraft.git

Then, install a local copy of this component's dependencies to complete the
development environment:

    # build vendor/ folder
    phing build-vendor

To make life easier for you, common tasks (such as running unit tests,
generating code review analytics, and creating the PEAR package) have been
automated using [phing](http://phing.info).  You'll find the automated steps
inside the build.xml file that ships with the component.

Run the command 'phing' in the component's top-level folder to see the full list
of available automated tasks.

License
-------

See LICENSE.txt for full license details.
