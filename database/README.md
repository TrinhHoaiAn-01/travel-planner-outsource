# Travel Planner database

The migration `2026_10_01_000000_align_travel_planner_erd` aligns the previous
Laravel schema with `travel_planner_erd.sql` supplied on October 1, 2026.
It preserves existing travel rows and renames `destinations.price` to
`entrance_fee`. It adds lodging, booking, payment, AI conversation, and activity
log tables, and matches the dump's column sizes, defaults, indexes, and foreign
key actions. Laravel's cache and queue tables remain available for the configured
database-backed services.

For a new database or an existing database managed by the earlier migrations:

```sh
php artisan migrate --seed
```

The seed fixture in `seeders/data/travel_planner_erd.json` contains all 16 populated
tables from the dump, including the original IDs, Vietnamese text, password hashes,
and timestamps. The three seeders load rows in dependency order, inside a
transaction. Repeat seeding updates the same IDs without duplicating rows.
Seeding does not remove additional application data. Use the fixtures with an
empty database or one whose demo IDs already correspond to this dump.

MySQL uses InnoDB so foreign keys are enforced even when the server defaults to
MyISAM, and connections use UTC to preserve the dump's timestamp values. PHPUnit
uses an isolated in-memory SQLite database; its generated primary
image marker is virtual, while MySQL uses the dump's stored generated column.

```sh
php artisan test
```

A database imported directly from the SQL dump already contains the application
tables. Its Laravel migration history must be registered before running normal
migrations; running the original create-table migrations against that import
would conflict with its existing tables.
