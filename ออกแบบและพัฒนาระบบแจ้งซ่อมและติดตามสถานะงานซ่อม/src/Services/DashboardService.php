<?php

namespace App\Services;

use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Repositories\RatingRepository;
use App\Repositories\CategoryRepository;

/**
 * Dashboard Analytics & Reporting Service
 * Matching Sequence Diagram 6.3
 */
class DashboardService
{
    private TicketRepository $ticketRepo;
    private UserRepository $userRepo;
    private RatingRepository $ratingRepo;
    private CategoryRepository $categoryRepo;

    public function __construct(
        ?TicketRepository $ticketRepo = null,
        ?UserRepository $userRepo = null,
        ?RatingRepository $ratingRepo = null,
        ?CategoryRepository $categoryRepo = null
    ) {
        $this->ticketRepo = $ticketRepo ?? new TicketRepository();
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->ratingRepo = $ratingRepo ?? new RatingRepository();
        $this->categoryRepo = $categoryRepo ?? new CategoryRepository();
    }

    public function getStatistics(): array
    {
        $statusCounts = $this->ticketRepo->countByStatus();
        $avgResolutionHours = $this->ticketRepo->avgResolutionTime();
        $topTechnicians = $this->ticketRepo->topTechnicians(5);
        $monthlyRatings = $this->ticketRepo->monthlyRatings();
        $recentActivity = $this->ticketRepo->getRecentActivity(8);
        $categoriesWithCount = $this->categoryRepo->allWithCount();
        $userCounts = $this->userRepo->countByRole();
        $avgRating = $this->ratingRepo->getOverallAverage();
        $totalRatings = $this->ratingRepo->getTotalRatings();

        return [
            'statusCounts'        => $statusCounts,
            'totalTickets'        => $statusCounts['total'] ?? 0,
            'openTickets'         => $statusCounts['Open'] ?? 0,
            'inProgressTickets'   => ($statusCounts['Assigned'] ?? 0) + ($statusCounts['InProgress'] ?? 0),
            'resolvedTickets'     => ($statusCounts['Resolved'] ?? 0) + ($statusCounts['Closed'] ?? 0),
            'avgResolutionHours'  => $avgResolutionHours,
            'avgRating'           => $avgRating,
            'totalRatings'        => $totalRatings,
            'topTechnicians'      => $topTechnicians,
            'monthlyRatings'      => $monthlyRatings,
            'recentActivity'      => $recentActivity,
            'categories'          => $categoriesWithCount,
            'userCounts'          => $userCounts,
        ];
    }
}
