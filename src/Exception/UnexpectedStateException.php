<?php
namespace Lapaz\QuickBrownFox\Exception;

use RuntimeException;

/**
 * This exception is thrown when something run with wrong state.
 */
class UnexpectedStateException extends RuntimeException implements QuickBrownFoxException
{

}
