# Website search

All website keyword search uses Apache Solr through `SearchServiceInterface` and
`SolrSearchService`: home search, city/destination autocomplete, the destinations
page, My Trips, admin destinations/cities/categories/users/reviews/bookings/payments/
activity logs, and the Commerce merchant property search field.

The existing `travel_destinations` core continues to serve the assistant's product
search. The `website_search` core on the same server contains typed documents for
website search. Its schema is in `deploy/solr-search/conf`. These cores have separate
indexing commands; rebuilding one does not clear the other.

## Start and index

```powershell
docker compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.commerce.yml up -d --build
docker exec --user www-data travel-planner-app php artisan search:index
docker exec --user www-data travel-planner-app php artisan commerce:index
```

Wait for both cores to load before indexing. `SEARCH_SOLR_URL` defaults to
`http://127.0.0.1:8983/solr/website_search` for a local PHP process; Compose sets it
to `http://solr:8983/solr/website_search` inside the app container. If Windows reserves
8080, use a local Compose port override. Do not remove data volumes to change ports.

## Behavior

The destinations, My Trips, admin search fields and merchant property search offer
up to six suggestions while typing. Suggestions use the same Solr search and list
filters as their results pages. Selecting one submits the current filter form;
merchant selection updates its live search. Arrow keys and Enter select a match,
Escape dismisses the list, and old requests are canceled as input changes. Trip
suggestions require login and private admin/property suggestions require an active
admin account. Suggestion responses are not cached.

- Search normalizes case and Vietnamese accents and matches partial words.
- Keywords are plain text, limited to 200 characters. Solr query syntax from users
  cannot change entity or ownership filters. Blank searches keep existing browsing.
- Solr supplies matching IDs; the database hydrates current records, applies access
  control, exact filters and the existing sort/pagination. Trip queries also send an
  owner filter to Solr. Stale/deleted IDs never become visible records.
- Cursor requests retrieve all matching IDs, so existing database pagination counts
  are not limited to Solr's first result page. Large result sets incur additional
  requests and memory proportional to matching IDs.
- Search outages and incomplete responses produce HTTP 503 with a readable message.
  There is no SQL or browser keyword-search fallback.
- Search fields are explicitly whitelisted. Passwords, tokens and activity metadata
  are not indexed. Personal and admin search data stays in Solr on the private Docker
  network; its host port binds only to localhost.

Model observers synchronize after transaction commit, independently of whether
Commerce Agents are enabled. Renaming a city/category/user or changing a booking
also refreshes the relevant related search records. Soft-deleted users remain
searchable only through the admin deleted-user database scope. Index failures log a
warning without rolling back a successful database write; run `search:index` to
repair the index. Direct SQL, mass Eloquent updates/deletes and database cascades
bypass model events: run `search:index` after these operations. Rebuilding clears
and reloads the website index in batches; schedule it when brief incomplete search
results are acceptable.

## Verification

```powershell
docker exec --user www-data -e APP_ENV=testing -e CACHE_STORE=array -e SESSION_DRIVER=array travel-planner-app php artisan test --do-not-cache-result --filter=WebsiteSearchTest
docker exec travel-planner-outsource-solr-1 solr create -c website_search_test -d /opt/website-solr
docker exec --user www-data -e APP_ENV=testing -e CACHE_STORE=array -e SESSION_DRIVER=array -e SOLR_INTEGRATION_URL=http://solr:8983/solr/website_search_test travel-planner-app php artisan test --do-not-cache-result --filter=WebsiteSearchLiveTest
```

Create the dedicated test core once. Live tests use SQLite in memory and clear only
`website_search_test`; they do not reset the application database or live index.
