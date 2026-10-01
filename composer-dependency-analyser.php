<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration;

return $config
    ->addPathRegexToExclude('~Test(Cases)?\.php$~')
    ->ignoreErrorsOnPackage('directorytree/opensearch-adapter', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('directorytree/opensearch-migrations', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('directorytree/opensearch-scout-driver', [ErrorType::UNUSED_DEPENDENCY]);
