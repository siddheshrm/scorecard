**Cricket Tournament Management System**
This PHP project is designed to manage a cricket tournament with user and admin functionalities. It allows users to sign up, view tournament scorecards and enables admins to manage matches.

**Features**
User Registration and Authentication:
a) New users can sign up by entering a unique username, age, and password.
b) User authentication ensures secure access to the system.

User Dashboard:
a) Upon login, users can view the scorecard of the entire tournament.

Admin Privileges:
a) Admin user has special access rights, including creating new matches, updating match details, and deleting matches if necessary.

**Additional Features**
a) Strike Rate Calculator - calculates the strike rate of each batsman based on the number of balls faced and runs scored.
b) Duckworth Lewis Calculator - calculates par score in rain-affected matches.
c) Cricket Trivia - Fetches cricket trivia from a custom API hosted on Heroku. The trivia is fetched automatically based on the date of the month to showcase basic knowledge of REST APIs.

**Technologies Used**
PHP, MySQL, HTML, and JavaScript.

**Scoreboard Calculations**
a) Ranks teams primarily based on points, and secondarily based on NRR.
b) The scoreboard is designed to update in such a way that, it calculates the Net Run Rate of each team based on the number of overs and balls played, wickets lost and runs scored. (Net Run Rate is calculated differently based on whether the team gets all out.)
c) Updates scorecard including matches played, wins, losses, tied, points, and NRR.

**Important**
a) Only a single user has admin privileges to edit scorecards.
b) Updating existing resulted matches are not yet handled.
c) The project does not focus on making the site fully responsive.

**Future Implementations**
a) User Privileges To Update: Instead of only admins being able to update the scorecard, all users will be able to update the scorecard in future versions.
b) Editing Resulted Matches: Currently, resulted matches cannot be edited. One has to delete the entire match details and add complete match details in case of any changes. Future updates will allow the editing of resulted matches.
c) Handling 'No Result' Matches: Currently, only tied matches are handled. Handling 'no result' matches (matches not completed due to rain or any other circumstances) will be implemented in a later stage of the project.

**Hosting**
The web application is hosted at: https://projectone.siddheshmestri.online

**About Me**
You can learn more about me and my other projects on my personal portfolio website at https://siddheshmestri.online

**Below are screenshots of the web-app**
Home/Login page : ![Home/Login page](<screenshots/home page.png>)
Register page : ![Register page](<screenshots/register page.png>)
User dashboard : ![User dashboard](<screenshots/user dashboard.png>)
Admin dashboard : ![Admin dashboard](<screenshots/admin dashboard.png>)
Create match page : ![Create match page](<screenshots/create match.png>)
Update match page : ![Update match page](<screenshots/update match.png>)
Tournament history page : ![Tournament history page](<screenshots/tournament history page.png>)
Strike rate calculator page : ![Strike rate calculator page](<screenshots/strike rate calculator.png>)
DLS par score calculator page :![DLS par score calculator page](<screenshots/dls calculator.png>)
