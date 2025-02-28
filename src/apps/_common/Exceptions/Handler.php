<?php

namespace Common\Exceptions;

use Common\Traits\Response\Send;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use JPush\Exceptions\APIRequestException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Yunhan\Utils\Env;

class Handler extends ExceptionHandler
{
    use Send;
    /**
     * A list of the exception types that should not be reported.
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ApiException::class,
        // jwt-auth token黑名单&token过期
        TokenBlacklistedException::class,
        TokenExpiredException::class,
        // rule
        RuleException::class,
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     * @param \Exception $e
     * @return void
     * @throws Exception
     */
    public function report(Exception $e)
    {
        parent::report($e);

        if (!$this->shouldntReport($e)) {
            DingHelper::notice(EmailHelper::warpException($e), '系统异常 - ' . app()->environment());
            $this->sendErrorMail($e);
        }
    }

    /**
     * @param Exception $exception
     */
    private function sendErrorMail(Exception $exception)
    {
        if (!Env::isDev()) {
//            EmailHelper::sendException($exception, '系统升级中，请稍后再试');
            \Log::info($exception);
            abort(500, '系统升级中，请稍后再试');
        }
    }

    /**
     * Render an exception into an HTTP response.
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\JsonResponse|Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpResponseException) {
            // return $e->getResponse();
        } elseif ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException(403, $e->getMessage());
        } elseif ($e instanceof ValidationException && $e->getResponse()) {
            // return $e->getResponse();
        } elseif ($e instanceof FatalErrorException) {
            $this->clearOutput();
        } elseif ($e instanceof ApiException) {
            $code = $e->getCode() ?: 13000;
            $e = new HttpException($code, $e->getMessage());
        }
        $code = ($e instanceof HttpExceptionInterface) ? $e->getStatusCode() : 13000;
        $msg = $e->getMessage() ?: $code . ' ' . self::getStatusText($e->getStatusCode());
        $error = [];
        if (!Env::isProd()) {
            $error = [
                'file' => $e->getFile() . ':' . $e->getLine(),
                'class' => get_class($e),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        }
        return response()->json($this->result(
            $code,
            $msg,
            $error
        ), 200);
    }

    /**
     * @param $status
     * @return array|mixed|string
     */
    public static function getStatusText($status)
    {
        return Response::$statusTexts[$status] ?? 'unknown status';
    }

    /**
     * Removes all output echoed before calling this method.
     */
    public function clearOutput()
    {
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }
}
