<?php
namespace Lapaz\QuickBrownFox\Exception;

use Doctrine\DBAL\DBALException;

class DatabaseException extends \RuntimeException implements QuickBrownFoxException
{
    /**
     * @param DBALException $e
     */
    public static function fromDBALException(DBALException $e)
    {
        throw new static($e->getMessage(), $e->getCode(), $e);
    }
}
