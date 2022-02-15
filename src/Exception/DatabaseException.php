<?php
namespace Lapaz\QuickBrownFox\Exception;

use Doctrine\DBAL\Exception;

/**
 * Database exception, actually uncheck version wrapper of DBALException.
 */
class DatabaseException extends \RuntimeException implements QuickBrownFoxException
{
    /**
     * Creates DatabaseException from DBALException.
     *
     * @param Exception $e Real exception from DBAL
     */
    public static function fromDBALException(Exception $e)
    {
        throw new static($e->getMessage(), $e->getCode(), $e);
    }
}
