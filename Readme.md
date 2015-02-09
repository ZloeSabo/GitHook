# Git Hooks for PHP
### Description
Tired of commiting files with syntax errors all over again? Got var_dump pushed to production once again? Use this library to perform checks every time you commit something.

### Usage
1. install this package with composer
```
composer require zloesabo/githooks
```

2. (optional) modify your project's composer.json "extra" section to include your custom hooks
```json
"extra": {
    "git-hooks": [
        "src/Prefix/HookDirectory",
        "@default"
    ]
}
```
You can also omit this to use default hooks

3. (optional) Write some custom hooks  
Your hooks must implement [HookInterface](Hook/HookInterface.php) to be loaded by hook loader.



### TODO's
Tests  
Different hook types  
PHP < 5.4  
Take care of existing hooks during install


