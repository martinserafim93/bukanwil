<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Redirect guests to the admin login page.
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $path = trim($request->getUri()->getPath(), '/');

        if ($path === 'admin/login') {
            return null;
        }

        if (! session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        return null;
    }

    /**
     * No post-processing required.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
