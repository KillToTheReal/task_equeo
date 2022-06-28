<?php

declare(strict_types=1);
namespace App;

use Ekvio\Integration\Contracts\Invoker;
use GuzzleHttp\Client;

class ApiRequester implements Invoker
{
    public function __invoke(array $arguments = []):array
    {
        $client = new Client(["base_uri"=>$arguments["parameters"]['url']]);
        $req = $client->request("GET",'/v2/users/search',[
            'headers'=>$arguments["parameters"]["headers"],
            'query'=>$arguments["parameters"]["params"]])->getBody()->getContents();
        $decode = json_decode($req,true);
        $someData = $decode['data'];
        while(isset($decode['meta']['pagination']['links']['next']))
        {
            $nextUrl = $decode['meta']['pagination']['links']['next'];
            $req = $client->request("GET",$nextUrl,[
                'headers'=>$arguments["parameters"]["headers"],
                'query'=>$arguments["parameters"]["params"]])->getBody()->getContents();
            $decode = json_decode($req);
            $someData = array_merge($someData, $decode["data"]);
        }
        echo("\nScript creating users array worked succesfully\n");
        return $someData;
    }
    public function name():string
    {
        return "Script creating users array worked succesfully";
    }


}