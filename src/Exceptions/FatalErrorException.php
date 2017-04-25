<?php
namespace Hug\Group\Exceptions;

use ErrorException;

class FatalErrorException extends ErrorException
{
    public function __construct($sMessage, $iCode, $iSeverity, $sFileName, $iLineno, $iTraceOffset = null, $bTraceArgs = true)
    {
        parent::__construct($sMessage, $iCode, $iSeverity, $sFileName, $iLineno);

        if (null !== $iTraceOffset) {
            if (function_exists('xdebug_get_function_stack')) {
                $aTrace = xdebug_get_function_stack();
                if (0 < $iTraceOffset) {
                    array_splice($aTrace, -$iTraceOffset);
                }

                foreach ($aTrace as &$aFrame) {
                    if (!isset($aFrame['type'])) {
                        //  XDebug pre 2.1.1 doesn't currently set the call type key http://bugs.xdebug.org/view.php?id=695
                        if (isset($aFrame['class'])) {
                            $aFrame['type'] = '::';
                        }
                    } elseif ('dynamic' === $aFrame['type']) {
                        $aFrame['type'] = '->';
                    } elseif ('static' === $aFrame['type']) {
                        $aFrame['type'] = '::';
                    }

                    // XDebug also has a different name for the parameters array
                    if (!$bTraceArgs) {
                        unset($aFrame['params'], $aFrame['args']);
                    } elseif (isset($aFrame['params']) && !isset($aFrame['args'])) {
                        $aFrame['args'] = $aFrame['params'];
                        unset($aFrame['params']);
                    }
                }

                unset($aFrame);
                $aTrace = array_reverse($aTrace);
            } else {
                $aTrace = [];
            }

            $this->setTrace($aTrace);
        }
    }

    protected function setTrace($aTrace)
    {
        $oTraceReflector = new \ReflectionProperty('Exception', 'trace');
        $oTraceReflector->setAccessible(true);
        $oTraceReflector->setValue($this, $aTrace);
    }
}
