<?php
namespace Lapaz\QuickBrownFox\Exception;

use RuntimeException;

/**
 * This exception is thrown when generator attempts to create random value for unsupported type.
 */
class UnsupportedTypeException extends RuntimeException implements QuickBrownFoxException
{

}
