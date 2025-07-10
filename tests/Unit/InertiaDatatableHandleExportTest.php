<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\TestModels\WithTestModels;
use Tests\TestModels\TestModel;
use Tests\TestModels\TestModelDataTable;
use Arkhas\InertiaDatatable\EloquentTable;
use Arkhas\InertiaDatatable\Columns\Column;
use Arkhas\InertiaDatatable\InertiaDatatable;
use Arkhas\InertiaDatatable\Services\ExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

// Test-specific subclass of InertiaDatatable that makes handleExport public
class TestableInertiaDatatable extends InertiaDatatable
{
    public function setup(): void
    {
        // This method is required by the abstract class
    }

    public function handleExport(): BinaryFileResponse
    {
        return parent::handleExport();
    }
}

class InertiaDatatableHandleExportTest extends TestCase
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




    public function test_handle_export_implementation()
    {
        // Create a mock BinaryFileResponse
        $mockResponse = $this->createMock(BinaryFileResponse::class);

        // Create a real TestModel query builder that supports cloning
        $query = TestModel::query();

        // Create a datatable with a partial mock to handle the session
        $datatable = $this->getMockBuilder(TestableInertiaDatatable::class)
            ->onlyMethods(['getFromSession'])
            ->getMock();

        // Mock getFromSession to return appropriate values for different keys
        $datatable->expects($this->any())
            ->method('getFromSession')
            ->willReturnCallback(function ($key, $default = null) {
                if ($key === 'visibleColumns') {
                    return ['name' => true, 'status' => false];
                } elseif ($key === 'filters') {
                    return [];
                }
                return $default;
            });

        // Create a table with exportable columns
        $table = EloquentTable::make($query);
        $table->columns([
            \Arkhas\InertiaDatatable\Columns\Column::make('name')->exportable(),
            \Arkhas\InertiaDatatable\Columns\Column::make('status')->exportable(),
        ]);
        $table->exportable(true);
        $table->exportType('csv');
        $table->exportColumn('visible');
        $table->exportName('test-export');
        $datatable->table($table);

        // Create a request with export parameters
        $request = new Request([
            'dt' => [
                'export' => true,
                'exportType' => 'excel',
                'exportColumns' => 'visible',
                'exportRows' => 'selected',
                'selectedIds' => '1,2,3'
            ]
        ]);
        $this->app->instance(Request::class, $request);

        // Mock the Excel facade to return our mock response
        \Maatwebsite\Excel\Facades\Excel::shouldReceive('download')
            ->once()
            ->andReturn($mockResponse);

        // Call handleExport
        $response = $datatable->handleExport();

        // Verify that the response is the mock BinaryFileResponse
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    public function test_handle_export_not_exportable()
    {
        // Create a real TestModel query builder
        $query = TestModel::query();

        // Create a datatable instance
        $datatable = new TestableInertiaDatatable();

        // Create a table that is NOT exportable
        $table = EloquentTable::make($query);
        $table->columns([
            \Arkhas\InertiaDatatable\Columns\Column::make('name'),
            \Arkhas\InertiaDatatable\Columns\Column::make('status'),
        ]);
        $table->exportable(false); // Set exportable to false
        $datatable->table($table);

        // Create a request with export parameters
        $request = new Request([
            'dt' => [
                'export' => true
            ]
        ]);
        $this->app->instance(Request::class, $request);

        // Expect an abort with 403
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('This table is not exportable');

        // Call handleExport - should throw an exception
        $datatable->handleExport();
    }
}
