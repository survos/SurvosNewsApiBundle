# NewsApiBundle

A Symfony bundle to interact with NewsApiCDN via the [NewsApi-PHP library](https://github.com/ToshY/NewsApiNet-PHP).

Still under development, feedback welcome!  

## Quickstart
```bash
symfony new news-api-demo --webapp && cd news-api-demo
composer require survos/news-api-bundle
```


## Installation

Go to https://newsapi.com and get a key.

Create a new Symfony project.

```bash
symfony new news-api-demo --webapp && cd news-api-demo
composer require survos/news-api-bundle
bin/console news-api:config <api-key> >> .env.local 
bin/console news-api:list
```

You can browse interactively with the basic admin controller.

```bash
composer require survos/simple-datatables-bundle
symfony server:start -d
symfony open:local --path=/news-api/zones
```

Or edit .env.local and add your API key.

As each storage zone has its own passwords and id, these need to be configured individually in survos_news-api.yaml.  Rather than tediously configuring each zone by cutting and pasting, we can use the first utility to dump the configuration with just the main api key.  This saves you from having to go to  https://dash.news-api.net/storage and go to each storage zone, then click on it and select "FTP and ApiAccess" and selecting each key.


```bin
bin/console news-api:config <api-key> 
```

Note: use --filter to limit to the zones to a regex (@todo)

You can skip passing the api key on the command line by defining it as an environment variable, etc.
```bash
echo "NEWS_API_KEY=api-key >> .env.local
```

This command dumps the packages/config/survos_news-api.yaml file with references to the environment variables, which are also dumped and should be added to .env.local.  If your application only reads from news-api, you can remove the password environment variables, it is only used during writing.  You can also remove the main api key if your application doesn't need it in production.

Open .env.local and replace the values.

Your application now has a bare-bones controller located at /admin/news-api, you may want to secure this route in security.yaml, or configure it in config/routes/survos_news-api.yaml.

You also have access to a command line interface.

```bash
bin/console news-api:list 
```

```bash
+------------- museado/ -----+--------+
| ObjectName     | Path      | Length |
+----------------+-----------+--------+
| photos finales | /museado/ | 0      |
+----------------+-----------+--------+


```

