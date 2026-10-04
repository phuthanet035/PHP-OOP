<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\View;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Repositories\RatingRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Services\FileUploader;
use App\Services\TicketService;
use App\Services\TicketStatusService;

/**
 * Ticket Controller
 * Implements ticket creation, tracking, status transitions, comments, and ratings
 * Matching Class Diagram 5 & Sequence Diagrams 6.1, 6.2
 */
class TicketController extends BaseController
{
    private TicketRepository $ticketRepo;
    private CategoryRepository $categoryRepo;
    private UserRepository $userRepo;
    private CommentRepository $commentRepo;
    private StatusLogRepository $statusLogRepo;
    private RatingRepository $ratingRepo;
    private TicketService $ticketService;
    private TicketStatusService $statusService;
    private FileUploader $uploader;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->userRepo = new UserRepository();
        $this->commentRepo = new CommentRepository();
        $this->statusLogRepo = new StatusLogRepository();
        $this->ratingRepo = new RatingRepository();
        $this->uploader = new FileUploader();
        $this->ticketService = new TicketService($this->ticketRepo, $this->commentRepo, $this->statusLogRepo, $this->uploader);
        $this->statusService = new TicketStatusService($this->ticketRepo, $this->commentRepo, $this->statusLogRepo, $this->ratingRepo);
    }

    public function index(Request $request): void
    {
        $keyword = trim($request->get('q', ''));
        $status = $request->get('status');
        $priority = $request->get('priority');
        $categoryId = $request->get('category_id');
        $scope = $request->get('scope', 'all');

        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($priority) $filters['priority'] = $priority;
        if ($categoryId) $filters['category_id'] = $categoryId;

        $user = Auth::user();
        $userId = (int) $user['id'];
        $userRole = $user['role'];

        // Role-based scoping: regular user sees own tickets; tech can view assigned or all
        if ($userRole === 'user') {
            $filters['user_id'] = $userId;
        } elseif ($userRole === 'technician' && $scope === 'my_jobs') {
            $filters['technician_id'] = $userId;
        }

        $tickets = $this->ticketRepo->search($keyword, $filters);
        $categories = $this->categoryRepo->all();
        $statusCounts = $this->ticketRepo->countByStatus();

        $this->view('tickets/index', [
            'tickets'      => $tickets,
            'categories'   => $categories,
            'statusCounts' => $statusCounts,
            'keyword'      => $keyword,
            'currentStatus'=> $status,
            'currentPriority' => $priority,
            'currentCategory' => $categoryId,
            'currentScope' => $scope,
        ]);
    }

    public function create(Request $request): void
    {
        $categories = $this->categoryRepo->all();
        $priorities = TicketPriority::cases();

        $this->view('tickets/create', [
            'categories' => $categories,
            'priorities' => $priorities,
        ]);
    }

    public function store(Request $request): void
    {
        $actor = Auth::user();

        $data = [
            'title'       => trim($request->post('title', '')),
            'category_id' => (int) $request->post('category_id', 0),
            'priority'    => $request->post('priority', 'Medium'),
            'description' => trim($request->post('description', '')),
        ];

        $validator = Validator::make($data, [
            'title'       => 'required|min:5|max:200',
            'category_id' => 'required|integer',
            'priority'    => 'required|in:Low,Medium,High,Urgent',
            'description' => 'required|min:10',
        ], $_FILES);

        // Optional image file validation if provided
        if ($request->hasFile('image')) {
            $fileVal = Validator::make([], [
                'image' => 'file_mimes:jpg,jpeg,png,webp|file_max_kb:5120'
            ], $_FILES);
            if ($fileVal->fails()) {
                View::setFlash('error', $fileVal->firstError());
                $this->redirect('/tickets/create');
                return;
            }
        }

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            $this->redirect('/tickets/create');
            return;
        }

        try {
            $ticketId = $this->ticketService->createTicket($data, $request->file('image'), $actor);
            View::setFlash('success', "บันทึกการแจ้งซ่อมรหัส #{$ticketId} เรียบร้อยแล้ว ระบบกำลังส่งต่อให้เจ้าหน้าที่");
            $this->redirect("/tickets/{$ticketId}");
        } catch (\Throwable $e) {
            View::setFlash('error', "เกิดข้อผิดพลาด: " . $e->getMessage());
            $this->redirect('/tickets/create');
        }
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $ticket = $this->ticketRepo->findWithDetails($id);

        if (!$ticket) {
            View::setFlash('error', 'ไม่พบข้อมูลใบแจ้งซ่อมที่ระบุ');
            $this->redirect('/tickets');
            return;
        }

        // Access control: User can only view their own tickets (unless technician or admin)
        $user = Auth::user();
        if ($user['role'] === 'user' && (int) $ticket['user_id'] !== (int) $user['id']) {
            View::setFlash('error', 'คุณไม่มีสิทธิ์เข้าดูใบแจ้งซ่อมของผู้อื่น');
            $this->redirect('/tickets');
            return;
        }

        $comments = $this->commentRepo->findByTicket($id);
        $statusLogs = $this->statusLogRepo->findByTicket($id);
        $technicians = $this->userRepo->findTechnicians();
        $rating = $this->ratingRepo->findByTicket($id);

        $this->view('tickets/show', [
            'ticket'      => $ticket,
            'comments'    => $comments,
            'statusLogs'  => $statusLogs,
            'technicians' => $technicians,
            'rating'      => $rating,
            'currentUser' => $user,
        ]);
    }

    /**
     * AJAX/POST Status Transition Endpoint
     * Matching Sequence Diagram 6.2
     */
    public function updateStatus(Request $request): void
    {
        $ticketId = (int) $request->param('id');
        $ticket = $this->ticketRepo->findWithDetails($ticketId);

        if (!$ticket) {
            if ($request->isAjax() || $request->expectsJson()) {
                Response::json(['success' => false, 'message' => 'ไม่พบข้อมูลใบแจ้งซ่อม'], 404);
            }
            View::setFlash('error', 'ไม่พบข้อมูลใบแจ้งซ่อม');
            $this->redirect('/tickets');
            return;
        }

        $actor = Auth::user();
        $newStatus = $request->input('status');
        $note = trim($request->input('note', ''));
        $comment = trim($request->input('comment', ''));
        $techId = (int) $request->input('technician_id', 0);
        $ratingScore = (int) $request->input('rating_score', 5);
        $ratingFeedback = trim($request->input('rating_feedback', ''));

        // Handle uploaded repair photo if transitioning to Resolved
        $uploadedPhoto = null;
        if ($request->hasFile('repair_photo')) {
            try {
                $uploadedPhoto = $this->uploader->upload($request->file('repair_photo'), 'repair_result');
            } catch (\Throwable $e) {
                if ($request->isAjax() || $request->expectsJson()) {
                    Response::json(['success' => false, 'message' => $e->getMessage()], 422);
                }
                View::setFlash('error', $e->getMessage());
                $this->redirect("/tickets/{$ticketId}");
                return;
            }
        }

        $extra = [
            'note'            => $note,
            'comment'         => $comment ?: $note,
            'technician_id'   => $techId,
            'image_path'      => $uploadedPhoto,
            'rating_score'    => $ratingScore,
            'rating_feedback' => $ratingFeedback,
        ];

        try {
            $this->statusService->transition($ticket, $newStatus, $actor, $extra);

            $msg = "อัปเดตสถานะงานแจ้งซ่อม #{$ticketId} เป็น '{$newStatus}' สำเร็จ";

            if ($request->isAjax() || $request->expectsJson()) {
                $updatedTicket = $this->ticketRepo->findWithDetails($ticketId);
                Response::json([
                    'success' => true,
                    'message' => $msg,
                    'ticket'  => $updatedTicket,
                ]);
            }

            View::setFlash('success', $msg);
            $this->redirect("/tickets/{$ticketId}");
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->expectsJson()) {
                Response::json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            View::setFlash('error', $e->getMessage());
            $this->redirect("/tickets/{$ticketId}");
        }
    }

    public function assignTechnician(Request $request): void
    {
        $ticketId = (int) $request->param('id');
        $techId = (int) $request->post('technician_id', 0);

        if (!Auth::isAdmin()) {
            View::setFlash('error', 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถมอบหมายงานได้');
            $this->redirect("/tickets/{$ticketId}");
            return;
        }

        $ticket = $this->ticketRepo->findWithDetails($ticketId);
        if (!$ticket) {
            View::setFlash('error', 'ไม่พบข้อมูลใบแจ้งซ่อม');
            $this->redirect('/tickets');
            return;
        }

        try {
            $this->statusService->transition($ticket, TicketStatus::Assigned->value, Auth::user(), [
                'technician_id' => $techId,
                'note'          => 'แอดมินมอบหมายงานให้ช่างเทคนิค',
            ]);
            View::setFlash('success', 'มอบหมายงานให้ช่างเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            View::setFlash('error', $e->getMessage());
        }

        $this->redirect("/tickets/{$ticketId}");
    }

    public function addComment(Request $request): void
    {
        $ticketId = (int) $request->param('id');
        $body = trim($request->post('body', ''));
        $actor = Auth::user();

        try {
            $this->ticketService->addComment($ticketId, $body, $request->file('image'), $actor);

            if ($request->isAjax() || $request->expectsJson()) {
                $comments = $this->commentRepo->findByTicket($ticketId);
                Response::json(['success' => true, 'comments' => $comments]);
            }

            View::setFlash('success', 'เพิ่มข้อความแสดงความคิดเห็นแล้ว');
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->expectsJson()) {
                Response::json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            View::setFlash('error', $e->getMessage());
        }

        $this->redirect("/tickets/{$ticketId}");
    }
}
