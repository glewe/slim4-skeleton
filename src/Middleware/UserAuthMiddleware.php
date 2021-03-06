<?php

namespace App\Middleware;

use App\Domain\User\Data\UserAuthData;
use App\Domain\User\Service\UserAuth;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

/**
 * Middleware.
 */
final class UserAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var UserAuth
     */
    private $auth;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * The constructor.
     *
     * @param SessionInterface $session The session
     * @param UserAuth $auth The user auth
     * @param ResponseFactoryInterface $responseFactory The response factory
     */
    public function __construct(
        SessionInterface $session,
        UserAuth $auth,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->session = $session;
        $this->auth = $auth;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->session->get('user');

        if ($user instanceof UserAuthData) {
            // User is logged in
            $this->auth->setUser($user);

            return $handler->handle($request);
        }

        // User is not logged in
        // Redirect to login page
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->fullUrlFor($request->getUri(), 'login');

        return $this->responseFactory->createResponse()->withHeader('Location', $url)->withStatus(302);
    }
}
