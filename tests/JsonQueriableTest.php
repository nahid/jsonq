<?php

namespace Nahid\JsonQ\Tests;

use Nahid\JsonQ\Jsonq;
use Nahid\JsonQ\Exceptions\FileNotFoundException;

class JsonQueriableTest extends \PHPUnit_Framework_TestCase
{
    const FILE_NAME = 'data.json';
    
    /**
     * @var Jsonq
     */
    protected $jsonq;
    
    /**
     * @var string
     */
    protected $file;
    
    protected function createFile()
    {
        $json = [
            'level1.1' => [
                'level2.1' => [
                    'level3.1' => 'data31',
                    'level3.2' => 32,
                    'level3.3' => true,
                ],
                'level2.2' => [
                    'level3.4' => 'data34',
                    'level3.5' => 35,
                    'level3.6' => false,
                ],
                'level2.3' => [
                    'level3.7' => 'data37',
                    'level3.8' => 38,
                    'level3.9' => true,
                ]
            ],
            'level1.2' => [
                'level2.4' => [
                    'level3.10' => 'data310',
                    'level3.11' => 311,
                    'level3.12' => true,
                ],
                'level2.5' => [
                    'level3.13' => 'data313',
                    'level3.14' => 314,
                    'level3.15' => false,
                ],
                'level2.6' => [
                    'level3.16' => 'data316',
                    'level3.17' => 317,
                    'level3.18' => true,
                ]
            ]
        ];
        
        file_put_contents(self::FILE_NAME, json_encode($json));
        $this->file = self::FILE_NAME;
    }
    
    protected function removeFile()
    {
        unlink(self::FILE_NAME);
        $this->file = null;
    }
    
    protected function setUp()
    {
        $this->createFile();
        $this->jsonq = new Jsonq(self::FILE_NAME);
    } 

    protected function tearDown()
    {
        $this->removeFile();
    } 
    
    /**
     * @param mixed $file
     * @param bool $result
     *
     * @dataProvider importProvider
     */
    public function testImport($file, $result)
    {
        if ($result) {
            $this->assertEquals(true, $this->jsonq->import($file));
        } else {
            $this->expectException(FileNotFoundException::class);
            $this->jsonq->import($file);
        }
    }

    public function importProvider()
    {
        return [
            [self::FILE_NAME, true], 
            [null, false], 
            [true, false], 
            [1, false], 
            ['invalid_path.json', false]
        ];
    }
}
