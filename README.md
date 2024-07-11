**Cricket Tournament Management System**
This PHP and MySQL-based project is designed to manage a cricket tournament with user and admin functionalities. It allows users to sign up, view tournament scorecards and enables admins to manage matches.

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
c) Updates scorecard including matches played, wins, losses, tied matches, points, and NRR.

**The Net Run Rate (NRR) calculation**
Formula :
Overs played = overs played + (balls played/6)
For = (number of runs scored/number of overs played)
Against = (number of runs conceded/number of overs bowled)
Net Run Rate = For - Against

Conditions :
a) Zero Runs: If runs scored are zero, overs are set to 20 and balls to 0 for that particular team.
b) All Wickets Lost: If all 10 wickets are lost, overs are set to 20 and balls to 0 for that particular team.
c) Inning 1 Specific: If the team did not complete 20 overs without losing all wickets, overs are set to 20 and balls to 0.
d) Inning 2 Specific: If the team batting second scored fewer runs than the first inning and did not lose all wickets, overs are set to 20 and balls to 0.
e) Inning 2 Specific: If the team batting second won without facing any legal delivery, overs are set to 0 and balls to 1 to avoid division by zero in NRR calculations.
f) General Case: In all other cases, actual overs and balls played are used.

**Please Note**
a) Only a single user has admin privileges to edit scorecards.
b) The project does not focus on making the site fully responsive.

**All possible conditions when updating existing match data and changes in the points table have been thoroughly handled**
a) Match result remained the same, change in NRR for both teams.
b) Match result overturned, changes in points, wins, losses, NRR for both teams.
c) Match result changed to tied, changes in points, wins, losses, NRR.
d) Match result remained tied, change in match details.
e) The match result changed from tied to having a clear winner, with changes in match details and points table.

**Upcoming Implementations**
a) User Privileges To Update: Instead of only admins being able to update the scorecard, all users will be able to update the scorecard in upcoming versions.
b) Handling 'No Result' Matches: Currently, only tied matches are handled. Handling 'no result' matches (matches not completed due to rain or any other circumstances) will be implemented in a later stage of the project.

**Hosting**
The web application is hosted at: https://scorecard.siddheshmestri.online

**About Me**
You can learn more about me and my other projects on my personal portfolio website at https://siddheshmestri.online

**Below are screenshots of the web-app**
Home/Login page : ![Home/Login page](<screenshots/home page.png>)
Register page : ![Register page](<screenshots/register page.png>)
User dashboard : ![User dashboard](<screenshots/user dashboard.png>)
Admin dashboard : ![Admin dashboard](<screenshots/admin dashboard.png>)
Create match page (admin) : ![Create match page](<screenshots/create match.png>)
Update match page (admin) : ![Update match page](<screenshots/update match.png>)
Edit match page (admin) : ![Update match page](<screenshots/edit match.png>)
Tournament management page (admin) : ![Tournament history page](<screenshots/tournament history page.png>)
Strike rate calculator page : ![Strike rate calculator page](<screenshots/strike rate calculator.png>)
DLS par score calculator page :![DLS par score calculator page](<screenshots/dls calculator.png>)
