## Boxzilla App

This is the site repository for [boxzillaplugin.com](https://account.boxzillaplugin.com).

The application takes care of the following.

- Account Area
- Admin Panel
- License API
- Plugin API
- HelpScout API


### Running this site locally

To run locally, clone this repository and create a `.env` file with your database settings. Then, run the following from the root folder to build the database schema.

```sh
git clone https://github.com/dannyvankooten/boxzillaplugin.com.git
cd boxzillaplugin.com
cp .env.example .env
php artisan migrate --force
```

### Issues
Something to improve? [Please open an issue!](https://github.com/dannyvankooten/boxzillaplugin.com/issues)