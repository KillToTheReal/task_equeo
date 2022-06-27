<?php


declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use function PHPUnit\Framework\isEmpty;
use GuzzleHttp\Client;


class HelloWorld implements Invoker
{

//    public function __construct($line = "Hello, world!\n"){
//        $this->line = $line;
//    }

    public function __invoke(array $arguments = [])
    {
        $client = new Client(["base_uri"=>$arguments["parameters"]['url']]);
        $req = $client->request("GET",'/v2/users/search',['headers'=>$arguments["parameters"]["headers"],'query'=>$arguments["parameters"]["params"]])->getBody()->getContents();
        $decode = json_decode($req,true);
        //print_r($decode);
        $someData = $decode['data'];
        while(isset($decode['meta']['pagination']['links']['next']))
        {
            $nextUrl = $decode['meta']['pagination']['links']['next'];
            $req = $client->request("GET",$nextUrl,['headers'=>$arguments["parameters"]["headers"],'query'=>$arguments["parameters"]["params"]])->getBody()->getContents();
            $decode = json_decode($req);
            $someData = array_merge($someData, $decode["data"]);
        }
        echo("\nScript creating users array worked succesfully\n");
        //print_r($arguments);
        return $someData;
    }

    public function name():string
    {
        return "Script creating users array worked succesfully";
    }


}