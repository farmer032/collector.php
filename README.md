### Collector.php

Collector for private and public GitHub repositories. Saves all code to TarBall files to folder like: githubusername_2025-02-09_15-39-29 (date("Y-m-d_H-i-s")).

Usage:
```commandLine
php collector.php --user=YOUR_USER_NAME --token=YOUR_TOKEN
```

First of all, need to create token in https://github.com/settings/tokens which will be able to access repositories. Need to select particular scope.
