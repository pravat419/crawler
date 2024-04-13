# Crawler Commands
1. composer update (Update the Project)

2. php crawl.php (Run Project through Command Line)

# Business Logic
I used GuzzleHttp\Client for all Http GET calls and PHP DOMDocument & DOMXpath classes to retrieve the content from HTML data received through Http Get Calls.

Crawler user defined class I wrote to do all the business logic. And you can have a look at crawl.php file which contains all the code responsible for storing topics details in a output.txt file.

The crawl.php will retrieve the HTML source from https://www.cochranelibrary.com and then get the URL of Browse anchor tag. After loading Browse page HTML source, I am trying to fetch the URL of "Allergy & intolerance" topics anchor tag. Then After I fetch the HTML source of "Allergy & intolerance" page. I am rotating each and every page to grab the paginated content URLs or topics URL specific to "Allergy & intolerance" topics.

After I grab all the topics link in a array variable $this->topicsurl, then I am rotating each of these URLs and fetching the HTML source of these pages. Then I am fetching the Title, Author, Publish Data and I already have topics URL & Topic Title which I am storing into output.txt file by concatenating "|" to each of these data and then all Topics inserted into a new line in the output.txt file.

You can add any topics into this array variable $topicsSearch = ["Allergy & intolerance"]; to fetch the topics details of those specified topics.
