<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\OAuth\UseCases\Commands\Login\Command as LoginCommand;
use App\Application\OAuth\UseCases\Commands\Login\Handler as LoginHandler;
use App\Application\OAuth\UseCases\Commands\Logout\Command as LogoutCommand;
use App\Application\OAuth\UseCases\Commands\Logout\Handler as LogoutHandler;
use App\Application\OAuth\UseCases\Commands\Register\Command as RegisterCommand;
use App\Application\OAuth\UseCases\Commands\Register\Handler as RegisterHandler;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * Не используется, так как регистрация пользователя через API не предусмотрена.
     * @param Request         $request
     * @param RegisterHandler $registerHandler
     *
     * @return JsonResponse
     */
    /*public function register(Request $request, RegisterHandler $registerHandler): JsonResponse
    {
        $validated = $request->validate([
                               'name' => 'required|string',
                               'email' => 'required|email|unique:users,email',
                               'password' => 'required|min:8',
                           ]);

        try {
            $result = $registerHandler->handle(new RegisterCommand($validated['name'], $validated['email'], $validated['password']));

            return response()->json($result, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }*/

    /**
     * @param Request      $request
     * @param LoginHandler $handler
     *
     * @return JsonResponse
     */
    public function login(Request $request, LoginHandler $handler): JsonResponse
    {
        $validated = $request->validate([
                               'email' => 'required|email',
                               'password' => 'required',
                           ]);
        try {
            $result = $handler->handle(new LoginCommand($validated['email'], $validated['password']));

            return response()->json($result, Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }


    /**
     * @param Request       $request
     * @param LogoutHandler $handler
     *
     * @return JsonResponse
     */
    public function logout(Request $request, LogoutHandler $handler): JsonResponse
    {
        try {
            $tokenId = $request->user()->token()->id;

            $handler->handle(new LogoutCommand($tokenId));
            return response()->json(['message' => 'Logged out']);

        } catch (Exception) {
            return response()->json(['message' => 'Logout failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
