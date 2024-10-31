This plugin allows you to setup easily configurable courses with optional child course modes.

## Customisable fields
Enable the fields you require in the plugin settings menu page.

## Templating
This plugin works with default wordpress templating, just edit the `archive-course.php`, `single-course.php` or `single-course_mode.php` template files.

If course modes are enabled then you will not be able to access the parent course as that will act as singular search result, and will redirect to the first or primary course mode if you try to visit the single post.

Slugs will be handled in the following way: `course/course_mode`.

## Contrbuting
Any changes to the plugin should respect that it is currently live on several sites.

Make a branch from the `production` branch, and do any server side testing by creating a merge request into the `testing` branch.