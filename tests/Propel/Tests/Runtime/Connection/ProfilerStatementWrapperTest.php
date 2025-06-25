<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Connection;

use PDO;
use Propel\Runtime\Connection\PdoConnection;
use Propel\Runtime\Connection\ProfilerConnectionWrapper;
use Propel\Runtime\Connection\ProfilerStatementWrapper;
use Propel\Runtime\Util\Profiler;
use Propel\Tests\Helpers\BaseTestCase;

class ProfilerStatementWrapperTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testProfilerStartCalled()
    {
        $profiler = new class() extends Profiler {
            public int $startCount = 0;

            public function start(): void
            {
                $this->startCount++;
            }
        };

        $pdo = new PdoConnection('sqlite::memory:');
        $con = new ProfilerConnectionWrapper($pdo);
        $con->setProfiler($profiler);

        $stmt = $con->prepare('SELECT :p1 AS col1, :p2 AS col2');
        $profiler->startCount = 0; // ignore call from prepare

        $value1 = 1;
        $stmt->bindValue(':p1', $value1, PDO::PARAM_INT);
        $this->assertSame(1, $profiler->startCount, 'bindValue() should call profiler->start()');

        $value2 = 2;
        $stmt->bindParam(':p2', $value2, PDO::PARAM_INT);
        $this->assertSame(2, $profiler->startCount, 'bindParam() should call profiler->start()');

        $stmt->execute();
        $this->assertSame(3, $profiler->startCount, 'execute() should call profiler->start()');
    }
}
