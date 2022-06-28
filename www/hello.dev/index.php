<?php

declare(strict_types=1);
use App\ApiRequester;
use App\FileSender;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\EqueoClient;
use Ekvio\Integration\Sdk\V2\Integration\HttpIntegrationResult;
use Ekvio\Integration\Sdk\V2\Integration\IntegrationResult;
use Ekvio\Integration\Skeleton\Adapter;
use Ekvio\Integration\Skeleton\EnvironmentConfiguration;
use Ekvio\Integration\Skeleton\Invoker\Composite;
use GuzzleHttp\Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Sftp\SftpAdapter;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
require_once __DIR__.'/../vendor/autoload.php';

// Переменные окружения из файла
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$appRoot = __DIR__;
$tmpDir = 'tmp';
// Это было здесь до меня
$config = array_merge_recursive(EnvironmentConfiguration::create(), [
    'services' => [
        ClientInterface::class => function () {
            return new Client([
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'http_errors' => false,
                'debug' => (bool) getenv('HTTP_CLIENT_DEBUG'),
                'verify' => false
            ]);
        },
        IntegrationResult::class => function () {
            return new HttpIntegrationResult();
        },
        EqueoClient::class => Di\autowire()
            ->constructorParameter('host', getenv('INTEGRATION_HOST'))
            ->constructorParameter('token', getenv('INTEGRATION_TOKEN'))
            ->constructorParameter('options', [
                'request_interval_timeout' => getenv('INTEGRATION_UPDATE_STATUS_RATE_TIME'),
                'debug' => getenv('APPLICATION_DEBUG'),
                'debug_request_body' => getenv('APPLICATION_DEBUG_REQUEST_BODY')
            ]),
        FilesystemInterface::class => function () use ($appRoot) {
            return new Filesystem(new Local($appRoot));
        },
        SftpAdapter::class => function () {
            return new SftpAdapter([
                'host' => getenv('INTEGRATION_SFTP_HOST'),
                'port' => getenv('INTEGRATION_SFTP_PORT'),
                'username' => getenv('INTEGRATION_SFTP_USER'),
                'password' => getenv('INTEGRATION_SFTP_PASSWORD'),
                'root' => getenv('INTEGRATION_SFTP_ROOT_DIR'),
                //'privateKey' => '', //ssh-key
                'timeout' => 30
            ]);
        },
        GetUsersWithPersonalAssignments::class => function(ContainerInterface $dic) {
            return new GetUsersWithPersonalAssignments(
                $dic->get(Profiler::class),
                $dic->get(EqueoClient::class)
            );
        },
        CsvReportGenerator::class => function (ContainerInterface $dic) {
            $csvHeader = [
                "USR_LOGIN",
                "USR_LAST_NAME",
                "USR_FIRST_NAME",
                "ROLE",
                "REGION_NAME",
                "CITY_NAME",
                "POSITION_NAME",
                "TEAM_NAME",
                "DEPARTAMENT_NAME",
                "ASSIGNMENT_NAME",
                "USR_MOBILE",
                "USR_EMAIL",
                "USR_UDF_USER_FIRED",
                "MANAGER_EMAIL",
                "WHITE_LIST"
            ];

            return new CsvReportGenerator(
                $dic->get(Profiler::class),
                $csvHeader
            );
        },
        DataExporter::class => function(ContainerInterface $dic) use ($sftpReportPath) {
            return new DataExporter(
                $dic->get(Profiler::class),
                new Filesystem(
                    $dic->get(SftpAdapter::class)
                ),
                $sftpReportPath
            );
        }
    ]
]);


$keyBr = "nwXfgwxxOLfE9w0joPSf4NAyab04T3Us";
$config["name"]="Api sender to mail.";
$config["company"]="None";
$array2 = array(
    "url"=>"https://integration.hotfix-prod.ru",
    "headers"=>[
        "Authorization" => "Bearer ".$keyBr,
        "Accept"=>"application/json",
        "Connection" => "keep-alive",
    ],
    "params" => ["per_page" => 200,"in_whitelist"=>"true","page" => 1]
    );

(new Adapter($config))->run(Composite::class, [ApiRequester::class=>$array2,
    FileSender::class=>[]]);



