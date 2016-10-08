[![Build Status][0]][1]

randomhost/icinga-check-minecraft
=================================

This package provides Icinga check commands for Minecraft.

Usage
-----

`PlayerCount` is currently the only available check but more may follow in the
future.

### Player count

Checks the amount of players on the server.

#### Usage example

```php
<?php
namespace randomhost\Icinga\Check\Minecraft;

require_once '/path/to/vendor/autoload.php';

use randomhost\Minecraft\Status as MinecraftStatus;

$mcStat = new MinecraftStatus();

$check = new PlayerCount($mcStat);
$check->setOptions(
    getopt(
        $check->getShortOptions(),
        $check->getLongOptions()
    )
);
$check->run();

echo $check->getMessage();
exit($check->getCode());
```

This will instantiate the `PlayerCount` class for the Minecraft server and check
the amount of players currently connected to the server.

#### Command line parameters

| Parameter           | Description                             |
| ------------------- | --------------------------------------- |
| --host              | Minecraft server IP address or hostname |
| --port              | Query port                              |
| --thresholdWarning  | Threshold to trigger the WARNING state  |
| --thresholdCritical | Threshold to trigger the CRITICAL state |

License
-------

See LICENSE.txt for full license details.

[0]: https://travis-ci.org/randomhost/icinga-check-minecraft.svg?branch=master
[1]: https://travis-ci.org/randomhost/icinga-check-minecraft
