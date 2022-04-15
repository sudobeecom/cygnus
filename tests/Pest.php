<?php

use SudoBee\Cygnus\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in("Feature", "Unit");
