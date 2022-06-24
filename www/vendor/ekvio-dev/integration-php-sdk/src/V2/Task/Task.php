<?php
declare(strict_types=1);

namespace Ekvio\Integration\Sdk\V2\Task;

use Ekvio\Integration\Sdk\ApiException;
use Ekvio\Integration\Sdk\V2\EqueoClient;

/**
 * Class Answer
 * @package Ekvio\Integration\Sdk\V2\Task
 */
class Task implements TaskStatistic
{
    private const TASKS_STATISTIC_ENDPOINT = '/v2/tasks/statistic';
    /**
     * @var EqueoClient
     */
    private $client;

    /**
     * Material constructor.
     * @param EqueoClient $client
     */
    public function __construct(EqueoClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $criteria
     * @return array
     * @throws ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function statistic(array $criteria = []): array
    {
        $fields = [];
        if(array_key_exists('tasks', $criteria)) {
            $fields['tasks'] = $criteria['tasks'];
        }

        if(array_key_exists('from_date', $criteria)) {
            $fields['from_date'] = $criteria['from_date'];
        }

        if(array_key_exists('user_status', $criteria)) {
            $fields['user_status'] = $criteria['user_status'];
        }

        if(array_key_exists('task_status', $criteria)) {
            $fields['task_status'] = $criteria['task_status'];
        }

        if(array_key_exists('answer_status', $criteria)) {
            $fields['answer_status'] = $criteria['answer_status'];
        }

        $response = $this->client->request('GET', self::TASKS_STATISTIC_ENDPOINT, $fields);

        if(isset($response['errors'])) {
            ApiException::apiErrors($response['errors']);
        }

        $integration = (int) $response['data']['integration'];
        $content = $this->client->integration($integration);

        if(isset($response['errors'])) {
            ApiException::apiErrors($response['errors']);
        }

        return $content['data'];
    }
}