# Syrian Heritage Map - vue js map and WP API endpoints

This is a WordPress plugin used as companion of the [wordpress-sha--importer](https://github.com/csgis/wordpress-sha--importer) plugin to visualize imported data of the [Syrian Heritage Project](https://project.syrian-heritage.org) on a map

It consist of two parts. A js app written in vue to display the data on a map. And some WP endpoints to serve the data to the map.

## Dev dependecies

* yarn
* node
* vue

## Test with CORS disabled

To test it in localhost, with CORS disabled start an insecure Chrome instance. For example in OSX:
```
$ open -n -a "Google Chrome" --args --user-data-dir=/tmp/temp_chrome_user_data_dir http://localhost:8080/ --disable-web-security
```

## Project setup
```
yarn install
```

### Set some needed configuration strings

in `src/config.js` the config variables look like this

```
const config = {
    mapbox: {
        accessToken: 'example',
        languageStyle: {
            'ar': 'mapbox://example',
            'de': 'mapbox://example',
            'en': 'mapbox://example'
        }
    },
    wp: {
        // url of the wordpress instance where the plugin is served from
        url: 'https://example.example'
    }
}
```

add your mapbox access token to the `accessToken` variable
```
...
    mapbox: {
            accessToken: 'example',
            ...
```

in mapbox create a map with the desired style and assign the `languageStyle.en` variable to the style url. En is our default language.
We had some translation bug from mapbox about i18n, so as a workaround you can create a copy of the default map style for each language and assign it to the relative variable.
```
...
    mapbox: {
        ...
        languageStyle: {
            'ar': 'mapbox://example',
            'de': 'mapbox://example',
            'en': 'mapbox://example'
        }
        ...
```


### Compiles and hot-reloads for development
```
yarn run serve
```

### Compiles and minifies for production
```
yarn run build
```

before build, be sure to insert the right url of the WP website in `src/config.js` For example
```
...
    wp: {
            // url of the wordpress instance where the plugin is served from
            url: 'https://example.example'
        }
...
```

after build, upload the dist folder to the wp server

### Run your tests
```
yarn run test
```

### Lints and fixes files
```
yarn run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
