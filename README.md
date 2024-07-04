**Cricket Tournament Management System:**
This PHP project is designed to manage a cricket tournament with user and admin functionalities. It allows users to sign up, view tournament scorecards and enables admins to manage matches.

**Features:**

User Registration and Authentication:
a)  New users can sign up with a username, age, and password.
b)  User authentication ensures secure access to the system.

User Dashboard:
a)  Upon login, users can view the scorecard of the entire tournament.

Admin Privileges:
a)  Admin users have special access rights, including creating new matches, updating match details, and deleting matches if necessary.

**Additional Features:**
a)  Strike Rate Calculator - calculates the strike rate of each batsman based on the number of balls faced and runs scored.
b)  Duckworth Lewis Calculator - calculates par score in rain-affected matches.

**Technologies Used:**
PHP, MySQL

**Scoreboard Calculations:**
a)  Ranks teams primarily based on points, then based on NRR.
b)  The scoreboard is designed to update in such a way that, it calculates the Net Run Rate of each team based on the number of overs and balls played, wickets lost and runs scored. (Net Run Rate is calculated differently based on whether the team gets all out.)
c)  Updates scorecard including matches played, wins, losses, tied, points and NRR.
