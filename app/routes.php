<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\HomeController;
use App\Application\Actions\ImportController;
use App\Application\Actions\Book\ListLineEditAction;
use App\Application\Actions\Book\ListLinesAction;
use App\Application\Actions\Book\ListTableAction;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', HomeController::class . ':home')->setName('home');

    $app->group('/livre', function (Group $group) {
        $group->get('', ListTableAction::class)->setName('book');
        $group->get('/lignes', ListLinesAction::class)->setName('lines');
        $group->any('/lignes/{id}', ListLineEditAction::class)->setName('line');
    });
    $app->group('/imports', function (Group $group) {
        $group->get('', ImportController::class . ':home')->setName('imports');
        $group->post('/paypal', ImportController::class . ':paypal')->setName('paypal');
        $group->post('/sg', ImportController::class . ':sg')->setName('sg');
        $group->post('/sogecom', ImportController::class . ':sogecom')->setName('sogecom');
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
