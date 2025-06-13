# REMP Campaign Skeleton

This is a pre-configured skeleton of REMP Campaign application with simple installation.

Campaign is a simple tool for creation and management of (primarily self-promo) banner campaigns on your web. Banner can either be a preconfigured template, or completely custom HTML/JS that the system executes on your behalf.

## Dependencies

To run Campaign we strongly recommend you to integrate skeleton application with other REMP tools listed below.

* Campaign
  * [Integration with CMS/CRM](https://github.com/remp2020/campaign-module?tab=readme-ov-file#integration-with-cmscrm)
    * [Javascript snippet](https://github.com/remp2020/campaign-module?tab=readme-ov-file#javascript-snippet)
    * [Segment integration](https://github.com/remp2020/campaign-module?tab=readme-ov-file#segment-integration)
  * [Integration with Beam Journal](#integration-with-beam-journal)
* SSO
  * [Integration with REMP SSO](#integration-with-remp-sso)

Note: Docker Compose of this skeleton provides REMP SSO, so you don't have to install it manually to get the app running. Use of the Docker Compose and images provided in this repository is not recommended in the production environment and is intended for testing/development purposes.

### Integration with Beam

Beam Tracker API and Beam Journal API (also known as Segments API) provide API for tracking and retrieving information about ongoing campaigns. Its integration with Campaign tool is optional, but provides ability to see campaign statistics directly in the Campaign admin interface.

Beam's APIs (and their dependencies) are not part of this repository. You need to install [Beam Skeleton](https://github.com/remp2020/beam-skeleton) (either use Docker Compose or local installation) and make the Segments API available for Campaign.

Once the APIs are available, make sure you change `REMP_SEGMENTS_ADDR` in the `.env` file. If you run Campaign from Docker Compose, you also need to make this host available in `docker-compose.override.yml`:

```yaml
services:
# ...
  campaign:
    extra_hosts:
      - "segments.remp.press:172.17.0.1" # usual IP of the Docker host machine
```

### Integration with REMP SSO

As a default authentication method for secured routes Campaign is using middleware `Remp\LaravelSso\Http\Middleware\VerifyJwtToken`, which authenticates user against preconfigured [SSO service](https://github.com/remp2020/remp/tree/master/Sso) running on `http://sso.remp.press:9595` url. You can change this URL in `.env` file as `REMP_SSO_ADDR` env variable.

We recommend to SSO from the Docker Compose provided in this repository. By default, SSO is exposed at `http://sso.remp.press:9595` which matches the default env settings. See [docker-compose.override.yml](./docker-compose.override.yml) for more information.

You can also run SSO locally by installing it manually. Please follow [SSO documentation](https://github.com/remp2020/remp/tree/master/Sso). To properly configure Docker network to access locally-running SSO, you should edit `docker-compose.override.yml` and make your SSO instance accessible to the network of Campaign's Docker compose via `extra_hosts` directive:

```yaml
services:
# ... 
  campaign:
    extra_hosts:
      - "sso.remp.press:172.17.0.1" # usual IP of the Docker host machine
```

## Installation

### Docker

The simplest possible way is to run this application in Docker containers. Docker Compose is used for orchestrating. Except of these two applications, there is no need to install anything on host machine.

Recommended _(tested)_ versions are:

- [Docker](https://www.docker.com/products/docker-engine) - 24.0.2, build cb74dfc
- [Docker Compose](https://docs.docker.com/compose/overview/) - v2.18.1

#### Steps to install application within docker

1. Get the Campaign Skeleton:

    ``` bash
    git clone https://github.com/remp2020/campaign-skeleton.git
    ```

    ```bash
    cd campaign-skeleton
    ```

2. Prepare environment & configuration files
    ```bash
    # Configuration of Campaign web admin
    cp .env.example .env
    ```
    ```bash
    # Configuration of other dependencies Campaign requires (databases, other REMP tools)
    cp docker-compose.override.example.yml docker-compose.override.yml
    ```
    
    No changes are required if you want to run application as it is.

    **Note:** Nginx web application runs on the port 9595 by default. Make sure this port is not used, otherwise you will encounter error like this when initializing Docker:

    ```
    ERROR: for nginx  Cannot start service nginx: Ports are not available: listen tcp 0.0.0.0:9595: bind: address already in use
    ```

    In such case, change port mapping in `docker-composer.override.yml`. For example, the following setting maps nginx's internal port 80 to external port 7979, so the application will be available at http://campaign.remp.press:7979.

    ```yaml
    services:
    # ...
      nginx:
        ports:
          - "7979:80"
    ```

3. Setup hosts

   Default host used by application is `http://campaign.remp.press`. This domain should point to localhost (`127.0.0.1`), so add it to local `/etc/hosts` file.

    ```bash
    echo '127.0.0.1 campaign.remp.press' | sudo tee -a /etc/hosts
    echo '127.0.0.1 sso.remp.press' | sudo tee -a /etc/hosts
    ```

4. Start Docker containers

    ```bash
    docker compose up
    ```

   You should see logs of starting containers. This may include errors, because application was not yet initialized.

5. If you run SSO from the Docker Compose (default), we need to initialize it first. Run the following set of commands:

    ```bash
    # run from anywhere in the project
    docker compose exec mysql mysql -uroot -psecret -e 'CREATE DATABASE IF NOT EXISTS sso'
    docker compose exec sso make install
    docker compose exec sso php artisan key:generate
    docker compose exec sso php artisan jwt:secret
    ```

5. Now we are ready to initialize Campaign's web app:

    ```bash
    # run from anywhere in the project
    docker compose exec campaign make install
    docker compose exec campaign php artisan key:generate
    ```
   
6. Seed the demo data (optional)

    ```bash
    docker compose exec campaign php artisan db:seed DemoSeeder
    docker compose exec campaign php artisan campaigns:refresh-cache
    ```
   
7. Visit `http://campaign.remp.press:9595` to verify authentication via SSO is working and the demo data was seeded correctly.

8. Visit testing article to feed data to Campaign (optional):

If you seeded demo data (optional step 6), you can visit http://campaign.remp.press:9595/test-article.html. The article integrates Campaign's JS snippet and displays the demo campaign banner.

Be aware that there will be no stats recorded for this campaign by default, as you need to run Beam first to collect and aggregate the data.

#### Updating

If you use docker compose, update the images to their latest versions first:

```bash
# stop the containers if they're running
docker compose stop

# download the latest versions of images
docker compose pull

# rebuild the containers with the new images
docker compose up --force-recreate --build 
```

If Campaign is running now, connect to the Docker container (if you use it), update Composer (PHP) dependencies, and repeat the installation process:

```bash
# install latest version of campaign
docker compose exec campaign composer update

# run service commands (migrations, cache generation, etc.)
docker compose exec campaign make install
```
   
### Manual installation

#### Dependencies

- PHP 8.3
- MySQL 8
- Redis 6.2

#### Installation

[comment]: <> (Clone this repository and run the following command inside the project folder:)
Clone this repository, go inside the folder and run the following to create configuration files from the sample ones:

```bash
cp .env.example .env
```

Edit `.env` file and set up all required values such as database and Redis connections.

Now run the installation:

```bash
make install
```

Set the application key
```bash
php artisan key:generate
```

#### Updating

When the new version is released, just update Composer (PHP) dependencies and repeat the installation process:

```bash
composer update
make install
```

## Customization

Campaign-skeleton is Laravel application with standard [directory structure](https://laravel.com/docs/12.x/structure) and whole Campaign functionality is provided as [Laravel package](https://laravel.com/docs/12.x/packages).
[Campaign-module](https://github.com/remp2020/campaign-module) provides own routes, commands, UI, database migrations.

So for the further information please follow official [Laravel documentation](https://laravel.com/docs/12.x) on corresponding version.

### Configuration

All of the configuration files for the Laravel framework are stored in the `config` directory. Also configuration files from Campaign package are published into config folder during project initialization.
Most configuration values could be overwritten in [environment](https://laravel.com/docs/12.x/configuration#environment-configuration) `.env` file.

For further information about configuration see official [Laravel documentation](https://laravel.com/docs/12.x/configuration).

### Commands

Along the commands provided by Campaign package you could add own commands by manual adding into folder `app/Console/Commands/` or by Artisan command `make:command`. If you would like to change the behaviour of Campaign command you could register own version of command with the same signature. 

For example see `App\Console\Commands\TestCommand` which can be call by `php artisan test`.

For further information about commands see official [Laravel documentation](https://laravel.com/docs/12.x/artisan).

### Routes

Routes registered by Campaign package could be replaced by own routes in files `routes/web.php` for web interface or in `routes/api.php` for API calls. 

Two routes are provided as example. Web route `/test` and API route `/api/test/create`.

For further information about routing see official [Laravel documentation](https://laravel.com/docs/12.x/routing).

### Views

To edit views from Campaign package add the own version of view into folder structure `resources/views/vendor/campaign/`. Laravel will first check if a custom version of the view has been placed in the folder otherwise will use view from Campaign package.

As an example you can see `/resources/views/test/index.blade.php` as the view file for `TestController::index` action.

For further information about views overriding see official [Laravel documentation](https://laravel.com/docs/12.x/packages#overriding-package-views).

### Observers

To listen for model changes (Eloquent events) you can use the observers. New observer can be added by using command `php artisan make:observer ModelNameObserver --model=ModelName` or manually added into folder `app/Observers` and registered in `AppServiceProvider`.

As an example is registered observer `App\Observers\CampaignObserver` for model `Remp\CampaignModule\Model\ShortMessageTemplate`.

For further information about observers overriding see official [Laravel documentation](https://laravel.com/docs/12.x/eloquent#observers).

### Database

Non-breaking database changes you could provide by adding own migrations into folder `database/migrations` or by Artisan command `make:migrations`.

For further information about migrations see official [Laravel documentation](https://laravel.com/docs/12.x/migrations).
