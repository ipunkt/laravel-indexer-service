# laravel-indexer-service
A laravel microservice for creating/updating/deleting resource items within a connected solr core.

This service provides an json api (1.0) standardize endpoint to communicate with an Apache Solr instance.

## Purpose

We support item creation, update and deletion, for single and multiple items at once. We use the json api 1.0 standard definition for transferring data.

Each item can have various attributes, which all will be stored on the solr core configured.

The `id` attribute is special. Without an `id` attribute solr wil give the item an `id`. If you specify one (no matter where: as id or within the attributes) the solr core uses the given `id`.

## Test

Once configured you can test the service via command:

	php artisan test:payload '{"data":"value"}'

## Configuration

You have to configure various service options. Each of them are environment variables.

### Sentry

We provide sentry as bug/log out-of-the-box. You just have to set the **SENTRY_DSN** with your sentry dsn.

### Secure Token

**SERVICE_SECURE_TOKEN** has to be configured to verify token access based communication. This token value has to be on every single request to the api as header `Authorization`.

Example: `Authorization Token wfeljhf`

The request is only valid when you configured the service with `SERVICE_SECURE_TOKEN=wfeljhf` in your environment.

### Cache

**CACHE_DRIVER** should not be set or changed to other value than its default `redis`. Maybe `file` could be used, but we suggest keeping it `redis`.

### Apache Solr

**SOLR_HOST** has to be configured with your host.

**SOLR_PORT** has to be the port it listens to. It defaults to `9893`.

**SOLR_PATH** has to be the path for the solr it listens on. It defaults to `/solr/`.

**SOLR_CORE** has to be the core name this service runs on. It defaults to `default`.

**SOLR_USERNAME** has to be the username to access this service.

**SOLR_PASSWORD** has to be the password to access this service.

**SOLR_TIMEOUT** has to be the timeout for the internal http client. It defaults to `30`.

### Validation Rules

The service is completely environment-driven for the validation rules.

#### Generic validation rules

You can set up validation rules for every request.

`GENERIC_VALIDATION_RULE_` followed by the attribute name to validate this for every request, ignoring existance of attribute.

Example `GENERIC_VALIDATION_RULE_ID` with the value `sometimes|numeric` means that everytime an `id` is given, it has to be numeric.

#### Input validation rules

You can set up validation rules for every attribute found in the input request.

`INPUT_VALIDATION_RULE_` followed by the attribute name to validate this for every request an attribute named like defined is present.
 
 Example `INPUT_VALIDATION_RULE_SOURCE` with the value `required|in:feed,crawler,page` means a given `source` attribute has to be one of the given values.

## Local Environment

Your local environment will be available [here](http://localhost:15540/).

We (ipunkt) provide a package called [rancherize](https://www.github.com/ipunkt/rancherize) for hosting our web stacks in a rancher environment with various docker images. For local development you can use rancherize command in the following way.

You need locally docker daemon installed.

Copy following content in a rancherize.json file in your project root:

```json
{
	"blueprints": {
		"webserver": "Rancherize\\Blueprint\\Webserver\\WebserverBlueprint"
	},
	"blueprint": "webserver",
	"default": {
		"add-redis": true,
		"add-database": false,
		"add-version": "SVN_REVISION",
		"queues": [
			{
				"connection": "redis",
				"name": "default"
			}
		],
		"rancher": {
			"in-service": true,
			"account": "your-rancher-account"
		},
		"docker": {
			"account": "docker-account",
			"repository": "docker-repository",
			"version-prefix": "laravel-indexer-service_",
			"base-image": "busybox"
		},
		"healthcheck": {
			"url": "\/healthcheck"
		},
		"nginx-config": "",
		"service-name": "LaravelIndexerService",
		"environment": {
			"QUEUE_DRIVER": "redis",
			"REDIS_HOST": "redis",
			"NO_MIGRATE": true,
			"NO_SEED": true,
			"SENTRY_DSN": "",
			"APP_KEY": "base64:WMNa3A7KdAq8NMABeXrQDVZ0tg3BfaV1stkZ5melL6g="
		}
	},
	"environments": {
		"local": {
			"debug-image": true,
			"sync-user-into-container": true,
			"expose-port": 15540,
			"use-app-container": false,
			"mount-workdir": true,
			"php": "7.0",
			"environment": {
				"APP_ENV": "local",
				"APP_DEBUG": true,
				"APP_KEY": "base64:14Bp/oUv0Va4MMdT/cK8rKrypEIrj5MW0dlIbUcSFK0=",
				"SOLR_HOST": "127.0.0.1",
				"SOLR_PORT": 8983,
				"SOLR_PATH": "/solr/",
				"SOLR_CORE": "gettingstarted",
				"SOLR_USERNAME": "",
				"SOLR_PASSWORD": "",
				"SOLR_TIMEOUT": 50
			}
		}
	}
}
```

Starting with `vendor/bin/rancherize start local` and stopping with `vendor/bin/rancherize stop local`.

### Artisan inside docker

`docker exec -it laravelindexerservice_LaravelIndexerService_1 php /var/www/app/artisan`

### Solr on local development

Just exec
```bash
$> docker run --name my_solr -d -p 8983:8983 -t solr
```

And optional create a core:
```bash
$> docker exec -it --user=solr my_solr bin/solr create_core -c gettingstarted
```

Afterwards stop and remove the container:
```bash
$> docker stop my_solr
$> docker rm my_solr
```
