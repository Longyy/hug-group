<?php
namespace Paf\Estate\Exceptions;

use ErrorException;
use Paf\LightService\Core\Message\Response\Response;
use Paf\LightService\Core\Constant\Status;

class Handler
{
    /**
     * Bootstrap the given application.
     *
     * @return void
     */
    public static function register()
    {
        error_reporting(-1);
        set_error_handler([new Handler, 'handleError']);
        set_exception_handler([new Handler, 'handleException']);
        register_shutdown_function([new Handler, 'handleShutdown']);
        if (env('APP_ENV') != 'testing') {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * Convert a PHP error to an ErrorException.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public static function handleError($iLevel, $sMessage, $sFile = '', $iLine = 0, $aContext = [])
    {
        if (error_reporting() & $iLevel) {
            throw new ErrorException($sMessage, 0, $iLevel, $sFile, $iLine);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param  \Exception  $oException
     * @return void
     */
    public static function handleException($oException)
    {
        error_log((string) $oException);
        app('syslog')->error((string) $oException);

        $oServiceReflector = new \ReflectionProperty(app(), 'service_');
        $oServiceReflector->setAccessible(true);
        $oService = $oServiceReflector->getValue();

        $oProtocolReflector = new \ReflectionProperty($oService, 'protocol_');
        $oProtocolReflector->setAccessible(true);
        $oProtocol = $oProtocolReflector->getValue($oService);
        echo $oProtocol->encodeResponse(
            Response::error([
                'code'    => Status::INTERNAL_ERROR,
                'message' => $oException->getMessage(),
            ])
        );
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public static function handleShutdown()
    {
        if (!is_null($aError = error_get_last()) && static::isFatal($aError['type'])) {
            static::handleException(static::fatalExceptionFromError($aError));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param  array  $aError
     * @return \Symfony\Component\Debug\Exception\FatalErrorException
     */
    protected static function fatalExceptionFromError(array $aError)
    {
        return new FatalErrorException(
            $aError['message'], $aError['type'], 0, $aError['file'], $aError['line']
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $iType
     * @return bool
     */
    protected static function isFatal($iType)
    {
        return in_array($iType, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}
