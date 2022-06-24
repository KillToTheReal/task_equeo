<?php


declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use function PHPUnit\Framework\isEmpty;

class HelloWorld implements Invoker{

    public string $line;

//    public function __construct($line = "Hello, world!\n"){
//        $this->line = $line;
//    }

    public function __invoke(array $arguments = [])
    {

        print_r($arguments);
        if(!isEmpty($arguments))
            $this->line = implode(" ",$arguments);
        else
            $this->line = "Hello, world!\n";

        echo($this->line);
    }

    public function name():string
    {
        return "Hello World printing class";
    }


}