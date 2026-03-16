<?php

namespace App\Controller;

use App\Event\EventHandler;
use App\Statistics\StatisticsManager;
use Pecee\Http\Request;
use Pecee\Http\Response;
use Pecee\SimpleRouter\SimpleRouter as Router;

readonly class ApiController
{

    public function __construct(
        private EventHandler $eventHandler,
        private StatisticsManager $statisticsManager,
    ) {}

    public function handleEvent(Request $request, Response $response): void
    {
        $data = $request->getInputHandler()->all();

        try {
            $results = $this->eventHandler->handleEvent($data);
        } catch (\Exception $e) {
            $response->httpCode(400);
            $response->json(['error' => $e->getMessage()]);
            return;
        }

        $response->httpCode(201);
        $response->json($results);
    }

    public function statistics(Request $request, Response $response): void
    {
        $matchId = $request->getInputHandler()->get('match_id')?->getValue();
        $teamId = $request->getInputHandler()->get('team_id')?->getValue();

        if (!$matchId) {
            $response->httpCode(400);
            $response->json(['error' => 'match_id is required']);
        }

        $results = [];
        if ($teamId) {
            $results = $this->statisticsManager->getTeamStatistics($matchId, $teamId);

            $response->json([
                'match_id' => $matchId,
                'team_id' => $teamId,
                'statistics' => $results
            ]);
        } else {
            $results = $this->statisticsManager->getMatchStatistics($matchId);

            $response->json([
                'match_id' => $matchId,
                'statistics' => $results
            ]);
        }
    }
}