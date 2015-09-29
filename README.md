# crontab
A PHP crontab parser which displays in a web page

Note that this should be used internally only and it is not recommended to publically display your crontabs online, so use at your own risk!

I wrote this to help see what was going on with crontab on any given server.  The index.php reads and displays a text (crontab.txt) file dumped out from crontab.  The script has been quite tailored to my needs so you'll need to adjust / rewrite code as you see fit.  The crontab.sh helps keep the crontab.txt file up to date.  Simply add the crontab.sh to your crontab and have it run nightly or what-have-you.

Also, I have it displaying each valid cron as either green (enabled) or red (disabled) to help understand what is currently active.