<?php

namespace App\Http\Controllers\Admin;

use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * @return View
     */
    public function __invoke(
        UserRepositoryInterface $userRepository,
        NewsRepositoryInterface $newsRepository,
        CategoryRepositoryInterface $categoryRepository,
        CommentRepositoryInterface $commentRepository,
    ): View
    {
        $totalUsers = $userRepository->count();
        $totalNews = $newsRepository->count();
        $totalCategories = $categoryRepository->count();
        $totalComments = $commentRepository->count();

        return view('admin.dashboard', compact(
        'totalUsers', 'totalNews', 'totalCategories', 'totalComments'));
    }
}
