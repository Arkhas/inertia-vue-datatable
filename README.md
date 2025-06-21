# Laravel Inertia Vue Datatable

The DataTable library provides a powerful and flexible way to create interactive data tables in your Laravel application. It integrates with Inertia.js to provide a seamless experience between your Laravel backend and Vue frontend, inspired by shadcn-vue.


## Key Features
• Easy to use: Simple API for creating and configuring DataTables

• Flexible: Customizable columns, filters, and actions

• Interactive: Built-in support for sorting, filtering, and pagination

• Exportable: Built-in support for exporting data with customizable formatting

• Responsive: Works well on all screen sizes

• Integrated: Seamlessly integrates with Laravel and Vue

• Modern UI: Styled with Tailwind CSS and inspired by shadcn-vue

• Lucide Icons: Automatic handling of Lucide icons



## Installation

```bash
composer require arkhas/inertia-datatable
```

Publish the assets:

```bash
php artisan vendor:publish --tag=inertia-datatable-assets
```

Optionally, you can publish the configuration and translations:

```bash
php artisan vendor:publish --tag=inertia-datatable-config
php artisan vendor:publish --tag=inertia-datatable-lang
```

Make sure to include the Tailwind CSS configuration in your project:

```js
// tailwind.config.js
module.exports = {
  content: [
    // ...
    './vendor/arkhas/inertia-datatable/resources/js/**/*.vue',
  ],
  // ...
}
```

## Basic Usage

### Creating a DataTable Class

To create a new DataTable, you need to create a class that extends `InertiaDatatable`. Here's a basic example:

```php
<?php

namespace App\DataTables;

use App\Models\User;
use Arkhas\InertiaDatatable\EloquentTable;
use Arkhas\InertiaDatatable\InertiaDatatable;
use Arkhas\InertiaDatatable\Columns\Column;
use Arkhas\InertiaDatatable\Columns\ActionColumn;
use Arkhas\InertiaDatatable\Columns\ColumnAction;
use Arkhas\InertiaDatatable\Filters\Filter;
use Arkhas\InertiaDatatable\Filters\FilterOption;

class UserDataTable extends InertiaDatatable
{
    protected array $availablePageSizes = [10, 25, 50, 100];

    public function setup(): void
    {
        $table = new EloquentTable(User::query());

        // Add columns
        $table->addColumn(
            (new Column('name', 'Name'))
                ->sortable()
                ->searchable()
        );

        $table->addColumn(
            (new Column('email', 'Email'))
                ->sortable()
                ->searchable()
        );

        $table->addColumn(
            (new Column('created_at', 'Created At'))
                ->sortable()
                ->setRenderCallback(function ($user) {
                    return $user->created_at->format('Y-m-d H:i');
                })
        );

        // Add action column
        $table->addColumn(
            (new ActionColumn('actions', 'Actions'))
                ->setAction(
                    (new ColumnAction('Edit'))
                        ->setName('edit_user')
                        ->setIcon('pencil')
                )
        );

        // Add filter
        $table->addFilter(
            (new Filter('role', 'Role'))
                ->addOption(new FilterOption('admin', 'Admin'))
                ->addOption(new FilterOption('user', 'User'))
        );

        // Enable export
        $table->setExportable(true);

        $this->table($table);
    }
}
```

### Using in Controller

```php
<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'datatable' => UserDataTable::make('datatable')
        ]);
    }
}
```

### Using in Vue Component

```vue
<script setup>
import { DataTable } from '@vendor/inertia-datatable';
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Users</h1>

    <DataTable v-bind="datatable" />
  </div>
</template>
```

## License

The MIT License (MIT).
