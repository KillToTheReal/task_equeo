<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\EqueoClient;
use Ekvio\Integration\Sdk\V2\User\UserApi;
use Ekvio\Integration\Sdk\V2\User\UserSearchCriteria;
use RuntimeException;

/**
 * Class ExportDataCollector
 * @package App
 */
class GetUsersWithPersonalAssignments implements Invoker
{
    private Profiler $profiler;
    private UserApi $user;
    private EqueoClient $client;

    const BLOCKED_TEAM = "Технические УЗ";

    public function __construct(Profiler $profiler, EqueoClient $client)
    {
        $this->profiler = $profiler;
        $this->user = new UserApi($client);
        $this->client = $client;
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Get personal assignment login...");
        $assignments = $this->client->pagedRequest('GET',"/v2/personals/assignments");
        $logins = array_column($assignments, 'login');
        if(empty($logins)) {
            throw new RuntimeException('Assigned accounts not found');
        }
        $this->profiler->profile(sprintf("Got %s users...", count($logins)));
        return $this->searchUsers($logins);
    }

    /**
     * @param array $logins
     * @return array
     */
    private function searchUsers(array $logins): array
    {
        $searchData = [];
        $chunkLogins = array_chunk($logins, 500);

        /** @var array<int> $chunkLogin */
        foreach ($chunkLogins as $chunkLogin) {
            $usersInfo = $this->getUsersInfoFromIntegrationApi($chunkLogin);
            $mappedUserFields = $this->mapUserFields($usersInfo);
            $searchData = array_merge($searchData, $mappedUserFields);
        }
        return $searchData;
    }

    /**
     * @param array $logins
     * @return array
     */
    private function getUsersInfoFromIntegrationApi(array $logins): array
    {
        $searchInfo = [];
        $searchInApi = $this->user->search(UserSearchCriteria::createFrom([
            'params' => [
                'include' => ['groups', 'forms']
            ],
            'filters' => [
                'login' =>
                    $logins
            ]
        ]));

        /** @var array $search */
        foreach ($searchInApi as $search) {
            $groups = $this->mapUserGroups($search['groups']);
            if($groups['team'] !== self::BLOCKED_TEAM) {
                $searchInfo[] = $search;
            }
        }
        return $searchInfo;
    }



    private function mapUserFields(array $usersInfo)
    {
        $accounts = [];
        foreach ($usersInfo as $account) {
            $groups = $this->mapUserGroups($account['groups']);

            $accounts[] = [
                $account['login'],
                $account['last_name'],
                $account['first_name'],
                $groups['role'],
                $groups['region'],
                $groups['city'],
                $groups['position'],
                $groups['team'],
                $groups['department'],
                $groups['function'],
                $account['phone'],
                $account['email'],
                $account['status'] === 'active'? 0: 1,
                $account['chief_email'],
                (int) $account['whitelist']
            ];
        }
        return $accounts;
    }

    private function mapUserGroups(array $groups): array
    {
        $mappedGroups = [];
        $groupsData = $groups['data'];
        foreach ($groupsData as $group){
            $type = $group['type'];
            $value = $group['name'];
            $mappedGroups[$type] = $value;
        }
        return $mappedGroups;
    }

    public function name(): string
    {
        return 'Export data collector';
    }
}