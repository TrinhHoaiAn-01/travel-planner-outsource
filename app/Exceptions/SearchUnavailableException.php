<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class SearchUnavailableException extends ServiceUnavailableHttpException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(30, 'Tìm kiếm hiện chưa sẵn sàng. Vui lòng thử lại sau.', $previous);
    }
}
