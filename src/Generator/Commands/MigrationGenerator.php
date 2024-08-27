<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Traits\HasTestTrait;

class MigrationGenerator extends FileGeneratorCommand
{
    use HasTestTrait;
}
