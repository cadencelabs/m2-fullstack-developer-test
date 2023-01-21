# Cadence Labs - M2 Full Stack Test
## Local Setup

1. Follow the instructions on this page ensure you have completed the prerequisites for running Magento 2 on your local:
https://devdocs.magento.com/guides/v2.4/install-gde/prereq/prereq-overview.html
2. Configure you app/etc/env.php with the following values, remember to replace DB_NAME, USERNAME, PASSWORD, LOCAL_URL to match your local environment.

```php
<?php
return [
    'backend' => [
        'frontName' => 'admin_movie'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => 'df078eed6e4eb68460b324845f2995fd'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => '<DB_NAME>',
                'username' => '<USERNAME>',
                'password' => '<PASSWORD>',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'default',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '52a_'
            ],
            'page_cache' => [
                'id_prefix' => '52a_'
            ]
        ],
        'allow_parallel_generation' => false
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'downloadable_domains' => [
        '<LOCAL_URL>'
    ],
    'install' => [
        'date' => 'Tue, 22 Jun 2021 05:39:20 +0000'
    ],
    'system' => [
        'default' => [
            'web' => [
                'unsecure' => [
                    'base_url' => 'https://<LOCAL_URL>/'
                ],
                'secure' => [
                    'base_url' => 'https://<LOCAL_URL>/'
                ],
                'cookie' => [
                    'cookie_domain' => '<LOCAL_URL>'
                ]
            ]
        ],
    ],
    'modules' => [
        'Magento_TwoFactorAuth' => 0
    ]
];
```
3. A copy of the database is located in the `/sql/db.sql.gz` directory of this repo, please import that to your local database as it contains all the custom product attributes, attribute set, and category.
4. Run `composer install` to install all the project dependencies.
5. Run `php bin/magento setup:upgrade` to finish installing the application.
6. Visit the local store URL to ensure the store loads, and you can see the home page.

### Admin Login
Username: `cadence`

Password: `AsdfAsdf$55`

# Instructions
You are creating an online movie store that is going to utilize https://www.themoviedb.org/ API to automatically generate a product catalog. All of your work should be contained in the `Cadence_Movie` module.
We have already created a boilerplate code for that module in `app/code/Cadence/Movie`.

## API Access
You will need to sign up for an account (https://www.themoviedb.org/signup) and follow the instructions on this page (https://developers.themoviedb.org/3/getting-started/introduction) to get an API key.
Once you have the API key you will be able to utilize the API to retrieve the data mentioned in this assignment.

## Backend
### Part 1
Create a Magento CLI script that will do the following:

1. Use the **Get Popular** endpoint (https://developers.themoviedb.org/3/movies/get-popular-movies) to fetch the most popular movies from the first page of the endpoint and create a Magento 2 product for each movie in the API response.
You should map the following attributes returned from the endpoint to these magento product attributes:
   - id -> sku
   - title -> name
   - overview -> description

2. Using the id from the previous endpoint make a call to the Get Details endpoint (https://developers.themoviedb.org/3/movies/get-movie-details) to fetch the following information and map it to the corresponding product attributes:
   - genre -> genre | comma separated list of genre names
   - release_date -> year | you will need to parse out the year from the release date and store just the year
   - vote_average -> vote_average

3. Make another call to Get Credits endpoint (https://developers.themoviedb.org/3/movies/get-movie-credits) to fetch the cast and crew information and map the returns information using the following instructions:
   - Loop through all the cast and create a comma-separated list of actor `name` and save that to the `actors` product attribute.
   - Loop through all the crew entries and only save the following information:
     - if crew.job === "Producer"
       - Save as `producer` - if more than one it should be saved as a comma-separated list.
     - if crew.job === "Director"
       - Save as `director` - if more than one it should be saved as a comma-separated list.

4. All products should be created with the following additional configuration:
   - product_type: virtual product
   - qty: 100
   - price: $5.99
   - stock_statue: in stock
   - category: Movie (See Cadence\Movie\Helper\Config::MOVIE_CATEGORY_ID)
   - attribute_set: Movie (See Cadence\Movie\Helper\Config::MOVIE_ATTRIBUTE_SET_ID)

5. **(Bonus)** Images - If time permits, also use the Get Images endpoint (https://developers.themoviedb.org/3/movies/get-movie-images) to pull all the images associated with the movie and add that to the product gallery.

### Part 2
Create a Magento CLI script which prints product information based on a given SKU using direct SQL queries.

## Frontend
- Create a new layout xml file in Cadence_Movie that adds a new block to output all the custom attributes parsed from the API and display it in the PDP.
- Create a Composer patch which adds text to the PDP:
  - Text - `(Details provided by The Movie DB)`
  - The text should be rendered underneath the product name.
- You are encouraged to use your creativity for this part and create a visually pleasing layout that contains all the elements listed in the mockup.

## Final Steps

Once you have completed the assignment, please make follow up in the "Cadence Labs - Take Home Test" email so we can review the results.
