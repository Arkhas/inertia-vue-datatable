<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\TestModels\TestModelDataTable;
use Tests\TestModels\WithTestModels;
use Tests\TestModels\TestModel;
use Arkhas\InertiaDatatable\EloquentTable;
use Arkhas\InertiaDatatable\Columns\Column;
use Arkhas\InertiaDatatable\InertiaDatatable;
use Arkhas\InertiaDatatable\Services\ExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Request;

class InertiaDatatableExportTest extends TestCase
{
    use WithTestModels;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestModels();
    }

    protected function tearDown(): void
    {
        $this->tearDownTestModels();
        parent::tearDown();
    }


    public function test_dummy()
    {
        $this->assertTrue(true);
    }
}
