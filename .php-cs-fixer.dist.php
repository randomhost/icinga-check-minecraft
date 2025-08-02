<?php
/**
 * PHP Code Style Fixer configuration.
 *
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2025 Random-Host.tv
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD License (3 Clause)
 *
 * @see https://github.random-host.tv
 */

declare(strict_types=1);

namespace PhpCsFixer;

$finder = Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
;

$config = new Config();
$config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        'phpdoc_annotation_without_dot' => false,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
        'phpdoc_to_comment' => false,
    ])
;

return $config;
