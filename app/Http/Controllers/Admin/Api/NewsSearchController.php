<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Api;

use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NewsSearchController extends Controller
{
    public function __construct(
        private NewsRepositoryInterface $newsRepository
    ) {
        $this->middleware('auth');
        // Передаем роль admin для доступа к API
        $this->middleware('admin_panel_access:admin');
    }

    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('comment.viewAny');

        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 10), 50); // Максимум 50 результатов

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $news = $this->newsRepository->searchForAutocomplete($query, $limit);

        return response()->json($news);
    }
}
