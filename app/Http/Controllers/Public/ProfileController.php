<?php

namespace App\Http\Controllers\Public;

use App\Application\Core\Profile\Exceptions\ProfileInvalidCurrentPasswordException;
use App\Application\Core\Profile\Exceptions\ProfileNotFoundException;
use App\Application\Core\Profile\UserCases\Commands\Delete\Command as DeleteCommand;
use App\Application\Core\Profile\UserCases\Commands\Delete\Handler as DeleteHandler;
use App\Application\Core\Profile\UserCases\Commands\Update\Command as UpdateCommand;
use App\Application\Core\Profile\UserCases\Commands\Update\Handler as UpdateHandler;
use App\Application\Core\Profile\UserCases\Queries\FetchyByUserId\Fetcher as ProfileFetcher;
use App\Application\Core\Profile\UserCases\Queries\FetchyByUserId\Query as ProfileQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Http\Requests\DeleteProfileRequest;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//use Exception;

class ProfileController extends Controller
{
    public function edit(AuthManager $authManager, ProfileFetcher $fetcher): View
    {
        $userId = $authManager->user()->getAuthIdentifier();

        if (! $userId) {
            throw new NotFoundHttpException('Профиль не найден');
        }

        try {
            $query = new ProfileQuery((int)$userId);
            $user = $fetcher->fetch($query);

        } catch (ProfileNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user profile.
     */
    public function update(UpdateProfileRequest $request, AuthManager $authManager, UpdateHandler $updateNewsUseCase): RedirectResponse
    {
        $userId = $authManager->user()->getAuthIdentifier();

       /* if (! $userId) {
            throw new NotFoundHttpException('Профиль не найден');
        }*/
        try {

            $command = new UpdateCommand(
                id: $userId,
                name: $request->get('name'),
                email: $request->get('email'),
                current_password: $request->filled('password') ? $request->current_password : null,
                password: $request->filled('password') ? $request->password : null,
            );

            $updateNewsUseCase->handle($command);
        } catch (ProfileNotFoundException | ProfileInvalidCurrentPasswordException $e) {
            return back()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('profile.edit')
                         ->with('success', 'Профиль успешно обновлен');
    }

    /**
     * Delete the user profile.
     */
    public function destroy(DeleteProfileRequest $request, AuthManager $authManager, DeleteHandler $deleteNewsUseCase): RedirectResponse
    {
        $userId = $authManager->user()->getAuthIdentifier();

      /*  if (! $userId) {
            throw new NotFoundHttpException('Профиль не найден');
        }*/

        try {
            $command = new DeleteCommand(
                $userId,
                $request->get('password')
            );

            $deleteNewsUseCase->handle($command);

            $authManager->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

        } catch (ProfileNotFoundException | ProfileInvalidCurrentPasswordException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect('/');
    }
}
