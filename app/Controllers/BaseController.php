<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\PengaturanModel;
use App\Models\TamuModel;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 */
abstract class BaseController extends Controller
{
    /**
     * Loaded application settings (key => value).
     *
     * @var array<string, string>
     */
    protected array $pengaturan = [];

    /**
     * Current count of unread tamu records.
     */
    protected int $unreadCount = 0;

    /**
     * Helpers loaded globally.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'html'];

    /**
     * @param CLIRequest|IncomingRequest $request
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load helpers before parent init so they are available in parent.
        parent::initController($request, $response, $logger);

        // Force MySQL session to WITA (UTC+8) so CURRENT_TIMESTAMP and CURDATE
        // use the application's timezone regardless of server configuration.
        \Config\Database::connect()->query("SET time_zone = '+08:00'");

        $pengaturanModel = new PengaturanModel();
        $this->pengaturan = $pengaturanModel->getAll();

        $tamuModel = new TamuModel();
        $this->unreadCount = $tamuModel->countUnread();
    }
}
