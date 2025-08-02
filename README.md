[![Build Status][1]][2]

# randomhost/icinga-check-minecraft

<!-- TOC -->
* [1. Purpose](#1-purpose)
* [2. Usage](#2-usage)
  * [2.1. Player Count](#21-player-count)
    * [2.1.1. Usage example](#211-usage-example)
    * [2.1.2. Command line parameters](#212-command-line-parameters)
* [3. License](#3-license)
<!-- TOC -->

## 1. Purpose

This package provides Icinga check commands for Minecraft.

## 2. Usage

`PlayerCount` is currently the only available check but more may follow in the
future.

### 2.1. Player Count

Checks the amount of players on the server.

#### 2.1.1. Usage example

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

#### 2.1.2. Command line parameters

| Parameter             | Description                             |
|-----------------------|-----------------------------------------|
| `--host`              | Minecraft server IP address or hostname |
| `--port`              | Query port                              |
| `--thresholdWarning`  | Threshold to trigger the WARNING state  |
| `--thresholdCritical` | Threshold to trigger the CRITICAL state |

## 3. License

See LICENSE.txt for full license details.


[1]: https://github.com/randomhost/icinga-check-minecraft/actions/workflows/php.yml/badge.svg
[2]: https://github.com/randomhost/icinga-check-minecraft/actions/workflows/php.yml
