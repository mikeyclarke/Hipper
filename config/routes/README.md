# Route configuration

Routes are located in accordance with their controller locations. For example, the route definition for `src/php/FrontEnd/App/Controller/Organization/TokenizedLoginController.php`
would be found in `config/routes/front_end/app/organization.yml`, and the route definition for `src/php/Api/App/Controller/Topic/CreateTopicController.php`
would be found in `config/routes/api/app/topic.yml`.

The source route files loaded from PHP are:

- `config/routes/api/app/routes.yml`
- `config/routes/api/sign_up_flow/routes.yml`
- `config/routes/front_end/sign_up_flow/routes.yml`

These files should ideally define no routes of their own and only import other files that do contain actual definitions.

## Route name format

When new imports are added to the aforementioned source files a `name_prefix` should be provided, e.g. all files imported
by `config/routes/api/app/routes.yml` are given the prefix `api.app.`, and all files imported by `config/front_end/app/rputes.yml`
are given the prefix `front_end.app.`

Route definitions in imported files should then used the format `entity[.sub_entity].action`.

Examples:

- `team.create`
- `project.docs.list`
- `project.doc.show`

The action should usually be a verb. Most actions will not be unique, to ensure consistency please try to use the following
verbs where appropriate:

- Create – for a page or API response that creates a new thing
- Show – for a page or API response that displays a single thing
- List – for a page or API response that displays a list of things

When using “list” you’ll generally want to pluralize the entity but in all other instances use the singular.

Examples:

- `team.show`
- `team.doc.create`
- `team.doc.show`
- `team.docs.list` ← “docs” not “doc”

⚠️ Please remember that your routes will be given a name prefix by the file that includes them. When using a route name
in code you’ll need to remember to include the prefix.
