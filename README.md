# m-commander
Easy tool for executing your own module via command line

1.Install via Composer (best option, however you can use it without it)

```
"200mph/m-commander": "1.1.*"
```

2.Create your command line module class and extend AbstractCliModule() from m-commander vendor

```
namespace cli\MyTest;

use m-commander\AbstractCliModule;

class TestMe() extends AbstractCliModule 
{

    /**
     * We have to create execute() method (abstraction requirements)
     *
     * @return void
    /*
    protected function execute()
    {

        $this->successOutput('Hello World' . PHP_EOL);

    }
}
```

3.Run your module

```
./vendor/bin/m-commander cli\\MyTest\\TestMe -v
```

You can also use semi quotes to avoid double back slashes notation.

```
./vendor/bin/m-commander 'cli\MyTest\TestMe' -v
```

Above notation is recommended if command have to be executed in CRON, or another shell script.

For more examples please have a look in to ./examples folder

4.Default options

-h
--help
                        Display this page
-v
--verbose
                        Verbose mode
-w
--write-output
                        Write output in to file. Eg "./m-commander 'myNamespace\MyModule' -w /home/user/test.log"
-l
--lock
                        Lock module process. Will not let you run another instance of this same module until current is finished. However you can execute script for another module.
