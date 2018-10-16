vendor/bin/codecept generate:cest api <api>
Creates the test php file in tests folder

# Run verbose and generate html output:
vendor/bin/codecept run -vvv --html

# run the tests against the staging environment:
vendor/bin/codecept run -vvv --env staging

# run the tests against the local dev environment:
vendor/bin/codecept run -vvv --env dev

# Run xml "group" tests
vendor/bin/codecept run -vvv -g xml 

# run smoke tests
vendor/bin/codecept run -vvv -g smoke

# run all tests in a file
vendor/bin/codecept run  tests/ValidateEmailLookupCest.php 

# run a particular test in a file
vendor/bin/codecept run  tests/ValidateEmailLookupCest.php:defaultCampaign

# the above matches
✔ ValidateEmailLookupCest: Default campaign (0.61s)
✔ ValidateEmailLookupCest: Default campaign xml (0.67s) 

vendor/bin/codecept run  tests/ValidateEmailLookupCest.php:defaultCampaign$

# Run smoke tests against staging from the file
vendor/bin/codecept run  -g smoke --env staging tests/ValidateEmailLookupCest.php
