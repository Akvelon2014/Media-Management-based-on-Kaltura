<?php
require_once 'DWHInspector.php';

class RegisterFilesTestCase extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		DWHInspector::purgeCycles(false);
		DWHInspector::cleanEtlServers();
		DWHInspector::dropTablePartitions('kalturadw_ds','ds_events');
		DWHInspector::dropTablePartitions('kalturadw_ds','ds_bandwidth_usage');
	}

	public function testBasicRegister()
	{
		$fileName = "test1";
		$processId = 1;
		$fileSize = 1000;
		$compressionSuffix = "gz";
		$subdir = ".";
		$server1 = "server1";
		DWHInspector::registerEtlServer($server1);
		DWHInspector::registerFile($fileName, $processId, $fileSize, $compressionSuffix, $subdir);
		$this->assertTrue(DWHInspector::isFileRegistered($fileName, $processId, $fileSize, $compressionSuffix, $subdir, $server1));
	}

	public function testBasicRegisterTwoServers()
        {
                $fileName1 = "test1";
                $fileName2 = "test2";
                $processId = 1;
                $fileSize = 1000;
                $compressionSuffix = "gz";
                $subdir = ".";
                $server1 = "server1";
                $server2 = "server2";
                DWHInspector::registerEtlServer($server1);
                DWHInspector::registerFile($fileName1, $processId, $fileSize, $compressionSuffix, $subdir);
                DWHInspector::registerEtlServer($server2);
                DWHInspector::registerFile($fileName2, $processId, $fileSize, $compressionSuffix, $subdir);
                $this->assertTrue(DWHInspector::isFileRegistered($fileName1, $processId, $fileSize, $compressionSuffix, $subdir, $server1));
                $this->assertTrue(DWHInspector::isFileRegistered($fileName2, $processId, $fileSize, $compressionSuffix, $subdir, $server2));
        }

	public function testBasicRegisterTwoServersDifferentLB()
        {
                $fileName1 = "test1";
                $fileName2 = "test2";
                $processId = 1;
                $fileSize = 1000;
                $compressionSuffix = "gz";
                $subdir = ".";
                $server1 = "server1";
                $server2 = "server2";
		$server1lb = 1;
		$server2lb = 2;
                DWHInspector::registerEtlServer($server1, $server1lb);
                DWHInspector::registerEtlServer($server2, $server2lb);
                DWHInspector::registerFile($fileName1, $processId, $fileSize, $compressionSuffix, $subdir);
                DWHInspector::registerFile($fileName2, $processId, $fileSize, $compressionSuffix, $subdir);
                $this->assertTrue(DWHInspector::isFileRegistered($fileName1, $processId, $fileSize, $compressionSuffix, $subdir, $server2));
                $this->assertTrue(DWHInspector::isFileRegistered($fileName2, $processId, $fileSize, $compressionSuffix, $subdir, $server1));
        }

	public function testRegisterTwoServersDifferentSizes()
        {
                $fileName1 = "test1";
                $fileName2 = "test2";
                $fileName3 = "test3";
                $processId = 1;
                $fileSize1K = 1000;
                $fileSize2K = 2000;
                $compressionSuffix = "gz";
                $subdir = ".";
                $server1 = "server1";
                $server2 = "server2";
                DWHInspector::registerEtlServer($server1);
                DWHInspector::registerFile($fileName1, $processId, $fileSize2K, $compressionSuffix, $subdir);
                DWHInspector::registerEtlServer($server2);
                DWHInspector::registerFile($fileName2, $processId, $fileSize1K, $compressionSuffix, $subdir);
                DWHInspector::registerFile($fileName3, $processId, $fileSize1K, $compressionSuffix, $subdir);
                $this->assertTrue(DWHInspector::isFileRegistered($fileName1, $processId, $fileSize2K, $compressionSuffix, $subdir, $server1));
                $this->assertTrue(DWHInspector::isFileRegistered($fileName2, $processId, $fileSize1K, $compressionSuffix, $subdir, $server2));
                $this->assertTrue(DWHInspector::isFileRegistered($fileName3, $processId, $fileSize1K, $compressionSuffix, $subdir, $server2));
        }
	
	public function testRegisterTwoServersDifferentSizesDifferentLB()
        {
                $fileName1 = "test1";
                $fileName2 = "test2";
                $fileName3 = "test3";
                $processId = 1;
                $fileSize2K = 2000;
                $compressionSuffix = "gz";
                $subdir = ".";
                $server1 = "server1";
                $server2 = "server2";
		$server1lb = 1;
                $server2lb = 2;
                DWHInspector::registerEtlServer($server1, $server1lb);
                DWHInspector::registerFile($fileName1, $processId, $fileSize2K, $compressionSuffix, $subdir);
                DWHInspector::registerEtlServer($server2, $server2lb);		
                DWHInspector::registerFile($fileName2, $processId, $fileSize2K, $compressionSuffix, $subdir);
                DWHInspector::registerFile($fileName3, $processId, $fileSize2K, $compressionSuffix, $subdir);
                $this->assertTrue(DWHInspector::isFileRegistered($fileName1, $processId, $fileSize2K, $compressionSuffix, $subdir, $server1));
                $this->assertTrue(DWHInspector::isFileRegistered($fileName2, $processId, $fileSize2K, $compressionSuffix, $subdir, $server2));
                $this->assertTrue(DWHInspector::isFileRegistered($fileName3, $processId, $fileSize2K, $compressionSuffix, $subdir, $server2));
        }

	public static function tearDownAfterClass()
	{
		DWHInspector::cleanEtlServers();
	}

}

?>
