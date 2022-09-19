<?php

/*
 * This file is part of the tbcd/doctrine-hfsql-driver package.
 *
 * (c) Thomas Beauchataud <thomas.beauchataud@yahoo.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TBCD\Doctrine\HFSQLDriver;

use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use TBCD\Doctrine\HFSQLDriver\Exception\Exception;

final class Statement implements StatementInterface
{

    /**
     * @var mixed
     */
    private mixed $statement;

    /**
     * @var array
     */
    private array $bindValues = [];

    /**
     * @param mixed $statement
     */
    public function __construct(mixed $statement)
    {
        $this->statement = $statement;
    }


    /**
     * @inheritDoc
     */
    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        assert(is_int($param));
        $this->bindValues[$param] = $value;
    }

    /**
     * @inheritDoc
     */
    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): ?bool
    {
        return $this->bindValue($param, $variable, $type);
    }

    /**
     * @inheritDoc
     */
    public function execute($params = null): ResultInterface
    {
        foreach (($params ?? []) as $paramId => $paramValue) {
            $this->bindValues[$paramId] = $paramValue;
        }

        $result = odbc_execute($this->statement, $this->bindValues);
        if (!$result) {
            throw new Exception(odbc_errormsg($this->statement), odbc_error($this->statement));
        }

        return new Result($result);
    }
}