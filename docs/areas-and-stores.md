# Areas and stores

The store directory now supports an `Area -> Store` hierarchy. Each store belongs to one area, while the claim still stores only `store_id`; historical claim records are not rewritten when an area is assigned or renamed.

## Use the admin UI

1. Open **Areas** and create a parent area, for example `Hall 1`.
2. Open **Stores**, edit a store, and choose its area. New stores require an area.
3. Repeat for each store. Only active stores assigned to an area appear in the public claim form.
4. On the claim form, choose the area first; the store dropdown then shows only stores in that area.

Existing stores get a nullable `area_id` from the migration, so they remain in the database and their historical claims remain valid. Assign those stores to areas before expecting them to appear in the new claim submission dropdown.

## Optional seeder mapping

`database/seeders/DatabaseSeeder.php` keeps the seven initial merchant names and has an `$areaAssignments` array. Add a mapping when you'd rather define the assignments in code:

```php
$areaAssignments = [
    'Hall 1' => ['Merchant Mombee', 'Merchant Trademark'],
    'Hall 2' => ['Merchant WOF', 'Merchant PPM'],
    'Hall 3' => ['Merchant BFF', 'Merchant Inacraft', 'Merchant Jacoweek'],
];
```

Those area names and assignments are examples only. Replace them with your actual layout. The seeder will create missing areas/stores and update the area assignment for the listed stores. It does not delete other stores or claims. Run `php artisan db:seed` after setting the map. If you manage assignments through the UI, leave `$areaAssignments` empty instead.

## Migration

Deploy the code and run `php artisan migrate`. The migration creates `areas` and adds nullable `stores.area_id`; it does not use `migrate:fresh`, truncate tables, or delete claim data.

## Filters and export

The dashboard/admin filters include area and store. Excel claim exports include an `Area` column and use the selected area filter. The proof ZIP uses the same area filter and includes the area name in each archive filename.
