<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCommentRequest;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Exception;
use App\Application\Core\Comment\Exceptions\CommentSaveException;
use App\Application\Core\Comment\UseCases\Commands\Create\Command;
use App\Application\Core\Comment\UseCases\Commands\Create\Handler;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCommentRequest $request, Handler $handler, AuthManager $authManager): RedirectResponse
    {
        try {
            $command = new Command(
                text:        $request->get('text'),
                authorId:    $authManager->user()->getAuthIdentifier(),
                newsId:      (int)$request->get('news_id'),
                parentId:    $request->filled('parent_id') ? (int)$request->get('parent_id') : null,
            );

            $handler->handle($command);

            return redirect()->back()->with('success', 'Комментарий будет успешно добавлен после модерации!');
        } catch (CommentSaveException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());

        }  catch (Exception) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Произошла непредвиденная ошибка при создании комментария. Попробуйте позже.');
        }
    }
}
