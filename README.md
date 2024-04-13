# Crawler Commands
1. composer update (Update the Project)

2. php crawl.php (Run Project through Command Line)

# Business Logic
I used GuzzleHttp\Client for all Http GET calls and PHP DOMDocument & DOMXpath classes to retrieve the content from HTML data received through Http Get Calls.

Crawler user defined class I wrote to do all the business logic. And you can have a look at crawl.php file which contains all the code responsible for storing topics details in a output.txt file.
