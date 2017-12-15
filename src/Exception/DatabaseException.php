<?php
namespace Lapaz\QuickBrownFox\Exception;

use Doctrine\DBAL\DBALException;

/**
 * Database exception, actually uncheck version wrapper of DBALException.
 */
class DatabaseException extends \RuntimeException implements QuickBrownFoxException
{
    /**
     * Creates DatabaseException from DBALException.
     *
     * @param DBALException $e Real exception from DBAL
     */
    public static function fromDBALException(DBALException $e)
    {
        throw new static($e->getMessage(), $e->getCode(), $e);
    }
}
