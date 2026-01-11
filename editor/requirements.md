# The Order Achievements editing tool
## Main business goals, objects and glossary
The tool should be a helper for organizing and maintaining a home-made achievement system for users of an internet forum. The internet forum uses BBcode.
The achievement system has a list of possible achievements to get, which can be assigned to users. Many users can get the same achievements; they are not unique per user.

There are 2 main business objects:
1. **Achievements**, associated with a title, description, a point value (can be both negative and positive), a category, and a URL pointing to an image file.
2. **Users**, simply identified with a name.

And the output of the system is two lists:
1. **The Master List**, a list of all achievements, divided into categories.
2. **The Roster List**, a list of the achievements for each user.

There should be support in the system for different categories of achievements, a display order for categories, display order of achievements within the categories and giving achievements to users, where the user's achievements are also in a particular display order.
## Data structure
The data should be stored in a relational database format.
Example: 4 tables, one for achievements, one for users, one for categories and a many-to-many relation table linking users and achievements.
All tables with have unique IDs, which can just be autoincremented integers.
The achievements have separate columns for the title, description, point value, display order and image URL, and a foreign key relation to category.
The users table should just have a name column.
The categories should have a name and a display order.
The relation table should have a composite primary key of achievement ID and user ID foreign keys, and a separate column representing the display order.
## Functional requirements
The following features must exist:
- Generating the output lists, examples in these files:
  - [master list](./forumdata/masterlist.txt) 
  - [roster list](./forumdata/rosterlist.txt)
- Viewing the main business objects:
  - View lists of achievements within a category
  - View lists of achievement categories
  - View lists of users
  - View single user and their associated achievements
  - *(optional, redundant with generating output list)* Viewing list of all achievements in all categories in their order
  - *(optional, redundant with generating output list)* Viewing list of all users with all achievements, with users sorted by name and achievements in proper order
- Editing the achievements and list
  - Create new achievements
  - Edit properties on achievements, i.e. title, description, points, image URL and display order
  - Switch category on achievement
- Editing the users
  - Add users
  - Edit properties on users, i.e. name
  - Add achievements to a user  
  - Change the order of a user's achievements
- Editing the categories
  - Add new category
  - Edit properties on category, i.e. name and display order
  - Reorder achievements within the category
  - Sort achievements within the category by Title

## Non-functional requirements
- The tool should have a high quality architecture, built using standard industry best practices. Example: A single page application following principles of modularity, feature-sliced design, Clean architecture, Domain Driven Design, SOLID, etc.
- Tool must be built with PHP version 8.3.29, with no external libraries used
- The database must be MySQL version 8.0.44
- There must be two modes of operation: The "production" mode which reads and writes all data in a MySQL database, and a "demo" mode which reads data from CSV files but never saves the user's changes beyond the web application memory.
- Security must be basic authentication to log in, and free access to all features from there. When in demo mode, the basic authentication is turned off and there is always free access.